<?php
/** R360 API gateway in Fusion open_sync. POST JSON only; no invented math results. */
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function respond(int $code,array $payload):void {http_response_code($code);echo json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){header('Allow: POST');respond(405,['status'=>'FAIL','executed'=>false,'error'=>'POST required']);}
if((int)($_SERVER['CONTENT_LENGTH']??0)>65536)respond(413,['status'=>'FAIL','executed'=>false,'error'=>'Request too large']);
$data=json_decode(file_get_contents('php://input'),true);
if(!is_array($data)||!is_string($data['equation']??null)||trim($data['equation'])==='')respond(400,['status'=>'FAIL','executed'=>false,'error'=>'Valid equation required']);
$variables=$data['variables']??[];
if(!is_array($variables)||count($variables)>100)respond(400,['status'=>'FAIL','executed'=>false,'error'=>'Invalid variables']);
$unit=$data['measurementSystem']??'metric';
if(!in_array($unit,['metric','imperial','custom'],true))respond(400,['status'=>'FAIL','executed'=>false,'error'=>'Invalid measurement system']);
$equation=trim($data['equation']);
class R360 {

 // Canonical registry operations recovered from r360/core/README.md.
 public static function Barry(array $state):array {
  if(!isset($state['a'],$state['b'])||!is_numeric($state['a'])||!is_numeric($state['b']))return ['status'=>'HOLD','reason'=>'Barry midpoint requires explicit a and b'];
  $center=((float)$state['a']+(float)$state['b'])/2;
  return ['status'=>'PASS_REFERENCE','referenceCenter'=>$center,'equation'=>'R360-006','state'=>$state];
 }
 public static function Norm(array $state):array {
  foreach(['PRE','NORM','POST'] as $k)if(!array_key_exists($k,$state)||!is_numeric($state[$k]))return ['status'=>'HOLD','reason'=>'Norm requires explicit PRE, NORM, POST values'];
  $pre=(float)$state['PRE'];$norm=(float)$state['NORM'];$post=(float)$state['POST'];
  return ['status'=>'PASS_COMPARISON','PRE'=>$pre,'NORM'=>$norm,'POST'=>$post,'preDelta'=>$norm-$pre,'postDelta'=>$post-$norm,'signedReturnResidual'=>$post-$pre,'units'=>$state['units']??null,'note'=>'Comparison only; physical conservation not asserted'];
 }
 public static function Missi(array $state):array {
  if(!isset($state['original'],$state['transformed'],$state['inverseReconstructed']))return ['status'=>'HOLD','reason'=>'Missi requires original, transformed, inverseReconstructed'];
  if(!is_numeric($state['original'])||!is_numeric($state['inverseReconstructed']))return ['status'=>'HOLD','reason'=>'Numeric state required'];
  $residual=(float)$state['original']-(float)$state['inverseReconstructed'];
  return ['status'=>'PASS_RESIDUAL_CALC','signedResidual'=>$residual,'closed'=>$residual===0.0,'equation'=>'R360-004/R360-008','note'=>'Caller supplies verified inverse; no inverse inferred'];
 }
 public static function registry(string $id,array $p):array {
  $need=static function(array $keys)use($p):bool {foreach($keys as $k)if(!array_key_exists($k,$p)||!is_numeric($p[$k]))return false;return true;};
  $hold=static fn(string $why):array=>['status'=>'HOLD','equation'=>$id,'reason'=>$why];
  $v=null;
  switch($id){
   case 'R360-001':if(!$need(['observed','target']))return $hold('observed and target required');$v=(float)$p['observed']-(float)$p['target'];break;
   case 'R360-002':if(!$need(['exact','working']))return $hold('exact and working required');$v=(float)$p['exact']-(float)$p['working'];break;
   case 'R360-006':if(!$need(['a','b']))return $hold('a and b required');$v=((float)$p['a']+(float)$p['b'])/2;break;
   case 'R360-007':if(!$need(['X','n']))return $hold('X and n required');$v=(float)$p['X']*pow(2,(float)$p['n']);break;
   case 'R360-008':if(!$need(['input','reconstructed']))return $hold('input and reconstructed required');$v=(float)$p['input']-(float)$p['reconstructed'];break;
   case 'R360-009':if(!$need(['R','S'])||(float)$p['S']==0.0)return $hold('R and nonzero S required');$v=1-abs((float)$p['R'])/abs((float)$p['S']);break;
   case 'R360-010':$v=pow(sqrt(137),2);break;
   case 'R360-013':$v=360*(1/360);break;
   default:return $hold('Operation requires additional canonical mapping or parameters; no guessed implementation');
  }
  if(!is_finite($v))return $hold('Nonfinite result');
  return ['status'=>'PASS_REGISTRY_NUMERIC','equation'=>$id,'value'=>$v,'precision'=>'PHP double','note'=>'Numeric evaluation only; not proof of R360 full geometric closure'];
 }
 public static function generate(array $request):array {
  $eq=trim($request['equation']);$vars=$request['variables'];$trace=[];$crumbs=[];
  $base=['api'=>'R360','engine'=>'R360 PHP arithmetic subset','equation'=>$eq,'measurementSystem'=>$request['measurementSystem'],'executed'=>false,'status'=>'HOLD','trace'=>[],'breadcrumbs'=>[],'residual'=>null];
  $hold=static fn(string $reason,array $trace=[]):array=>array_merge($base,['reason'=>$reason,'trace'=>$trace]);
  $expr=str_replace(['×','÷','−','²','³'],['*','/','-','^2','^3'],$eq);
  $tokens=[];$pos=0;$pattern='/\\G\\s*([A-Za-z_][A-Za-z_0-9]*|(?:\\d+(?:\\.\\d*)?|\\.\\d+)(?:[eE][+\\-]?\\d+)?|[()+*\\/\\-^=])/A';
  while($pos<strlen($expr)){if(!preg_match($pattern,$expr,$m,0,$pos))return $hold('Unsupported token at byte '.$pos,[['stage'=>'EXPLODE','tokens'=>$tokens]]);$tokens[]=$m[1];$pos+=strlen($m[0]);}
  $trace[]=['stage'=>'EXPLODE','tokens'=>$tokens];
  $equals=array_keys($tokens,'=',true);
  if(count($equals)>1)return $hold('Multiple equals signs unsupported',$trace);
  $assignment=null;
  if(count($equals)===1){$i=$equals[0];if($i!==1||!preg_match('/^[A-Za-z_]\\w*$/',$tokens[0]))return $hold('Only name = expression assignments supported',$trace);$assignment=$tokens[0];$tokens=array_slice($tokens,2);}
  if(!$tokens)return $hold('Missing expression',$trace);
  $values=[];
  foreach($tokens as $t){if(!preg_match('/^[A-Za-z_]\\w*$/',$t))continue;
   if(!array_key_exists($t,$vars))return $hold('Undefined variable '.$t,$trace);
   $v=$vars[$t];$unit=is_array($v)?($v['unit']??'1'):'1';$v=is_array($v)?($v['value']??null):$v;
   if(!is_numeric($v)||!is_finite((float)$v))return $hold('Invalid numeric definition for '.$t,$trace);
   if($unit!=='1'&&$unit!=='')return $hold('Dimensional unit execution not implemented for '.$t,$trace);
   $values[$t]=(float)$v;$trace[]=['stage'=>'RESOLVE','variable'=>$t,'value'=>$values[$t],'source'=>'request','locked'=>true];
  }
  $trace[]=['stage'=>'MAP','implementation'=>'arithmetic subset only'];$trace[]=['stage'=>'LOCK','values'=>$values];
  $i=0;$n=count($tokens);
  $parse=null;$sum=null;$product=null;$power=null;$atom=null;
  $atom=function()use(&$atom,&$sum,&$i,$tokens,$n,$values):float {
   if($i >= $n)throw new RuntimeException('Missing operand');
   $t=$tokens[$i++];
   if($t==='('){$v=$sum();if(($tokens[$i++]??null)!==')')throw new RuntimeException('Missing closing parenthesis');return $v;}
   if($t==='+'||$t==='-'){$v=$atom();return $t==='-'?-$v:$v;}
   if(array_key_exists($t,$values))return $values[$t];
   if(is_numeric($t))return (float)$t;
   throw new RuntimeException('Unexpected token '.$t);
  };
  $power=function()use(&$power,&$atom,&$i,$tokens):float {$v=$atom();if(($tokens[$i]??null)==='^'){$i++;$v=pow($v,$power());}return $v;};
  $product=function()use(&$product,&$power,&$i,$tokens,&$crumbs):float {$v=$power();while(in_array($tokens[$i]??null,['*','/'],true)){$op=$tokens[$i++];$w=$power();if($op==='/'&&$w==0.0)throw new RuntimeException('Division by zero');$v=$op==='*'?$v*$w:$v/$w;$crumbs[]=['operator'=>$op,'value'=>$v];}return $v;};
  $sum=function()use(&$sum,&$product,&$i,$tokens,&$crumbs):float {$v=$product();while(in_array($tokens[$i]??null,['+','-'],true)){$op=$tokens[$i++];$w=$product();$v=$op==='+'?$v+$w:$v-$w;$crumbs[]=['operator'=>$op,'value'=>$v];}return $v;};
  try{$value=$sum();if($i!==$n)throw new RuntimeException('Unexpected token '.$tokens[$i]);if(!is_finite($value))throw new RuntimeException('Nonfinite result');}
  catch(Throwable $e){return $hold($e->getMessage(),$trace);}
  $residual=null;if($assignment!==null&&array_key_exists($assignment,$vars)){$target=$vars[$assignment];$target=is_array($target)?($target['value']??null):$target;if(is_numeric($target))$residual=$value-(float)$target;}
  $trace[]=['stage'=>'EXECUTE','value'=>$value];$trace[]=['stage'=>'AUDIT','residual'=>$residual,'scope'=>'arithmetic only'];
  return array_merge($base,['status'=>'PASS_ARITHMETIC_ONLY','executed'=>true,'value'=>$value,'assignment'=>$assignment,'breadcrumbs'=>$crumbs,'residual'=>$residual,'trace'=>$trace,'reason'=>'No GJG, Barry, Norm, Missi or geometric closure executed']);
 }
}
require_once __DIR__.'/R360Executable.php';
try{respond(200,R360Executable::generate(['equation'=>$equation,'variables'=>$variables,'measurementSystem'=>$unit]));}
catch(Throwable $e){respond(500,['status'=>'FAIL','executed'=>false,'error'=>'R360 server error']);}

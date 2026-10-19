<?php
declare(strict_types=1);
/** R360 Revision-4 callable execution candidate. Never claims full locked GJG math. */
final class R360Executable {
 public static function generate(array $input): array {
  $equation=trim((string)($input['equation']??''));
  $vars=$input['variables']??[];
  $unit=(string)($input['measurementSystem']??'metric');
  $trace=[];$breadcrumbs=[];
  $base=['engine'=>'R360 Rev4 candidate','status'=>'HOLD','executed'=>false,'equation'=>$equation,'measurementSystem'=>$unit,'breadcrumbs'=>[],'trace'=>[],'residual'=>null,'precision'=>'PHP binary64'];
  $hold=static fn(string $reason):array=>array_merge($base,['reason'=>$reason,'trace'=>$trace]);
  if($equation===''||!is_array($vars))return $hold('Equation and variable object required');
  $normalized=str_replace(['×','÷','−','²','³'],['*','/','-','^2','^3'],$equation);
  if(!preg_match('/^[\\x20-\\x7e]+$/',$normalized))return $hold('Unsupported symbol; no implicit constant replacement');
  preg_match_all('/(?:\\d+(?:\\.\\d*)?|\\.\\d+)(?:[eE][+-]?\\d+)?|[A-Za-z_][A-Za-z_0-9]*|[()+*\\/^=\\-]|\\S/', $normalized,$match);
  $tokens=$match[0];
  $compact=preg_replace('/\\s+/','',$normalized);
  if(implode('',$tokens)!==$compact)return $hold('Unsupported token');
  $trace[]=['stage'=>'EXPLODE','tokens'=>$tokens];
  $eqPositions=array_keys($tokens,'=',true);$assignment=null;
  if(count($eqPositions)>1)return $hold('Multiple equals signs');
  if(count($eqPositions)===1){
   $i=$eqPositions[0];
   if($i!==1||!preg_match('/^[A-Za-z_]\\w*$/',$tokens[0]))return $hold('Only named assignments supported');
   $assignment=$tokens[0];$tokens=array_slice($tokens,2);
  }
  $resolved=[];$deps=[];
  foreach($tokens as $t){
   if(!preg_match('/^[A-Za-z_]\\w*$/',$t))continue;
   if(!array_key_exists($t,$vars))return $hold('Unresolved variable '.$t);
   $def=$vars[$t];$value=is_array($def)?($def['value']??null):$def;
   $source=is_array($def)?($def['source']??'request'):'request';
   $version=is_array($def)?($def['version']??null):null;
   $u=is_array($def)?($def['unit']??'1'):'1';
   if(!is_numeric($value)||!is_finite((float)$value))return $hold('Non-numeric variable '.$t);
   if($u!=='1'&&$u!=='')return $hold('Dimensional computation not implemented: '.$t.' ['.$u.']');
   $resolved[$t]=(float)$value;$deps[$t]=['source'=>$source,'version'=>$version,'unit'=>$u];
  }
  $trace[]=['stage'=>'RESOLVE/LOCK','values'=>$resolved,'provenance'=>$deps];
  $trace[]=['stage'=>'DEPENDENCY MAP','variables'=>array_keys($resolved)];
  $trace[]=['stage'=>'REUSE OR RECOMPUTE','mode'=>'recompute','cacheHits'=>0,'note'=>'Cross-request cache not yet validated'];
  $pos=0;$count=count($tokens);
  $atom=$power=$mul=$sum=null;
  $atom=function()use(&$atom,&$sum,&$pos,$tokens,$count,$resolved):float {
   if($pos >= $count)throw new RuntimeException('Missing operand');
   $t=$tokens[$pos++];
   if($t==='('){$v=$sum();if(($tokens[$pos++]??null)!==')')throw new RuntimeException('Missing closing parenthesis');return $v;}
   if($t==='+'||$t==='-'){$v=$atom();return $t==='-'?-$v:$v;}
   if(array_key_exists($t,$resolved))return $resolved[$t];
   if(is_numeric($t))return (float)$t;
   throw new RuntimeException('Unexpected token '.$t);
  };
  $power=function()use(&$power,&$atom,&$pos,$tokens):float {$v=$atom();if(($tokens[$pos]??null)==='^'){$pos++;$v=pow($v,$power());}return $v;};
  $mul=function()use(&$mul,&$power,&$pos,$tokens,&$breadcrumbs):float {
   $v=$power();
   while(in_array($tokens[$pos]??null,['*','/'],true)){$op=$tokens[$pos++];$w=$power();if($op==='/'&&$w==0.0)throw new RuntimeException('Division by zero');$before=$v;$v=$op==='*'?$v*$w:$v/$w;$breadcrumbs[]=['operation'=>$op,'left'=>$before,'right'=>$w,'result'=>$v];}return $v;
  };
  $sum=function()use(&$sum,&$mul,&$pos,$tokens,&$breadcrumbs):float {
   $v=$mul();
   while(in_array($tokens[$pos]??null,['+','-'],true)){$op=$tokens[$pos++];$w=$mul();$before=$v;$v=$op==='+'?$v+$w:$v-$w;$breadcrumbs[]=['operation'=>$op,'left'=>$before,'right'=>$w,'result'=>$v];}return $v;
  };
  try{$value=$sum();if($pos!==$count)throw new RuntimeException('Unexpected token '.$tokens[$pos]);if(!is_finite($value))throw new RuntimeException('Nonfinite result');}
  catch(Throwable $e){return array_merge($base,['reason'=>$e->getMessage(),'trace'=>$trace]);}
  $trace[]=['stage'=>'EXECUTE','result'=>$value];
  $trace[]=['stage'=>'RECONSTRUCT','result'=>$value,'scope'=>'arithmetic'];
  $residual=null;
  if($assignment!==null&&array_key_exists($assignment,$vars)){
   $target=is_array($vars[$assignment])?($vars[$assignment]['value']??null):$vars[$assignment];
   if(is_numeric($target))$residual=$value-(float)$target;
  }
  $trace[]=['stage'=>'RESIDUAL AUDIT','residual'=>$residual,'note'=>'Only independently supplied target permits residual'];
  $trace[]=['stage'=>'VERSIONED OUTPUT','implementation'=>'Rev4 candidate 0.1','lockedMath'=>false];
  return array_merge($base,['status'=>'PASS_ARITHMETIC_ONLY','executed'=>true,'assignment'=>$assignment,'value'=>$value,'breadcrumbs'=>$breadcrumbs,'trace'=>$trace,'residual'=>$residual,'reason'=>'No locked GJG/Barry/Norm/Missi geometry claimed']);
 }
}

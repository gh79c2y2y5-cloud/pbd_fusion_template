<?php
declare(strict_types=1);
/** R360 equation registry: provenance-preserving consolidation, not a claim of full locked implementation. */
final class R360 {
 public const REGISTRY_VERSION='2026-10-19-consolidation-001';
 public static function definitions():array {
  return [
   'R360-001'=>['expression'=>'observed-target','source'=>'r360/core/README.md','status'=>'SIGN_PENDING'],
   'R360-002'=>['expression'=>'exact=working+(exact-working)','source'=>'r360/core/README.md','status'=>'RECOVERED'],
   'R360-003'=>['expression'=>'sum(compatible signed residuals)','source'=>'r360/core/README.md','status'=>'RECOVERED'],
   'R360-004'=>['expression'=>'inverse(transform(state))=state','source'=>'r360/core/README.md','status'=>'INVERSE_REQUIRED'],
   'R360-005'=>['expression'=>'state round-trip closure','source'=>'r360/core/README.md','status'=>'PATH_REQUIRED'],
   'R360-006'=>['expression'=>'(a+b)/2','source'=>'r360/core/README.md','status'=>'RECOVERED'],
   'R360-007'=>['expression'=>'X*2^n; inverse /2^n','source'=>'r360/core/README.md','status'=>'RECOVERED'],
   'R360-008'=>['expression'=>'input-reconstructed','source'=>'r360/core/README.md','status'=>'RECOVERED'],
   'R360-009'=>['expression'=>'1-abs(R)/abs(S)','source'=>'r360/core/README.md','status'=>'RECOVERED'],
   'R360-010'=>['expression'=>'sqrt(137)^2','source'=>'r360/core/README.md','status'=>'RECOVERED'],
   'R360-011'=>['expression'=>'L=sqrt(137), A=L^2, V=L^3','source'=>'r360/core/README.md','status'=>'RECOVERED'],
   'R360-012'=>['expression'=>'B=137^(2/3); sqrt(B^3)','source'=>'r360/core/README.md','status'=>'RECOVERED'],
   'R360-013'=>['expression'=>'360*(1/360)','source'=>'r360/core/README.md','status'=>'RECOVERED'],
   'R360-014'=>['expression'=>'sum(m_i*r_i)/sum(m_i)','source'=>'r360/core/README.md','status'=>'RECOVERED'],
   'R360-015'=>['expression'=>'orientation transform chain','source'=>'r360/core/README.md','status'=>'FRAMES_UNVERIFIED'],
   'R360-016'=>['expression'=>'deltaE>epsilon AND stable basin','source'=>'r360/core/README.md','status'=>'THRESHOLD_UNDEFINED'],
   'R360-017'=>['expression'=>'versioned state record','source'=>'r360/core/README.md','status'=>'RECORD'],
   'R360-SCALE-REV002'=>['expression'=>'root_unit(1)=10^-100 inch','source'=>'R360_SCALE_POWERS_TABLE_REV002_2026-10-19.md','status'=>'OWNER_CONFIRMED']
  ];
 }
 public static function generate(array $request):array {
  $id=(string)($request['equationUID']??'');$params=$request['variables']??[];
  $base=['engine'=>'R360','registryVersion'=>self::REGISTRY_VERSION,'equationUID'=>$id,'executed'=>false,'status'=>'HOLD','breadcrumbs'=>[],'residual'=>null];
  if($id===''||!isset(self::definitions()[$id]))return $base+['reason'=>'Supply a known equationUID; free-form R360 EXPLODE engine is not yet recovered','availableEquationUIDs'=>array_keys(self::definitions())];
  if(!is_array($params))return $base+['reason'=>'variables must be an object'];
  $d=self::definitions()[$id];$base['definition']=$d;
  $required=match($id){
   'R360-001'=>['observed','target'],'R360-002'=>['exact','working'],
   'R360-006'=>['a','b'],'R360-007'=>['X','n'],
   'R360-008'=>['input','reconstructed'],'R360-009'=>['R','S'],
   default=>[]};
  foreach($required as $k)if(!array_key_exists($k,$params)||!is_numeric(is_array($params[$k])?($params[$k]['value']??null):$params[$k]))return $base+['reason'=>'Missing explicit numeric variable '.$k];
  $v=static fn($k)=>(float)(is_array($params[$k])?$params[$k]['value']:$params[$k]);
  $result=null;
  switch($id){
   case 'R360-001':return $base+['reason'=>'Signed residual convention pending approval; not silently fixed'];
   case 'R360-002':$result=['residual'=>$v('exact')-$v('working'),'reconstructed'=>$v('working')+($v('exact')-$v('working'))];break;
   case 'R360-006':$result=($v('a')+$v('b'))/2;break;
   case 'R360-007':$result=$v('X')*pow(2,$v('n'));break;
   case 'R360-008':$result=$v('input')-$v('reconstructed');break;
   case 'R360-009':if($v('S')==0.0)return $base+['reason'=>'S must be nonzero'];$result=1-abs($v('R'))/abs($v('S'));break;
   case 'R360-010':$result=pow(sqrt(137),2);break;
   case 'R360-011':$L=sqrt(137);$result=['L'=>$L,'A'=>$L*$L,'V'=>$L*$L*$L];break;
   case 'R360-012':$B=pow(137,2/3);$result=['B'=>$B,'reconstructed'=>sqrt(pow($B,3))];break;
   case 'R360-013':$result=360*(1/360);break;
   case 'R360-SCALE-REV002':return $base+['status'=>'SYMBOLIC_ONLY','reason'=>'Exact 10^-100 inch must not be rounded into binary64','exact'=>'10^-100 inch'];
   default:return $base+['reason'=>'Registry entry recovered; executable dependencies or frames not verified'];
  }
  if(is_float($result)&&!is_finite($result))return $base+['reason'=>'Nonfinite result'];
  return array_merge($base,['status'=>'PASS_NUMERIC_SUBSET','executed'=>true,'result'=>$result,'precision'=>'PHP binary64','trace'=>[['stage'=>'RESOLVE/LOCK','inputs'=>$params],['stage'=>'EXECUTE','equationUID'=>$id],['stage'=>'AUDIT','scope'=>'numerical registry only']],'reason'=>'Does not certify complete GJG or locked R360 execution']);
 }
 // One callable class method for each currently recovered registry EquationUID.
 public static function R360_001(array $variables=[]):array {return self::generate(['equationUID'=>'R360-001','variables'=>$variables]);}
 public static function R360_002(array $variables=[]):array {return self::generate(['equationUID'=>'R360-002','variables'=>$variables]);}
 public static function R360_003(array $variables=[]):array {return self::generate(['equationUID'=>'R360-003','variables'=>$variables]);}
 public static function R360_004(array $variables=[]):array {return self::generate(['equationUID'=>'R360-004','variables'=>$variables]);}
 public static function R360_005(array $variables=[]):array {return self::generate(['equationUID'=>'R360-005','variables'=>$variables]);}
 public static function R360_006(array $variables=[]):array {return self::generate(['equationUID'=>'R360-006','variables'=>$variables]);}
 public static function R360_007(array $variables=[]):array {return self::generate(['equationUID'=>'R360-007','variables'=>$variables]);}
 public static function R360_008(array $variables=[]):array {return self::generate(['equationUID'=>'R360-008','variables'=>$variables]);}
 public static function R360_009(array $variables=[]):array {return self::generate(['equationUID'=>'R360-009','variables'=>$variables]);}
 public static function R360_010(array $variables=[]):array {return self::generate(['equationUID'=>'R360-010','variables'=>$variables]);}
 public static function R360_011(array $variables=[]):array {return self::generate(['equationUID'=>'R360-011','variables'=>$variables]);}
 public static function R360_012(array $variables=[]):array {return self::generate(['equationUID'=>'R360-012','variables'=>$variables]);}
 public static function R360_013(array $variables=[]):array {return self::generate(['equationUID'=>'R360-013','variables'=>$variables]);}
 public static function R360_014(array $variables=[]):array {return self::generate(['equationUID'=>'R360-014','variables'=>$variables]);}
 public static function R360_015(array $variables=[]):array {return self::generate(['equationUID'=>'R360-015','variables'=>$variables]);}
 public static function R360_016(array $variables=[]):array {return self::generate(['equationUID'=>'R360-016','variables'=>$variables]);}
 public static function R360_017(array $variables=[]):array {return self::generate(['equationUID'=>'R360-017','variables'=>$variables]);}
 public static function R360_SCALE_REV002(array $variables=[]):array {return self::generate(['equationUID'=>'R360-SCALE-REV002','variables'=>$variables]);}

 public static function Barry(array $state):array {return self::generate(['equationUID'=>'R360-006','variables'=>$state]);}
 public static function Norm(array $state):array {return ['status'=>'HOLD','reason'=>'Locked Norm function body not yet recovered','input'=>$state];}
 public static function Missi(array $state):array {return ['status'=>'HOLD','reason'=>'Locked Missi inverse function body not yet recovered','input'=>$state];}
}

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
// The canonical class belongs in r360/core, not in this API or the client.
$coreFile=dirname(__DIR__,2).'/r360/core/R360.php';
if(is_file($coreFile)) require_once $coreFile;
if(class_exists('R360') && method_exists('R360','generate')){
 try{
  $result=R360::generate(['equation'=>$equation,'variables'=>$variables,'measurementSystem'=>$unit]);
  if(!is_array($result))throw new RuntimeException('R360 returned a non-object result');
  respond(200,$result);
 }catch(Throwable $e){respond(500,['status'=>'FAIL','executed'=>false,'error'=>'R360 execution failed']);}
}
respond(200,['api'=>'R360','version'=>'0.1.0','status'=>'HOLD','executed'=>false,'equation'=>$equation,'measurementSystem'=>$unit,'trace'=>[['stage'=>'RECEIVE','variableCount'=>count($variables)]],'breadcrumbs'=>[],'residual'=>null,'reason'=>'Canonical R360 class is not installed at r360/core/R360.php.']);

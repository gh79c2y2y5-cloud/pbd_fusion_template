<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
function send(int $code,array $payload):void {http_response_code($code);echo json_encode($payload,JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE);exit;}
require_once __DIR__.'/R360.php';
if(($_SERVER['REQUEST_METHOD']??'')==='GET'){send(200,['api'=>'R360','registryVersion'=>R360::REGISTRY_VERSION,'status'=>'REGISTRY_AVAILABLE','equations'=>R360::definitions(),'note'=>'Only recovered numeric subset executable; no full locked math claim']);}
if(($_SERVER['REQUEST_METHOD']??'')!=='POST'){header('Allow: GET, POST');send(405,['status'=>'FAIL','error'=>'GET or POST required']);}
if((int)($_SERVER['CONTENT_LENGTH']??0)>65536)send(413,['status'=>'FAIL','error'=>'Request too large']);
$data=json_decode(file_get_contents('php://input'),true);
if(!is_array($data))send(400,['status'=>'FAIL','error'=>'JSON object required']);
if(!isset($data['equationUID'])&&isset($data['equation']))$data['equationUID']=$data['equation'];
try{send(200,R360::generate($data));}catch(Throwable $e){send(500,['status'=>'FAIL','executed'=>false,'error'=>'R360 API execution error']);}

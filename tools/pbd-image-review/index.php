<?php
declare(strict_types=1);
session_start();
$secret=getenv('PBD_REVIEW_PASSWORD');
if(!$secret){http_response_code(503);exit('Set PBD_REVIEW_PASSWORD');}
if(!isset($_SESSION['auth']))$_SESSION['auth']=false;
if(isset($_POST['password'])&&hash_equals($secret,(string)$_POST['password'])){session_regenerate_id(true);$_SESSION['auth']=true;}
if(!$_SESSION['auth']){echo '<form method="post"><input type="password" name="password" required><button>Log in</button></form>';exit;}
if(!isset($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(16));
$root=getenv('PBD_PRIVATE_DIR');
if(!$root||!is_dir($root)){http_response_code(503);exit('Configure PBD_PRIVATE_DIR outside webroot');}
$images=$root.'/images';$records=$root.'/records';$texts=$root.'/texts';
foreach([$images,$records,$texts] as $dir){if(!is_dir($dir))mkdir($dir,0700,true);}
function esc(string $s):string{return htmlspecialchars($s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');}
function gallery(string $dir):array{$out=[];foreach(scandir($dir)?:[] as $name){if(!preg_match('/^[a-zA-Z0-9._-]+\.(jpg|jpeg|png|webp)$/i',$name))continue;$path=$dir.'/'.$name;if(is_link($path)||!is_file($path))continue;$mime=(new finfo(FILEINFO_MIME_TYPE))->file($path);if(in_array($mime,['image/jpeg','image/png','image/webp'],true))$out[$name]=$path;}return $out;}
$files=gallery($images);$message='';
if(isset($_GET['image'])){$name=(string)$_GET['image'];if(!isset($files[$name])){http_response_code(404);exit;}header('Content-Type: '.(new finfo(FILEINFO_MIME_TYPE))->file($files[$name]));header('X-Content-Type-Options: nosniff');header('Cache-Control: private, no-store');readfile($files[$name]);exit;}
if($_SERVER['REQUEST_METHOD']==='POST'&&!isset($_POST['password'])){
 try{
 if(!hash_equals($_SESSION['csrf'],(string)($_POST['csrf']??'')))throw new RuntimeException('Invalid token');
 if(isset($_FILES['upload'])&&$_FILES['upload']['error']===UPLOAD_ERR_OK){$f=$_FILES['upload'];if($f['size']>15000000)throw new RuntimeException('Image too large');$mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);$ext=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'][$mime]??null;if(!$ext||!getimagesize($f['tmp_name']))throw new RuntimeException('Invalid image');$name=bin2hex(random_bytes(12)).'.'.$ext;if(!move_uploaded_file($f['tmp_name'],$images.'/'.$name))throw new RuntimeException('Upload failed');chmod($images.'/'.$name,0600);$message='Uploaded '.$name;}
 elseif(isset($_POST['save'])){$name=(string)($_POST['filename']??'');if(!isset($files[$name]))throw new RuntimeException('Missing image');$body=(string)($_POST['transcription']??'');if(strlen($body)>250000)throw new RuntimeException('Text too long');$id=hash('sha256',$name);$record=['filename'=>$name,'sha256'=>hash_file('sha256',$files[$name]),'transcription'=>$body,'updated_utc'=>gmdate('c')];file_put_contents($records.'/'.$id.'.json',json_encode($record,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),LOCK_EX);file_put_contents($texts.'/'.$id.'.txt',$body,LOCK_EX);$message='Saved '.$name;}
 }catch(Throwable $e){$message='Error: '.$e->getMessage();}
 $files=gallery($images);
}
$selected=(string)($_GET['selected']??$_POST['filename']??'');if(!isset($files[$selected]))$selected=array_key_first($files)??'';
$existing='';if($selected!==''){$p=$records.'/'.hash('sha256',$selected).'.json';if(is_file($p))$existing=(string)((json_decode(file_get_contents($p),true)??[])['transcription']??'');}
?><!doctype html><html lang="en"><meta charset="utf-8"><title>PBD Image Review</title><style>body{background:#0b1a30;color:#e8d6c3;font:16px system-ui;margin:2rem}a{color:#ce9b72}img{max-width:100%;max-height:55vh}textarea{width:100%;height:220px;background:#162c46;color:white}.grid{display:grid;grid-template-columns:260px 1fr;gap:25px}aside{overflow:auto;max-height:75vh}aside a{display:block;padding:8px}@media(max-width:650px){.grid{display:block}}</style><h1>Project Blue Dragon Image Review</h1><p><?=esc($message)?></p><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=esc($_SESSION['csrf'])?>"><input type="file" name="upload" accept="image/jpeg,image/png,image/webp" required><button>Upload</button></form><div class="grid"><aside><?php foreach($files as $name=>$path):?><a href="?selected=<?=rawurlencode($name)?>"><?=esc($name)?></a><?php endforeach;?></aside><main><?php if($selected!==''):?><h2><?=esc($selected)?></h2><img src="?image=<?=rawurlencode($selected)?>" alt="Selected image"><form method="post"><input type="hidden" name="csrf" value="<?=esc($_SESSION['csrf'])?>"><input type="hidden" name="filename" value="<?=esc($selected)?>"><p>Transcription (editable)</p><textarea name="transcription"><?=esc($existing)?></textarea><button name="save" value="1">Save transcription</button></form><?php endif;?></main></div></html>
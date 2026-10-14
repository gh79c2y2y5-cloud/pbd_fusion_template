<?php
declare(strict_types=1);
// Project Blue Dragon R&D: CLI-only, resumable batch intake.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
$base=dirname(__DIR__);
$input=getenv('PBD_RND_INPUT') ?: $base.'/incoming';
$output=getenv('PBD_RND_OUTPUT') ?: $base.'/records';
$limit=max(1,min(100,(int)(getenv('PBD_RND_BATCH') ?: 10)));
@mkdir($input,0700,true); @mkdir($output,0700,true);
$lock=fopen($base.'/process.lock','c');
if (!$lock || !flock($lock,LOCK_EX|LOCK_NB)) exit("Already running\n");
$files=[];
foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($input,FilesystemIterator::SKIP_DOTS)) as $f){
 if($f->isFile() && preg_match('/\.(jpg|jpeg|png|webp)$/i',$f->getFilename())) $files[]=$f->getPathname();
}
sort($files,SORT_STRING);
$processed=0;$skipped=0;
$ocr=trim((string)shell_exec('command -v tesseract 2>/dev/null'));
foreach($files as $path){
 $sha=hash_file('sha256',$path);$dest=$output.'/'.$sha.'.json';
 if(is_file($dest)){$skipped++;continue;}
 $text='';$status='needs_manual_transcription';
 if($ocr!==''){
  $text=trim((string)shell_exec(escapeshellarg($ocr).' '.escapeshellarg($path).' stdout 2>/dev/null'));
  if($text!=='')$status='machine_transcribed_unverified';
 }
 $data=['source_file'=>basename($path),'sha256'=>$sha,'transcription'=>$text,
 'transcription_status'=>$status,'engineering_review'=>'not_reviewed',
 'math_verification'=>'not_tested','chronological_phase'=>'unknown','created_at'=>gmdate('c')];
 $tmp=$dest.'.tmp.'.getmypid();
 if(file_put_contents($tmp,json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_INVALID_UTF8_SUBSTITUTE))===false)continue;
 if(!rename($tmp,$dest)){@unlink($tmp);continue;}
 if(++$processed >= $limit)break;
}
echo gmdate('c')." processed=$processed existing=$skipped scanned=".count($files)."\n";
flock($lock,LOCK_UN);fclose($lock);

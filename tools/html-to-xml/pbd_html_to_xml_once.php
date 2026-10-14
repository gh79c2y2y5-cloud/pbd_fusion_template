<?php
declare(strict_types=1);
// One-time HTML-to-XML converter. Requires PHP DOM. Set environment variables:
// PBD_CONVERT_TOKEN, PBD_HTML_SOURCE, PBD_XML_OUTPUT.
$secret=getenv('PBD_CONVERT_TOKEN');
if(!$secret || !hash_equals($secret,(string)($_GET['token']??''))){http_response_code(403);exit('Access denied');}
header('Content-Type: text/plain; charset=utf-8');
$src=getenv('PBD_HTML_SOURCE');$dst=getenv('PBD_XML_OUTPUT');
if(!$src||!$dst||!is_dir($src)){http_response_code(400);exit('Configure source/output directories');}
$src=realpath($src);
if(!is_dir($dst)&&!mkdir($dst,0750,true)){http_response_code(500);exit('Cannot create output');}
$dst=realpath($dst);
if($src===$dst||str_starts_with($dst.'/',$src.'/')){http_response_code(400);exit('Output must be separate from source');}
$files=glob($src.'/*.{html,htm,HTML,HTM}',GLOB_BRACE)?:[];sort($files,SORT_NATURAL);
$run=($_GET['run']??'')==='yes';$overwrite=($_GET['overwrite']??'')==='yes';
echo ($run?'RUN':'PREVIEW').': '.count($files)." HTML files\n";
$ok=0;$skip=0;$fail=0;
foreach($files as $file){
 if(!is_file($file)||is_link($file))continue;
 $name=pathinfo($file,PATHINFO_FILENAME);$out=$dst.'/'.$name.'.xml';
 if(is_file($out)&&!$overwrite){echo "SKIP $name\n";$skip++;continue;}
 if(!$run){echo 'WOULD CONVERT '.basename($file)."\n";continue;}
 $html=file_get_contents($file);if($html===false){$fail++;continue;}
 $doc=new DOMDocument('1.0','UTF-8');
 $old=libxml_use_internal_errors(true);
 $parsed=$doc->loadHTML('<?xml encoding="UTF-8">'.$html,LIBXML_NONET|LIBXML_COMPACT);
 libxml_clear_errors();libxml_use_internal_errors($old);
 if(!$parsed){echo "PARSE FAILED $name\n";$fail++;continue;}
 foreach(iterator_to_array($doc->childNodes) as $child){if($child instanceof DOMProcessingInstruction && $child->target==='xml')$doc->removeChild($child);}
 $doc->encoding='UTF-8';$doc->formatOutput=true;$xml=$doc->saveXML();
 if(!$xml||file_put_contents($out,$xml,LOCK_EX)===false){$fail++;continue;}
 echo "OK $name.xml\n";$ok++;
}
echo "converted=$ok skipped=$skip failed=$fail\n";
echo "Well-formed XML is not automatically a Joomla module manifest. Remove this script after use.\n";

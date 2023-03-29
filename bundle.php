<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}", true);
} else {
  header('Access-Control-Allow-Origin: *', true);
}
header('Cache-Control: public, max-age=2592000', true);
header('Content-Type: application/javascript; charset=UTF-8', true);
header('Vary: Accept-Encoding,Sec-Available-Dictionary', true);
$comp = '';
if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'sbr') !== false &&
    isset($_SERVER['HTTP_SEC_AVAILABLE_DICTIONARY']) &&
    $_SERVER['HTTP_SEC_AVAILABLE_DICTIONARY'] == '74b856e554018fec0d6054c51bc1588fbf2386338d851842447a9510db015732') {
  header("Content-Encoding: sbr", true);
  $comp = '.sbr.74b856e554018fec0d6054c51bc1588fbf2386338d851842447a9510db015732';
} elseif (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'br') !== false) {
  header("Content-Encoding: br", true);
  $comp = '.br';
} elseif (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
  header("Content-Encoding: gzip", true);
  $comp = '.gz';
}
$file = __DIR__ . "/bundle.js$comp";
$filesize = filesize($file);
header("Content-Length: $filesize", true);
readfile($file);
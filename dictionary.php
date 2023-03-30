<?php
if (isset($_SERVER['HTTP_ORIGIN'])) {
  header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}", true);
} else {
  header('Access-Control-Allow-Origin: *', true);
}
header('Cache-Control: public, max-age=2592000', true);
header('Content-Type: application/javascript; charset=UTF-8', true);
header('Vary: Accept-Encoding,Sec-Available-Dictionary', true);
header('Use-As-Dictionary: p="bundle*"', true);
header("X-Accept-Encoding-Received: {$_SERVER['HTTP_ACCEPT_ENCODING']}", true);
$comp = '';
if (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'br') !== false) {
  header("Content-Encoding: br", true);
  $comp = '.br';
} elseif (strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') !== false) {
  header("Content-Encoding: gzip", true);
  $comp = '.gz';
}
$file = __DIR__ . "/dictionary.js$comp";
$filesize = filesize($file);
header("Content-Length: $filesize", true);
readfile($file);
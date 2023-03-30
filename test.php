<?php
$uid = sha1(strval(microtime(true)));
$dictionary_url = $_REQUEST['dictionary'];
$bundle_url = $_REQUEST['bundle'];
$path = __DIR__ . "/data/$uid";
header('Cache-Control: private, no-store, no-cache', true)
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Shared Brotli Test Result</title>
    <style>
      body {
        font-family: Arial, Helvetica, sans-serif;
        line-height: 1.5em;
      }
      pre {
        line-height: 1em;
      }
      p {
        margin-block-start: 0;
        margin-block-end: 0;
      }
    </style>
  </head>
  <body>
    <H1>Shared Brotli Test Result</H1>

<?php
// Fetch the dictionary and verify that it has the correct headers
echo "<h2>Step 1 - Fetch Dictionary</h2>\n";
$dict = "$path.dict";
$result = fetch($dictionary_url, null, $dict);
if (is_file($dict)) {
  echo "<p><b>Dictionary URL</b>: " . htmlspecialchars($dictionary_url) . "</p>\n";
  $hash = hash_file('sha256', $dict);
  echo "<p><b>Dictionary Hash</b>: $hash</p>";
  $size = filesize($dict);
  echo "<p><b>Dictionary File Size</b>: $size</p>";
  $dsize = $result['size'];
  echo "<p><b>Dictionary File Transfer Size</b>: $dsize</p>";
}
dumpHeaders($result['response']);

if (is_file($dict)) {
// Fetch the bundle without a dictionary
echo "<h2>Step 2 - Fetch Resource (without dictionary compression)</h2>\n";
$bundle = "$path.bundle";
$result = fetch($bundle_url, null, $bundle);
if (is_file($bundle)) {
  echo "<p><b>Resource URL</b>: " . htmlspecialchars($bundle_url) . "</p>\n";
  $bhash = hash_file('sha256', $bundle);
  echo "<p><b>Resource Hash</b>: $bhash</p>";
  $size = filesize($bundle);
  echo "<p><b>Resource File Size</b>: $size</p>";
  $bsize = $result['size'];
  echo "<p><b>Resource File Transfer Size</b>: $bsize</p>";
}
dumpHeaders($result['response']);

// Fetch the bundle with the dictionary and make sure it matches the non-dictionary version after decompression
echo "<h2>Step 3 - Fetch Dictionary-Compressed Resource</h2>\n";
$sbr = "$path.sbr";
$result = fetch($bundle_url, $hash, $sbr);
if (is_file($sbr)) {
  echo "<p><b>Resource URL</b>: " . htmlspecialchars($bundle_url) . "</p>\n";
  $size = filesize($sbr);
  echo "<p><b>Resource File Size</b>: $size</p>";
  $ssize = $result['size'];
  echo "<p><b>Resource File Transfer Size</b>: $ssize</p>";
}
dumpHeaders($result['response']);
}

// Try using the dictionary to decompress the response
echo "<h2>Step 4 - Verify Dictionary-Compressed Response</h2>\n";
// make sure the content-encoding was SBR
$sbr_transfer = false;
foreach($result['response'] as $header) {
  if (strcasecmp($header, 'Content-Encoding: sbr') === 0) {
    $sbr_transfer = true;
    break;
  }
}
if (!$sbr_transfer) {
  echo '<h1>Fail - Content-Encoding was not sbr</h1>';
} elseif (is_file($dict) && is_file($sbr)) {
  $dec = "$path.dec";
  exec("brotli --decompress -D '$dict' -o '$dec' $sbr");
  if (is_file($dec)) {
    $size = filesize($dec);
    echo "<p><b>Decompressed File Size</b>: $size</p>";
    $dhash = hash_file('sha256', $dec);
    echo "<p><b>Decompressed Hash</b>: $dhash</p>";
    if ($dhash == $bhash) {
      echo '<h1>SUCCESS - Hashes Match</h1>';
    } else {
      echo '<h1>Fail - Hashes Do Not Match</h1>';
    }
  }
}
?>

  </body>
</html>
<?php
//cleanup
if (isset($dict) && is_file($dict)) { unlink($dict); }
if (isset($bundle) && is_file($bundle)) { unlink($bundle); }
if (isset($sbr) && is_file($sbr)) { unlink($sbr); }
if (isset($dec) && is_file($dec)) { unlink($dec); }

// helper functions
function dumpHeaders($headers) {
  echo "<h3>Response Headers</h3>\n";
  echo "<pre>\n";
  foreach( $headers as $header) {
    echo "$header\n";
  }
  echo "</pre>\n";
}

$response_headers = array();
function parseResponseHeaders($ch, $header_line) {
  global $response_headers;
  $response_headers[] = trim($header_line);
  return strlen($header_line);
}

function fetch($url, $available_dictionary, $file) {
  global $response_headers;
  $response_headers = array();
  $result = array();
  $fp = fopen($file, 'w+');
  if ($fp) {
    $ch = curl_init($url);
    $ua = isset($info['ua']) ? $info['ua'] : $_SERVER['HTTP_USER_AGENT'];
    // Add the default headers
    $headers = array(
      "User-Agent: $ua",
      'Accept: */*',
      'Accept-Language: en-US,en;q=0.9',
      'Sec-Fetch-Dest: script',
      'Sec-Fetch-Mode: no-cors',
      'Sec-Fetch-Site: same-origin'
    );
    if (isset($available_dictionary)) {
      $headers [] = 'Accept-Encoding: gzip,br,sbr';
      $headers [] = "Sec-Available-Dictionary: $available_dictionary";
    } else {
      curl_setopt($ch, CURLOPT_ENCODING , '');
    }
    curl_setopt($ch, CURLOPT_TIMEOUT, 600);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_HEADERFUNCTION, "parseResponseHeaders"); 
    curl_setopt($ch, CURLOPT_FILE, $fp);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if (curl_exec($ch) === false) {
      $result['error'] = "Error fetching $url";
    } else {
      $result['size'] = curl_getinfo($ch, CURLINFO_SIZE_DOWNLOAD);
    }
    curl_close($ch);
    fclose($fp);
  }
  $result['response'] = $response_headers;
  return $result;
}

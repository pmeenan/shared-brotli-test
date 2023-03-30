<?php
$uid = sha1(strval(microtime(true)));
$dir = $_SERVER['REQUEST_URI'];
if (!str_ends_with($dir, "/")) {
  $dir = dirname($dir);
}
$path = "https://{$_SERVER['HTTP_HOST']}$dir";
header('Cache-Control: private, no-store, no-cache', true)
?>
<!DOCTYPE html>
<html>
  <head>
    <title>Shared Brotli Tester</title>
    <style>
      body {
        font-family: Arial, Helvetica, sans-serif;
        line-height: 1.5em;
      }
    </style>
  </head>
  <body>
    <H1>Shared Brotli Tester</H1>
    <p>This test page will run several tests to determine if Shared Brotli compression will work with the server and CDN configuration that it is running behind.</p>
    <p>Specifically, it will test:</p>
    <ul>
      <li>A javascript resource can respond with a "Use-As-Dictionary:" response header.</li>
      <li>A request for a javascript resource with "Accept-Encoding: sbr,br,gzip" and "Sec-Available-Dictionary:" request headers pass the headers through to the back-end application server.</li>
      <li>A response with "Content-Encoding: sbr" is passed through from the server back to the client.</li>
      <li>The sbr-compressed resource matches the uncompressed version of the same resource.</li>
    </ul>
    <p>
      The test defaults to using pre-prepared shared-brotli resources that are bundled with the test but you can substitute other URLs if there are other resources that you would like to test.
    </p>
    <p>
      If you would like to test a CDN's support for passing "sbr" content encoding and varying the cache responses, configure the CDN with this server as a back-end and you should be able to use the same test page on the origin you configure.
    </p>
    <form action="test.php" method="post">
      <p>
        <label for="dictionary">Dictionary URL:</label><br>
        <?php
        $dictionary = htmlspecialchars("{$path}dictionary.php?uid=$uid");
        echo "<input type='text' id='dictionary' name='dictionary' size='160' value='$dictionary'>\n";
        ?>
      </p>
      <p>
        <label for="bundle">Compressed Resource URL:</label><br>
        <?php
        $bundle = htmlspecialchars("{$path}bundle.php?uid=$uid");
        echo "<input type='text' id='bundle' name='bundle' size='160' value='$bundle'>\n";
        ?>
      </p>
      <input type="submit" value="Submit">
    </form>
    <script>
    function fixUrl(id) {
      const e = document.getElementById(id);
      const url = new URL(e.value);
      const origin = window.location.origin;
      e.value = origin + url.pathname + url.search
    }
    fixUrl('dictionary');
    fixUrl('bundle');
    </script>
  </body>
</html>
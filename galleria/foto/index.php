<?
$info = $_SERVER['PATH_INFO'];
if (substr($info, 0, 1) != '/' || strpos($info, '/.') !== false)
  {
    header('Status: 403 Forbidden');
    exit(1);
  }

# elimina /index.php/PATH_INFO da PHP_SELF
$base = substr($_SERVER['PHP_SELF'], 1,
               strlen($_SERVER['PHP_SELF']) - strlen($info) - 1);
$base = dirname($base);

$file = $_ENV['OPENSHIFT_DATA_DIR'] . $base . $info;
$size = @filesize($file);
if ($size === false)
  {
    header('Status: 404 Not Found');
    exit(1);
  }

header('Content-Type: image/jpeg');
header('Content-Length: ' . $size);
readfile($file);

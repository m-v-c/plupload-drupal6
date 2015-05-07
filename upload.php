<?php
/**
 * upload.php
 *
 * Copyright 2013, Moxiecode Systems AB
 * Released under GPL License.
 *
 * License: http://www.plupload.com/license
 * Contributing: http://www.plupload.com/contributing
 */

// Make sure file is not cached (as it happens for example on iOS devices)
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

/* 
// Support CORS
header("Access-Control-Allow-Origin: *");
// other CORS headers if any...
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
  exit; // finish preflight CORS requests here
}
*/

// 5 minutes execution time
@set_time_limit(5 * 60);

// Settings
$targetDir = '/tmp' . DIRECTORY_SEPARATOR . "plupload";
$cleanupTargetDir = true; // Remove old files
$maxFileAge = 5 * 3600; // Temp file age in seconds

// Create target dir
if (!file_exists($targetDir)) {
  @mkdir($targetDir);
}

// Get drupal session id
$sid = $_REQUEST['sid'];

// Check against drupal sessions table
// TODO this just makes sure that the user has a valid drupal account; add other checks if desired
// TODO get rid of the ugly hack that parses settings.php manually :/
$settings = $_SERVER['DOCUMENT_ROOT'] . '/sites/default/settings.php';
$settings = split("\n", file_get_contents($settings));
$db_url = preg_grep('/^\$db_url/', $settings);
$db_url = array_pop($db_url);
$db_url = preg_replace("/\\\$db_url.*=.*'(.*?)'\s*;/", '$1', $db_url);
$db_url = parse_url($db_url);
$db_url['path'] = substr($db_url['path'], 1);

$mysqli = new mysqli($db_url['host'], $db_url['user'], $db_url['pass'], $db_url['path']);
$result = $mysqli->query("SELECT uid FROM sessions WHERE sid = '$sid'");
$row = $result->fetch_assoc();
if ($row && $row['uid'] > 0) {
  // success
}
else {
  die('{"jsonrpc" : "2.0", "error" : {"code": 107, "message": "Unknown user."}, "id" : "id"}');
}

// Get a file name
if (isset($_REQUEST["name"])) {
  $fileName = $_REQUEST["name"];
} elseif (!empty($_FILES)) {
  $fileName = $_FILES["file"]["name"];
/*
} else {
  $fileName = uniqid("file_");
*/
}

// Clean the fileName for security reasons
$fileName = preg_replace('/[^\w\._]+/', '_', $fileName);

$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

// Chunking might be enabled
$chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
$chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 0;

// Remove old temp files  
if ($cleanupTargetDir) {
  if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
    die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
  }

  while (($file = readdir($dir)) !== false) {
    $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

    // If temp file is current file proceed to the next
    if ($tmpfilePath == "{$filePath}.part") {
      continue;
    }

    // Remove temp file if it is older than the max age and is not the current file
    if (preg_match('/\.part$/', $file) && (filemtime($tmpfilePath) < time() - $maxFileAge)) {
      @unlink($tmpfilePath);
    }
  }
  closedir($dir);
} 

// Open temp file
if (!$out = @fopen("{$filePath}.part", $chunks ? "ab" : "wb")) {
  die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
}

if (!empty($_FILES)) {
  if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
    die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
  }

  // Read binary input stream and append it to temp file
  if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
  }
} else {  
  if (!$in = @fopen("php://input", "rb")) {
    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
  }
}

while ($buff = fread($in, 4096)) {
  fwrite($out, $buff);
}

@fclose($out);
@fclose($in);

// Check if file has been uploaded
if (!$chunks || $chunk == $chunks - 1) {
  // Strip the temp .part suffix off 
  rename("{$filePath}.part", $filePath);
}

// Return Success JSON-RPC response
die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');

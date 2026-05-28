<?php
/**
 * Secure file delivery with HMAC and expiry.
 * Example URL: /security/download.php?f=BASE64_PATH&e=TIMESTAMP&t=HMAC
 */

declare(strict_types=1);
define('__APP__', true);

// CONFIG: adjust to real protected directory
$PROTECTED_ROOT = realpath(__DIR__ . '/../protected_uploads');
$SECRET = getenv('BESPLATNA_SIGNING_KEY') ?: 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET';

function bad_request(int $code=403) {
  http_response_code($code);
  echo 'Forbidden';
  exit;
}

if (!isset($_GET['f'], $_GET['e'], $_GET['t'])) {
  bad_request(400);
}

$encodedPath = (string)$_GET['f'];
$expiry = (int)$_GET['e'];
$token  = (string)$_GET['t'];

if ($expiry < time()) {
  bad_request(410);
}

$path = base64_decode($encodedPath, true);
if ($path === false) { bad_request(400); }

// Normalize and confine to PROTECTED_ROOT
$real = realpath($path);
if ($real === false || strpos($real, $PROTECTED_ROOT) !== 0) {
  bad_request(403);
}

$expected = hash_hmac('sha256', $real . '|' . $expiry, $SECRET);
if (!hash_equals($expected, $token)) {
  bad_request(403);
}

if (!is_file($real) || !is_readable($real)) {
  bad_request(404);
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $real) ?: 'application/octet-stream';
finfo_close($finfo);

header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($real));
header('X-Accel-Buffering: no');
readfile($real);
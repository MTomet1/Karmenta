<?php
/**
 * Example usage to create a signed, expiring URL.
 * Usage: include this and call generate_signed_url($absPath, $ttlSeconds)
 */
function generate_signed_url(string $absPath, int $ttl = 1800): string {
  $PROTECTED_ROOT = realpath(__DIR__ . '/../protected_uploads');
  $SECRET = getenv('BESPLATNA_SIGNING_KEY') ?: 'CHANGE_THIS_TO_A_LONG_RANDOM_SECRET';

  $real = realpath($absPath);
  if ($real === false || strpos($real, $PROTECTED_ROOT) !== 0) {
    throw new RuntimeException('Path must be inside protected_uploads');
  }
  $expiry = time() + $ttl;
  $token  = hash_hmac('sha256', $real . '|' . $expiry, $SECRET);
  $f      = urlencode(base64_encode($real));
  return "/security/download.php?f={$f}&e={$expiry}&t={$token}";
}

// Example:
// echo generate_signed_url(__DIR__ . '/../protected_uploads/sample.jpg');
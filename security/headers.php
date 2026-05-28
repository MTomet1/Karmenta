<?php
if (headers_sent()) {
    return;
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
/* Uncomment if site has HTTPS:
header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
*/

header(
    "Content-Security-Policy: default-src 'self'; " .
    "base-uri 'self'; form-action 'self'; frame-ancestors 'none'; object-src 'none'; " .
    "img-src 'self' data: blob: https://maps.gstatic.com https://www.gstatic.com; " .
    "script-src 'self' 'unsafe-inline' https://ajax.googleapis.com https://cdn.jsdelivr.net https://stackpath.bootstrapcdn.com https://maxcdn.bootstrapcdn.com https://maps.googleapis.com https://maps.gstatic.com https://www.google.com https://www.gstatic.com; " .
    "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://stackpath.bootstrapcdn.com https://maxcdn.bootstrapcdn.com; " .
    "font-src 'self' data: https://fonts.gstatic.com https://stackpath.bootstrapcdn.com https://maxcdn.bootstrapcdn.com; " .
    "frame-src https://www.google.com https://www.google.hr https://recaptcha.google.com; " .
    "connect-src 'self' https://maps.googleapis.com https://www.google.com https://www.gstatic.com;"
);

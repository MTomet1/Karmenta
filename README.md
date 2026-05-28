# besplatna.hr — Security Patch

This package provides a pragmatic hardening baseline for Apache/PHP shared hosting.

## Files

- `.htaccess` — disables directory listing, blocks sensitive files, adds security headers, optional HTTPS redirect.
- `security/headers.php` — sets headers from PHP (fallback or supplement).
- `security/session_boot.php` — secure session cookie flags.
- `security/download.php` — HMAC-signed, expiring file delivery from `protected_uploads/`.
- `security/generate_signed_url.php` — helper to generate signed links.
- `security/pdo_bootstrap_example.php` — PDO template.
- `public_uploads/.htaccess` — blocks PHP execution in uploads.

## How to apply

1. Upload/extract this zip into your web root (where index.php lives).
2. Include at the very top of each entry PHP file:
   ```php
   <?php
   require __DIR__ . '/security/headers.php';
   require __DIR__ . '/security/session_boot.php';
   ```
3. Move sensitive/user-uploaded files out of public document root when possible.
   For public assets, put them in `public_uploads/` (PHP execution blocked).
4. For private assets, place into `/protected_uploads/` (create sibling folder one level above web root if possible) and serve with `security/download.php` using `generate_signed_url.php`.
5. If you break front-end assets due to CSP, whitelist necessary domains in:
   - `.htaccess` header `Content-Security-Policy`
   - `security/headers.php` CSP string

## Notes

- On localhost (no SSL), keep the HTTPS redirect commented.
- On production with SSL, enable the redirect in `.htaccess`.
- If Apache lacks `mod_headers`, remove header directives or ask hosting to enable it.
- Always keep PHP/Apache and dependencies updated.
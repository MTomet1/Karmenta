<?php
   require __DIR__ . '/security/headers.php';
   require __DIR__ . '/security/session_boot.php';
$currentScript = basename($_SERVER['PHP_SELF']);

if (isset($_GET['menu']) && !is_numeric($_GET['menu'])) {
    http_response_code(400);
    exit('<h2 style="color:red;">Nevaljani parametar (menu)!</h2>');
}
if ($currentScript !== 'Wishlist.php' && isset($_GET['action']) && !is_numeric($_GET['action'])) {
    http_response_code(400);
    exit('<h2 style="color:red;">Nevaljani parametar (action)!</h2>');
}
if (isset($_GET['id']) && !is_numeric($_GET['id'])) {
    http_response_code(400);
    exit('<h2 style="color:red;">Nevaljani parametar (id)!</h2>');
}

// DB connect...

	# Connect to MySQL database
$MySQL = mysqli_connect("localhost", "root", "", "karmenta");

if (!$MySQL) {
    die("Greška spajanja: " . mysqli_connect_error());
}
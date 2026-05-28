<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';

define('__APP__', TRUE);
include("dbconn.php");

if (isset($_GET['menu']))   { $menu   = (int)$_GET['menu']; }
if (isset($_GET['action'])) { $action = (int)$_GET['action']; }
if (!isset($_POST['_action_'])) { $_POST['_action_'] = FALSE; }
if (!isset($menu)) { $menu = 1; }

/* Redirect wishlist to standalone */
if ($menu === 8) {
    header("Location: Wishlist.php");
    exit;
}

$bodyClass = 'page-default';
switch ($menu) {
  case 1: $bodyClass = 'page-home'; break;
  case 2: $bodyClass = 'page-cars'; break;
  case 3: $bodyClass = 'page-contact'; break;
  case 4: $bodyClass = 'page-about'; break;
  case 5: $bodyClass = 'page-register'; break;
  case 6: $bodyClass = 'page-signin'; break;
  case 7: $bodyClass = 'page-admin'; break;
  case 9:  $bodyClass = 'page-auth'; break;
  case 10: $bodyClass = 'page-auth'; break;
  case 11: $bodyClass = 'page-auth'; break;
  default: $bodyClass = 'page-default';
}

include_once("functions.php");
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Karmenta — Vaš premium automobil čeka. Pažljivo odabrana ponuda vozila svih kategorija.">
    <meta name="author" content="matijatometic@gmail.com">
    <link rel="icon" href="Gallery/Karmenta_2_cropped-removebg-preview.ico" type="image/x-icon">
    <link rel="shortcut icon" href="Gallery/Karmenta_2_cropped-removebg-preview.ico" type="image/x-icon">
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;1,9..40,300&display=swap" rel="stylesheet">
    <!-- Icons -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
    <!-- Styles -->
    <link rel="stylesheet" href="style1.css">
    <title>Karmenta<?php if($menu===2){ echo ' — Automobili'; } elseif($menu===3){ echo ' — Kontakt'; } elseif($menu===4){ echo ' — O nama'; } elseif($menu===5){ echo ' — Registracija'; } elseif($menu===6){ echo ' — Prijava'; } elseif($menu===7){ echo ' — Admin'; } ?></title>
</head>
<body class="<?= htmlspecialchars($bodyClass, ENT_QUOTES, 'UTF-8') ?>">

<?php if ($menu == 1): ?>
<div class="video-wrapper">
    <video class="bg-video" autoplay muted loop playsinline>
        <source src="Gallery/854671-hd_1920_1080_25fps.mp4" type="video/mp4">
    </video>
    <div class="video-overlay"></div>
</div>
<?php endif; ?>

<?php include("menu.php"); ?>

<main>
    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']);
    }
    if (!isset($menu) || $menu == 1)  { include("home.php"); }
    elseif ($menu == 2)               { include("Cars.php"); }
    elseif ($menu == 3)               { include("contact.php"); }
    elseif ($menu == 4)               { include("about-us.php"); }
    elseif ($menu == 5)               { include("register.php"); }
    elseif ($menu == 6)               { include("signin.php"); }
    elseif ($menu == 7)               { include("admin.php"); }
    elseif ($menu == 9)               { include("change-password.php"); }
    elseif ($menu == 10)              { include("forgot-password.php"); }
    elseif ($menu == 11)              { include("reset-password.php"); }
    ?>
</main>

<footer class="site-footer">
    <div class="footer-content">
        <h2>Karmenta</h2>
        <p>Mjesto sigurne kupnje. Nudimo velik izbor kvalitetnih vozila i najbolju uslugu za naše korisnike.</p>
        <p>Kontakt: +385 98 415 105</p>
        <div class="footer-social">
            <a href="https://www.facebook.com/" target="_blank"><img src="Gallery/facebook.png" alt="Facebook"></a>
            <a href="https://www.instagram.com/" target="_blank"><img src="Gallery/insta.png" alt="Instagram"></a>
            <a href="https://www.linkedin.com/" target="_blank"><img src="Gallery/linkedin.png" alt="LinkedIn"></a>
        </div>
    </div>
    <div class="footer-bottom">&copy; <?= date("Y") ?> Matija Tometić. All rights reserved.</div>
</footer>

</body>
</html>

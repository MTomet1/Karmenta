<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';

$currentMenu = isset($_GET['menu']) ? (int)$_GET['menu'] : 1;
$isWishlist  = (basename($_SERVER['PHP_SELF']) === 'Wishlist.php');
?>
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">

<section class="header">
    <nav>
        <a href="index.php"><img src="Gallery/Karmenta_2_cropped-removebg-preview.ico" alt="Karmenta"></a>
        <div class="nav-links" id="navlinks">
            <ul>
                <li><a href="index.php?menu=1"<?= ($currentMenu===1 && !$isWishlist) ? ' class="active"' : '' ?>>Početna</a></li>
                <li><a href="index.php?menu=2"<?= ($currentMenu===2) ? ' class="active"' : '' ?>>Automobili</a></li>
                <li><a href="index.php?menu=3"<?= ($currentMenu===3) ? ' class="active"' : '' ?>>Kontakt</a></li>
                <li><a href="index.php?menu=4"<?= ($currentMenu===4) ? ' class="active"' : '' ?>>O nama</a></li>
                <li><a href="Wishlist.php"<?= $isWishlist ? ' class="active"' : '' ?>>Wishlist</a></li>
                <?php if (isset($_SESSION['user']['valid']) && $_SESSION['user']['valid'] === 'true'): ?>
                <li><a href="index.php?menu=7"<?= ($currentMenu===7) ? ' class="active"' : '' ?>>Admin</a></li>
                <li><a href="signout.php">Odjava</a></li>
                <?php endif; ?>
            </ul>
        </div>
        <i class="fa fa-bars" onclick="showmenu()"></i>
    </nav>
    <div class="menu-overlay"></div>
</section>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const menu    = document.querySelector(".nav-links");
  const toggle  = document.querySelector("nav .fa-bars");
  const overlay = document.querySelector(".menu-overlay");
  if (!menu || !toggle || !overlay) return;

  const closeBtn = document.createElement("i");
  closeBtn.classList.add("fa", "fa-times");
  closeBtn.style.display  = "none";
  closeBtn.style.cursor   = "pointer";
  closeBtn.style.color    = "white";
  closeBtn.style.fontSize = "24px";
  closeBtn.style.position = "fixed";
  closeBtn.style.top      = "20px";
  closeBtn.style.right    = "25px";
  closeBtn.style.zIndex   = "10000000";
  toggle.parentNode.insertBefore(closeBtn, toggle.nextSibling);

  function openMenu() {
    menu.classList.add("show");
    overlay.classList.add("active");
    document.body.classList.add("menu-open");
    toggle.style.display   = "none";
    closeBtn.style.display = "block";
  }

  function closeMenu() {
    menu.classList.remove("show");
    overlay.classList.remove("active");
    document.body.classList.remove("menu-open");
    toggle.style.display   = "block";
    closeBtn.style.display = "none";
  }

  toggle.addEventListener("click", openMenu);
  closeBtn.addEventListener("click", closeMenu);
  overlay.addEventListener("click", closeMenu);
  menu.addEventListener("click", e => {
    if (e.target.tagName === "A") closeMenu();
  });
});

function showmenu() {}
</script>

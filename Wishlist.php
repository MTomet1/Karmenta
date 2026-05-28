<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';
include 'dbconn.php';
?>
<!DOCTYPE html>
<html lang="hr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist — Karmenta</title>
    <link rel="stylesheet" href="style1.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="icon" href="Gallery/Karmenta_2_cropped-removebg-preview.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">


</head>
<body class="page-wishlist">

<div class="wl-bg"></div>

<?php include 'menu.php'; ?>

<main>
    <div class="wl-shell">

        <!-- Page header -->
        <div class="wl-header">
            <div class="wl-header-accent"></div>
            <div>
                <div class="wl-header-sub">Tvoja kolekcija</div>
                <div class="wl-header-label">Wishlist</div>
            </div>
        </div>

        <div class="wl-divider"></div>

        <!-- Count -->
        <div class="wl-count">
            Automobili <span id="wl-count-badge">—</span>
        </div>

        <!-- Grid (populated by JS) -->
        <div class="wl-grid" id="wishlist-grid">
            <!-- Skeleton placeholders while JS loads -->
            <?php for ($i = 0; $i < 3; $i++): ?>
            <div class="wl-skeleton">
                <div class="wl-skeleton-img"></div>
                <div class="wl-skeleton-body">
                    <div class="wl-skeleton-line long"></div>
                    <div class="wl-skeleton-line short"></div>
                    <div class="wl-skeleton-line short"></div>
                    <div class="wl-skeleton-btn"></div>
                </div>
            </div>
            <?php endfor; ?>
        </div>

    </div>
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
    <div class="footer-bottom">&copy; <?php echo date("Y"); ?> Matija Tometić. All rights reserved.</div>
</footer>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const grid  = document.getElementById("wishlist-grid");
    const badge = document.getElementById("wl-count-badge");
    let wishlist = [];

    try {
        wishlist = JSON.parse(localStorage.getItem("wishlist") || "[]");
    } catch (e) { wishlist = []; }

    badge.textContent = wishlist.length;

    if (wishlist.length === 0) {
        grid.innerHTML = `
            <div class="wl-empty">
                <span class="wl-empty-icon">♡</span>
                <p>Nema spremljenih automobila</p>
                <a href="index.php?menu=2" class="btn">Pregledaj automobile</a>
            </div>`;
        return;
    }

    fetch("load_wishlist.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ wishlist })
    })
    .then(res => {
        if (!res.ok) throw new Error("Network error");
        return res.text();
    })
    .then(html => {
        grid.innerHTML = html || `
            <div class="wl-empty">
                <span class="wl-empty-icon">♡</span>
                <p>Nema pronađenih automobila</p>
            </div>`;
        badge.textContent = grid.querySelectorAll(".car-card").length;
    })
    .catch(() => {
        grid.innerHTML = `
            <div class="wl-empty">
                <span class="wl-empty-icon">⚠</span>
                <p>Greška pri učitavanju. Pokušajte ponovo.</p>
            </div>`;
    });
});

/* ── Remove card on click ── */
document.addEventListener("click", e => {
    const btn = e.target.closest(".remove-from-wishlist");
    if (!btn) return;

    const card = btn.closest(".car-card");
    if (card) {
        card.style.transition = "opacity 0.3s ease, transform 0.3s ease";
        card.style.opacity    = "0";
        card.style.transform  = "scale(0.94)";
    }

    setTimeout(() => {
        const id = parseInt(btn.dataset.id, 10);
        let wishlist = [];
        try { wishlist = JSON.parse(localStorage.getItem("wishlist") || "[]"); } catch(e){}
        wishlist = wishlist.filter(x => x !== id);
        localStorage.setItem("wishlist", JSON.stringify(wishlist));
        location.reload();
    }, 300);
});
</script>

</body>
</html>
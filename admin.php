<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';
require __DIR__ . '/security/csrf.php';

if (!isset($_SESSION['user']['valid']) || $_SESSION['user']['valid'] !== 'true') {
    $_SESSION['message'] = '<p style="text-align:center;color:white;">Morate se prijaviti.</p>';
    header("Location: index.php?menu=6");
    exit;
}

if (!isset($action)) { $action = 1; }

/* Stats */
$statCars  = 0;
$statUsers = 0;
if (isset($MySQL)) {
    $r = mysqli_query($MySQL, "SELECT COUNT(*) AS c FROM cars");
    if ($r) { $row = mysqli_fetch_assoc($r); $statCars = (int)$row['c']; }
    $r2 = mysqli_query($MySQL, "SELECT COUNT(*) AS c FROM users");
    if ($r2) { $row2 = mysqli_fetch_assoc($r2); $statUsers = (int)$row2['c']; }
}
?>

<div class="admin-panel">

    <div class="section-header" style="margin-bottom:28px;">
        <div class="section-accent"></div>
        <div>
            <div class="section-label">Dobrodošli, <?= htmlspecialchars($_SESSION['user']['firstname'] ?? 'Admin') ?></div>
            <div class="section-title">Administration</div>
        </div>
    </div>

    <!-- Stats -->
    <div class="adm-stats">
        <div class="adm-stat">
            <div class="adm-stat-number"><?= $statCars ?></div>
            <div class="adm-stat-label">Automobila u ponudi</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-number"><?= $statUsers ?></div>
            <div class="adm-stat-label">Registriranih korisnika</div>
        </div>
        <div class="adm-stat">
            <div class="adm-stat-number"><?= date('Y') ?></div>
            <div class="adm-stat-label">Aktivna godina</div>
        </div>
    </div>

    <!-- Nav cards -->
    <div class="admin-nav">
        <a href="index.php?menu=7&action=1" class="admin-card <?= ($action==1) ? 'active' : '' ?>">
            <span>👥</span>
            <h3>Users</h3>
            <p>Upravljaj registriranim korisnicima</p>
        </a>
        <a href="index.php?menu=7&action=2" class="admin-card <?= ($action==2) ? 'active' : '' ?>">
            <span>🚗</span>
            <h3>Cars</h3>
            <p>Upravljaj oglasima automobila</p>
        </a>
        <a href="index.php?menu=9" class="admin-card <?= ($menu==9) ? 'active' : '' ?>">
            <span>🔑</span>
            <h3>Lozinka</h3>
            <p>Promijeni administratorsku lozinku</p>
        </a>
    </div>

    <!-- Content -->
    <div class="admin-content">
        <?php
        if ($action == 1)      { include("admin/users.php"); }
        elseif ($action == 2)  { include("admin/cars.php"); }
        ?>
    </div>

</div>

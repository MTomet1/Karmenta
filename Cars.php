<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';
include 'dbconn.php';

/* ── Column existence checks ── */
$colCheck   = mysqli_query($MySQL, "SHOW COLUMNS FROM cars LIKE 'fuel_type'");
$hasExtras  = ($colCheck && mysqli_num_rows($colCheck) > 0);
$viewsCheck = mysqli_query($MySQL, "SHOW COLUMNS FROM cars LIKE 'views'");
$hasViews   = ($viewsCheck && mysqli_num_rows($viewsCheck) > 0);

/* ── Sort whitelist ── */
$allowedSorts = [
    'views_desc'   => $hasViews ? 'views DESC' : 'id DESC',
    'price_asc'    => 'price ASC',
    'price_desc'   => 'price DESC',
    'year_desc'    => 'year DESC',
    'year_asc'     => 'year ASC',
    'mileage_asc'  => 'mileage ASC',
    'mileage_desc' => 'mileage DESC',
];
$sortKey = (isset($_GET['sort']) && array_key_exists($_GET['sort'], $allowedSorts)) ? $_GET['sort'] : 'views_desc';
$orderBy = $allowedSorts[$sortKey];

/* ── Pagination ── */
$perPage = 12;
$page    = max(1, (int)($_GET['page'] ?? 1));

/* ── Build prepared statement ── */
$conditions = [];
$types      = '';
$params     = [];

if (!empty($_GET['search'])) {
    $s = '%' . $_GET['search'] . '%';
    $conditions[] = "(brand LIKE ? OR model LIKE ?)";
    $types .= 'ss';
    $params[] = $s;
    $params[] = $s;
}
if (!empty($_GET['brand'])) {
    $conditions[] = "brand = ?";
    $types .= 's';
    $params[] = $_GET['brand'];
}
if (!empty($_GET['year'])) {
    $conditions[] = "year = ?";
    $types .= 'i';
    $params[] = (int)$_GET['year'];
}
if ($hasExtras && !empty($_GET['fuel'])) {
    $conditions[] = "fuel_type = ?";
    $types .= 's';
    $params[] = $_GET['fuel'];
}
if (!empty($_GET['price_max'])) {
    $conditions[] = "price <= ?";
    $types .= 'd';
    $params[] = (float)$_GET['price_max'];
}

$whereSQL = $conditions ? " WHERE " . implode(" AND ", $conditions) : "";

/* Total count for pagination */
$countStmt = $MySQL->prepare("SELECT COUNT(*) FROM cars" . $whereSQL);
if ($params) $countStmt->bind_param($types, ...$params);
$countStmt->execute();
$countStmt->bind_result($totalCount);
$countStmt->fetch();
$countStmt->close();

$totalPages = max(1, (int)ceil($totalCount / $perPage));
$page       = min($page, $totalPages);
$offset     = ($page - 1) * $perPage;

/* Main query */
$mainTypes  = $types . 'ii';
$mainParams = array_merge($params, [$perPage, $offset]);
$mainStmt   = $MySQL->prepare("SELECT * FROM cars" . $whereSQL . " ORDER BY $orderBy LIMIT ? OFFSET ?");
$mainStmt->bind_param($mainTypes, ...$mainParams);
$mainStmt->execute();
$result = $mainStmt->get_result();

/* ── Filter options ── */
$brandsQ = mysqli_query($MySQL, "SELECT DISTINCT brand FROM cars ORDER BY brand");
$yearsQ  = mysqli_query($MySQL, "SELECT DISTINCT year FROM cars ORDER BY year DESC");
$fuelsQ  = $hasExtras
    ? mysqli_query($MySQL, "SELECT DISTINCT fuel_type FROM cars WHERE fuel_type IS NOT NULL AND fuel_type != '' ORDER BY fuel_type")
    : false;

$searchVal   = htmlspecialchars($_GET['search'] ?? '');
$brandVal    = $_GET['brand'] ?? '';
$yearVal     = $_GET['year'] ?? '';
$fuelVal     = $_GET['fuel'] ?? '';
$priceMaxVal = $_GET['price_max'] ?? '';

$activeFilters = count(array_filter([$searchVal, $brandVal, $yearVal, $fuelVal, $priceMaxVal]));
if ($sortKey !== 'views_desc') $activeFilters++;
?>

<div class="cars-shell">

    <div class="section-header">
        <div class="section-accent"></div>
        <div>
            <div class="section-label">Naša ponuda</div>
            <div class="section-title">Automobili</div>
        </div>
    </div>

    <!-- Filter -->
    <form method="get" action="index.php" class="cf-form" id="filterForm">
        <input type="hidden" name="menu" value="2">

        <!-- Search row — always visible -->
        <div class="cf-top">
            <div class="cf-search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                <input type="text" name="search" placeholder="Pretraži brand, model…" value="<?= $searchVal ?>">
            </div>
            <button type="submit" class="cf-btn-search">Pretraži</button>
            <button type="button" class="cf-btn-toggle" id="cfToggle" aria-expanded="false">
                Filtri<?php if ($activeFilters > 0): ?> <span class="cf-badge"><?= $activeFilters ?></span><?php endif; ?>
                <svg class="cf-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>
            </button>
            <?php if ($conditions || $sortKey !== 'views_desc'): ?>
            <a href="index.php?menu=2" class="cf-btn-reset" title="Resetiraj filtere">✕</a>
            <?php endif; ?>
        </div>

        <!-- Expandable filters -->
        <div class="cf-panel" id="cfPanel"<?= $activeFilters > 0 ? ' style="display:grid;"' : '' ?>>
            <div class="filter-field">
                <label>Brand</label>
                <select name="brand">
                    <option value="">Svi brendovi</option>
                    <?php while ($br = mysqli_fetch_assoc($brandsQ)): ?>
                    <option value="<?= htmlspecialchars($br['brand']) ?>"<?= ($brandVal === $br['brand']) ? ' selected' : '' ?>><?= htmlspecialchars($br['brand']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="filter-field">
                <label>Godina</label>
                <select name="year">
                    <option value="">Sve godine</option>
                    <?php while ($yr = mysqli_fetch_assoc($yearsQ)): ?>
                    <option value="<?= (int)$yr['year'] ?>"<?= ($yearVal == $yr['year']) ? ' selected' : '' ?>><?= (int)$yr['year'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php if ($fuelsQ && mysqli_num_rows($fuelsQ) > 0): ?>
            <div class="filter-field">
                <label>Gorivo</label>
                <select name="fuel">
                    <option value="">Sve vrste</option>
                    <?php while ($fu = mysqli_fetch_assoc($fuelsQ)): ?>
                    <option value="<?= htmlspecialchars($fu['fuel_type']) ?>"<?= ($fuelVal === $fu['fuel_type']) ? ' selected' : '' ?>><?= htmlspecialchars($fu['fuel_type']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <?php endif; ?>
            <div class="filter-field">
                <label>Max. cijena (EUR)</label>
                <input type="number" name="price_max" placeholder="npr. 25000" value="<?= htmlspecialchars($priceMaxVal) ?>">
            </div>
            <div class="filter-field">
                <label>Sortiranje</label>
                <select name="sort">
                    <option value="views_desc"<?= $sortKey==='views_desc' ? ' selected':'' ?>>Najpregledavaniji</option>
                    <option value="price_asc"<?= $sortKey==='price_asc' ? ' selected':'' ?>>Cijena ↑</option>
                    <option value="price_desc"<?= $sortKey==='price_desc' ? ' selected':'' ?>>Cijena ↓</option>
                    <option value="year_desc"<?= $sortKey==='year_desc' ? ' selected':'' ?>>Godište: najnovije</option>
                    <option value="year_asc"<?= $sortKey==='year_asc' ? ' selected':'' ?>>Godište: najstarije</option>
                    <option value="mileage_asc"<?= $sortKey==='mileage_asc' ? ' selected':'' ?>>Kilometraža ↑</option>
                    <option value="mileage_desc"<?= $sortKey==='mileage_desc' ? ' selected':'' ?>>Kilometraža ↓</option>
                </select>
            </div>
        </div>
    </form>

<script>
    (function() {
        const toggle = document.getElementById('cfToggle');
        const panel  = document.getElementById('cfPanel');
        if (!toggle || !panel) return;
        toggle.addEventListener('click', () => {
            const open = panel.style.display === 'grid';
            panel.style.display = open ? 'none' : 'grid';
            toggle.setAttribute('aria-expanded', String(!open));
        });
    })();
</script>

    <!-- Car grid -->
    <div class="cars-grid" id="carsGrid">
        <?php
        if (!$result || $result->num_rows === 0):
        ?>
        <div class="cars-empty">
            <span class="cars-empty-icon">🔍</span>
            <p>Nema automobila za odabrane filtere</p>
        </div>
        <?php
        else:
            while ($row = $result->fetch_assoc()):
                $pic = $row['picture'];
                if (!$pic) {
                    $imgQ = mysqli_query($MySQL, "SELECT filename FROM car_images WHERE car_id=" . (int)$row['id'] . " LIMIT 1");
                    if ($imgQ && $img = mysqli_fetch_assoc($imgQ)) $pic = $img['filename'];
                }
                $title = htmlspecialchars($row['brand'] . ' ' . $row['model']);
                $year  = htmlspecialchars($row['year']);
                $price = number_format((float)$row['price'], 0, ',', '.');
                $desc  = htmlspecialchars(mb_strimwidth($row['description'] ?? '', 0, 120, '…'));
                $id    = (int)$row['id'];
                $fuel  = htmlspecialchars($row['fuel_type'] ?? '');
                $km    = $row['mileage'] ? number_format((int)$row['mileage'], 0, ',', '.') . ' km' : '';
                $trans = htmlspecialchars($row['transmission'] ?? '');
        ?>
        <div class="car-card">
            <div class="car-img-wrap">
                <?php if ($pic): ?>
                <img src="Gallery/cars/<?= htmlspecialchars($pic) ?>" alt="<?= $title ?>" loading="lazy">
                <?php else: ?>
                <img src="Gallery/no-car-img.png" alt="Nema slike" loading="lazy">
                <?php endif; ?>
                <button class="car-wishlist-btn add-to-wishlist" data-id="<?= $id ?>" title="Spremi u wishlist">♡</button>
            </div>
            <div class="car-card-details">
                <div class="car-title"><?= $title ?></div>
                <div class="car-info">
                    <span><b>Godina:</b> <?= $year ?></span>
                    <?php if ($km): ?><span><b>km:</b> <?= $km ?></span><?php endif; ?>
                </div>
                <?php if ($fuel || $trans): ?>
                <div class="car-chips">
                    <?php if ($fuel): ?><span class="chip"><?= $fuel ?></span><?php endif; ?>
                    <?php if ($trans): ?><span class="chip"><?= $trans ?></span><?php endif; ?>
                </div>
                <?php endif; ?>
                <div class="car-price"><?= $price ?> EUR</div>
                <?php if ($desc): ?><div class="car-desc"><?= $desc ?></div><?php endif; ?>
                <div class="car-actions">
                    <button class="btn btn-primary open-car-details" data-id="<?= $id ?>">Detalji</button>
                    <button class="btn btn-ghost add-to-wishlist" data-id="<?= $id ?>">♡ Spremi</button>
                </div>
            </div>
        </div>
        <?php
            endwhile;
        endif;
        ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <div style="display:flex;justify-content:center;align-items:center;gap:6px;margin-top:32px;flex-wrap:wrap;">
        <?php
        $baseParams = $_GET;
        unset($baseParams['page']);
        for ($p = 1; $p <= $totalPages; $p++):
            $href = htmlspecialchars('?' . http_build_query(array_merge($baseParams, ['page' => $p])));
            $isActive = ($p === $page);
        ?>
        <a href="<?= $href ?>" style="display:inline-flex;align-items:center;justify-content:center;min-width:38px;height:38px;padding:0 10px;border-radius:8px;border:1px solid <?= $isActive ? 'var(--blue)' : 'var(--border)' ?>;background:<?= $isActive ? 'var(--blue)' : 'var(--card)' ?>;color:<?= $isActive ? '#fff' : 'var(--text)' ?>;font-size:0.88rem;text-decoration:none;transition:all 0.2s;"><?= $p ?></a>
        <?php endfor; ?>
    </div>
    <div style="text-align:center;margin-top:10px;font-size:0.78rem;color:var(--text-muted);">
        Stranica <?= $page ?> od <?= $totalPages ?> &mdash; <?= $totalCount ?> automobila
    </div>
    <?php endif; ?>

</div>

<!-- Car details modal -->
<div id="carModal" class="modal">
    <div class="modal-content" id="carModalContent">
        <span class="close" id="carModalClose">&times;</span>
        <div style="padding:60px;text-align:center;color:rgba(232,234,240,0.4);font-size:0.9rem;letter-spacing:0.1em;">UČITAVANJE…</div>
    </div>
</div>

<!-- Lightbox -->
<div id="lightbox-overlay">
    <span class="close-lightbox" id="closeLightbox">&times;</span>
    <img id="lightboxImg" src="" alt="Pregled slike">
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    ['carModal', 'lightbox-overlay'].forEach(id => {
        const el = document.getElementById(id);
        if (el) document.body.appendChild(el);
    });
});

function getWishlist() { return JSON.parse(localStorage.getItem('wishlist') || '[]'); }
function saveWishlist(wl) { localStorage.setItem('wishlist', JSON.stringify(wl)); }

function refreshWishlistButtons() {
    const wl = getWishlist();
    document.querySelectorAll('.add-to-wishlist').forEach(btn => {
        const id = parseInt(btn.dataset.id);
        const inList = wl.includes(id);
        if (btn.classList.contains('btn')) {
            btn.textContent = inList ? '♥ Spremljeno' : '♡ Spremi';
            btn.classList.toggle('in-wishlist', inList);
        } else {
            btn.textContent = inList ? '♥' : '♡';
            btn.classList.toggle('in-wishlist', inList);
        }
    });
}
refreshWishlistButtons();

document.addEventListener('click', e => {
    const btn = e.target.closest('.add-to-wishlist');
    if (!btn) return;
    e.preventDefault();
    const id = parseInt(btn.dataset.id);
    let wl = getWishlist();
    if (wl.includes(id)) { wl = wl.filter(x => x !== id); } else { wl.push(id); }
    saveWishlist(wl);
    refreshWishlistButtons();
});

function execInjectedScripts(container) {
    container.querySelectorAll('script').forEach(old => {
        const s = document.createElement('script');
        s.textContent = old.textContent;
        document.body.appendChild(s);
        document.body.removeChild(s);
    });
}

document.querySelectorAll('.open-car-details').forEach(btn => {
    btn.addEventListener('click', function () {
        const modal        = document.getElementById('carModal');
        const modalContent = document.getElementById('carModalContent');
        const oldGm = document.getElementById('galleryModal');
        if (oldGm) oldGm.remove();
        modalContent.innerHTML = '<span class="close" id="carModalClose">&times;</span><div style="padding:60px;text-align:center;color:rgba(232,234,240,0.4);font-size:0.9rem;letter-spacing:0.1em;">UČITAVANJE…</div>';
        modal.classList.add('show');
        document.getElementById('carModalClose').onclick = () => modal.classList.remove('show');
        fetch('car-details.php?id=' + this.dataset.id)
            .then(r => r.text())
            .then(html => {
                modalContent.innerHTML = '<span class="close" id="carModalClose">&times;</span>' + html;
                document.getElementById('carModalClose').onclick = () => modal.classList.remove('show');
                const gm = document.getElementById('galleryModal');
                if (gm) document.body.appendChild(gm);
                execInjectedScripts(modalContent);
            })
            .catch(() => {
                modalContent.innerHTML = '<span class="close" id="carModalClose">&times;</span><div style="padding:40px;text-align:center;color:#e74c3c;">Greška pri učitavanju</div>';
                document.getElementById('carModalClose').onclick = () => modal.classList.remove('show');
            });
    });
});

document.getElementById('carModal').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('show');
});
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.getElementById('carModal').classList.remove('show');
});

document.addEventListener('click', e => {
    if (e.target.matches('.car-gallery img')) {
        document.getElementById('lightboxImg').src = e.target.src;
        document.getElementById('lightbox-overlay').classList.add('show');
    }
    if (e.target.matches('#closeLightbox') || e.target.matches('#lightbox-overlay')) {
        document.getElementById('lightbox-overlay').classList.remove('show');
    }
});
</script>

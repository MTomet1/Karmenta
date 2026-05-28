<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';
require __DIR__ . '/api_helpers.php';
include 'dbconn.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo '<div class="cd-body"><p style="color:var(--red);">Greška: nevaljani ID.</p></div>';
    exit;
}

$id     = (int)$_GET['id'];
$result = mysqli_query($MySQL, "SELECT * FROM cars WHERE id = $id LIMIT 1");

if (!$result || mysqli_num_rows($result) === 0) {
    echo '<div class="cd-body"><p style="color:var(--red);">Auto nije pronađen.</p></div>';
    exit;
}

$car    = mysqli_fetch_assoc($result);
mysqli_query($MySQL, "UPDATE cars SET views = views + 1 WHERE id = $id");
$images = [];

$imgsQ = mysqli_query($MySQL, "SELECT filename FROM car_images WHERE car_id = $id ORDER BY id ASC");
if ($imgsQ && mysqli_num_rows($imgsQ) > 0) {
    while ($img = mysqli_fetch_assoc($imgsQ)) $images[] = $img['filename'];
} elseif ($car['picture']) {
    $images[] = $car['picture'];
}

$title    = htmlspecialchars($car['brand'] . ' ' . $car['model']);
$priceEur = (float)$car['price'];
$price    = number_format($priceEur, 0, ',', '.');
$rates    = get_exchange_rates();              // vanjski API #1 (tečaj), null ako nedostupan
?>


<div class="car-details-container">

    <!-- ── Gallery (old style) ─────────────────────── -->
    <?php if (!empty($images)): ?>
    <div style="padding: 20px 24px 0;">
        <img class="cd-main-img" id="cdMainImg"
             src="Gallery/cars/<?= htmlspecialchars($images[0]) ?>"
             alt="<?= $title ?>"
             onclick="openGallery(0)">

        <?php if (count($images) > 1): ?>
        <div class="cd-thumbs" id="cdThumbs">
            <?php foreach ($images as $i => $img): ?>
            <img src="Gallery/cars/<?= htmlspecialchars($img) ?>"
                 alt="<?= $title ?> - <?= $i + 1 ?>"
                 class="<?= $i === 0 ? 'active' : '' ?>"
                 onclick="setMain(<?= $i ?>)">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- ── Text / specs (new style) ──────────────────── -->
    <div class="cd-body">
        <div style="display:flex;justify-content:space-between;align-items:baseline;gap:12px;">
            <div class="cd-title"><?= $title ?></div>
            <?php if (isset($car['views'])): ?>
            <div style="font-size:0.78rem;color:var(--text-muted);white-space:nowrap;flex-shrink:0;">👁 <?= number_format((int)$car['views'], 0, ',', '.') ?> prikaza</div>
            <?php endif; ?>
        </div>
        <div class="cd-price">
            <span id="cdPriceVal"><?= $price ?></span>
            <?php if ($rates): ?>
            <select id="cdCurSelect" aria-label="Valuta"
                    style="font:inherit;color:inherit;background:transparent;border:1px solid var(--text-muted,#888);border-radius:6px;padding:2px 6px;cursor:pointer;vertical-align:middle;">
                <option value="EUR">EUR</option>
                <option value="USD">USD</option>
                <option value="GBP">GBP</option>
                <option value="CHF">CHF</option>
            </select>
            <?php else: ?>
            EUR
            <?php endif; ?>
        </div>
        <?php if ($rates): ?>
        <div style="font-size:0.74rem;color:var(--text-muted,#888);margin-top:-6px;">
            Tečaj uživo (ECB / Frankfurter API)
        </div>
        <?php endif; ?>

        <div class="cd-specs">
            <div class="cd-spec">
                <div class="cd-spec-label">Godina</div>
                <div class="cd-spec-value"><?= htmlspecialchars($car['year']) ?></div>
            </div>
            <?php if (!empty($car['fuel_type'])): ?>
            <div class="cd-spec">
                <div class="cd-spec-label">Gorivo</div>
                <div class="cd-spec-value"><?= htmlspecialchars($car['fuel_type']) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($car['mileage'])): ?>
            <div class="cd-spec">
                <div class="cd-spec-label">Kilometraža</div>
                <div class="cd-spec-value"><?= number_format((int)$car['mileage'], 0, ',', '.') ?> km</div>
            </div>
            <?php endif; ?>
            <?php if (!empty($car['transmission'])): ?>
            <div class="cd-spec">
                <div class="cd-spec-label">Mjenjač</div>
                <div class="cd-spec-value"><?= htmlspecialchars($car['transmission']) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($car['engine'])): ?>
            <div class="cd-spec">
                <div class="cd-spec-label">Motor</div>
                <div class="cd-spec-value"><?= htmlspecialchars($car['engine']) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($car['color'])): ?>
            <div class="cd-spec">
                <div class="cd-spec-label">Boja</div>
                <div class="cd-spec-value"><?= htmlspecialchars($car['color']) ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($car['doors'])): ?>
            <div class="cd-spec">
                <div class="cd-spec-label">Vrata</div>
                <div class="cd-spec-value"><?= (int)$car['doors'] ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($car['seats'])): ?>
            <div class="cd-spec">
                <div class="cd-spec-label">Sjedala</div>
                <div class="cd-spec-value"><?= (int)$car['seats'] ?></div>
            </div>
            <?php endif; ?>
            <?php if (!empty($car['car_condition'])): ?>
            <div class="cd-spec">
                <div class="cd-spec-label">Stanje</div>
                <div class="cd-spec-value"><?= htmlspecialchars($car['car_condition']) ?></div>
            </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($car['description'])): ?>
        <div class="cd-description">
            <b>Opis:</b><br>
            <?= nl2br(htmlspecialchars($car['description'])) ?>
        </div>
        <?php endif; ?>

        <div class="cd-actions">
            <a href="index.php?menu=3" class="btn btn-primary">Pošalji poruku →</a>
            <button class="btn btn-ghost" data-id="<?= $id ?>" id="wl-btn-<?= $id ?>">♡ Wishlist</button>
        </div>
    </div>

</div>

<!-- Fullscreen gallery modal -->
<div id="galleryModal" class="gallery-modal" onclick="backdropClose(event)">
    <button type="button" class="gallery-close-btn" onclick="closeGallery(event)">&times;</button>
    <button type="button" class="gallery-arrow-btn gallery-arrow-left" onclick="prevImage(event)">&#10094;</button>
    <img id="galleryImage" alt="<?= $title ?>">
    <button type="button" class="gallery-arrow-btn gallery-arrow-right" onclick="nextImage(event)">&#10095;</button>
</div>

<script>
const images  = [<?php foreach ($images as $img): ?>"Gallery/cars/<?= htmlspecialchars($img, ENT_QUOTES) ?>",<?php endforeach; ?>];
let   current = 0;

function setMain(index) {
    current = index;
    document.getElementById('cdMainImg').src = images[index];
    document.querySelectorAll('#cdThumbs img').forEach((t, i) => t.classList.toggle('active', i === index));
}

function openGallery(index) {
    if (!images.length) return;
    current = index;
    document.getElementById('galleryModal').style.display = 'block';
    document.getElementById('galleryImage').src = images[current];
    document.body.style.overflow = 'hidden';
}
function closeGallery(e) {
    if (e) e.stopPropagation();
    document.getElementById('galleryModal').style.display = 'none';
    document.body.style.overflow = '';
}
function nextImage(e) { if (e) e.stopPropagation(); current = (current + 1) % images.length; document.getElementById('galleryImage').src = images[current]; }
function prevImage(e) { if (e) e.stopPropagation(); current = (current - 1 + images.length) % images.length; document.getElementById('galleryImage').src = images[current]; }
function backdropClose(e) { if (e.target.id === 'galleryModal') closeGallery(); }

document.addEventListener('keydown', e => {
    if (document.getElementById('galleryModal').style.display !== 'block') return;
    if (e.key === 'ArrowRight') nextImage();
    if (e.key === 'ArrowLeft')  prevImage();
    if (e.key === 'Escape')     closeGallery();
});

/* Wishlist sync */
(function () {
    const btn = document.getElementById('wl-btn-<?= $id ?>');
    if (!btn) return;
    const id  = <?= $id ?>;
    const wl  = JSON.parse(localStorage.getItem('wishlist') || '[]');
    if (wl.includes(id)) btn.textContent = '♥ Wishlist';

    btn.addEventListener('click', () => {
        let wl = JSON.parse(localStorage.getItem('wishlist') || '[]');
        if (wl.includes(id)) {
            wl = wl.filter(x => x !== id);
            btn.textContent = '♡ Wishlist';
        } else {
            wl.push(id);
            btn.textContent = '♥ Wishlist';
        }
        localStorage.setItem('wishlist', JSON.stringify(wl));
    });
})();

<?php if ($rates): ?>
/* Konverzija cijene preko tečaja (vanjski API – Frankfurter) */
(function () {
    const priceEur = <?= json_encode($priceEur) ?>;
    const rates    = <?= json_encode(['EUR' => 1] + $rates) ?>;
    const valEl    = document.getElementById('cdPriceVal');
    const sel      = document.getElementById('cdCurSelect');
    if (!valEl || !sel) return;

    sel.addEventListener('change', () => {
        const cur = sel.value;
        const amount = Math.round(priceEur * (rates[cur] || 1));
        valEl.textContent = amount.toLocaleString('de-DE');
    });
})();
<?php endif; ?>
</script>

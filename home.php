<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';

$brandsQ = mysqli_query($MySQL, "SELECT DISTINCT brand FROM cars ORDER BY brand");
$yearsQ  = mysqli_query($MySQL, "SELECT DISTINCT year FROM cars ORDER BY year DESC");
?>


<div class="search-shell">
    <div class="search-card">

        <div class="search-rhombus-wrap">
            <div class="search-rhombus">
                <img src="Gallery/Karmenta_2_cropped-removebg-preview.png" alt="Karmenta">
            </div>
        </div>

        <div class="search-eyebrow">
            <i></i>
            Karmenta &mdash; Zagreb
            <i></i>
        </div>

        <h1 class="search-title">Pretraga<br>Automobila</h1>

        <form class="search-form" method="get" action="index.php">
            <input type="hidden" name="menu" value="2">

            <input type="text" name="search" placeholder="Pretraga po brendu / modelu...">

            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;">
                <select name="brand">
                    <option value="">Svi brendovi</option>
                    <?php while ($brandR = mysqli_fetch_array($brandsQ)): ?>
                    <option value="<?= htmlspecialchars($brandR['brand']) ?>"><?= htmlspecialchars($brandR['brand']) ?></option>
                    <?php endwhile; ?>
                </select>

                <select name="year">
                    <option value="">Sve godine</option>
                    <?php while ($yearR = mysqli_fetch_array($yearsQ)): ?>
                    <option value="<?= (int)$yearR['year'] ?>"><?= (int)$yearR['year'] ?></option>
                    <?php endwhile; ?>
                </select>

                <input type="number" name="price_max" placeholder="Max. cijena (EUR)" min="0" step="500">
            </div>

            <div class="search-btn-wrap">
                <button type="submit" class="search-submit">Pretraži</button>
            </div>
        </form>

    </div>
</div>

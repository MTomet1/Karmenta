<?php
$menu   = $_GET['menu']   ?? 7;
$action = $_GET['action'] ?? 2;

/* ── ADD CAR ── */
if (isset($_POST['_action_']) && $_POST['_action_'] === 'add_car') {
    csrf_check();

    $brand        = $_POST['brand'];
    $model        = $_POST['model'];
    $year         = (int)$_POST['year'];
    $price        = (float)$_POST['price'];
    $description  = $_POST['description'] ?? '';
    $fuel_type    = $_POST['fuel_type'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $mileage      = !empty($_POST['mileage']) ? (int)$_POST['mileage'] : null;
    $color        = $_POST['color'] ?? '';
    $engine       = $_POST['engine'] ?? '';
    $doors        = !empty($_POST['doors']) ? (int)$_POST['doors'] : null;
    $seats        = !empty($_POST['seats']) ? (int)$_POST['seats'] : null;
    $car_cond     = $_POST['car_condition'] ?? 'Rabljeno';

    $stmt = $MySQL->prepare("INSERT INTO cars (brand, model, year, price, description, fuel_type, transmission, mileage, color, engine, doors, seats, car_condition) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidsssissiis", $brand, $model, $year, $price, $description, $fuel_type, $transmission, $mileage, $color, $engine, $doors, $seats, $car_cond);
    $stmt->execute();
    $ID = $MySQL->insert_id;
    $stmt->close();

    if (isset($_FILES['car_images']) && $ID) {
        $main_picture = "";
        foreach ($_FILES['car_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['car_images']['error'][$key] == UPLOAD_ERR_OK) {
                $ext = strtolower(strrchr($_FILES['car_images']['name'][$key], "."));
                if (in_array($ext, ['.jpg','.jpeg','.png','.gif','.webp'])) {
                    $img_name = $ID . '-' . uniqid() . $ext;
                    move_uploaded_file($tmp_name, __DIR__ . "/../Gallery/cars/" . $img_name);
                    if ($main_picture === "") $main_picture = $img_name;
                    mysqli_query($MySQL, "INSERT INTO car_images (car_id, filename) VALUES ($ID, '$img_name')");
                }
            }
        }
        if ($main_picture !== "") {
            mysqli_query($MySQL, "UPDATE cars SET picture='$main_picture' WHERE id=$ID");
        }
    }

    $_SESSION['message'] = '<p style="color:white;text-align:center;">Automobil uspješno dodan!</p>';
    echo '<script>window.location.replace("index.php?menu=7&action=2");</script>';
    exit;
}

/* ── EDIT CAR ── */
if (isset($_POST['_action_']) && $_POST['_action_'] === 'edit_car') {
    csrf_check();

    $eid          = (int)$_POST['edit'];
    $brand        = $_POST['brand'];
    $model        = $_POST['model'];
    $year         = (int)$_POST['year'];
    $price        = (float)$_POST['price'];
    $description  = $_POST['description'] ?? '';
    $fuel_type    = $_POST['fuel_type'] ?? '';
    $transmission = $_POST['transmission'] ?? '';
    $mileage      = !empty($_POST['mileage']) ? (int)$_POST['mileage'] : null;
    $color        = $_POST['color'] ?? '';
    $engine       = $_POST['engine'] ?? '';
    $doors        = !empty($_POST['doors']) ? (int)$_POST['doors'] : null;
    $seats        = !empty($_POST['seats']) ? (int)$_POST['seats'] : null;
    $car_cond     = $_POST['car_condition'] ?? 'Rabljeno';

    $stmt = $MySQL->prepare("UPDATE cars SET brand=?, model=?, year=?, price=?, description=?, fuel_type=?, transmission=?, mileage=?, color=?, engine=?, doors=?, seats=?, car_condition=? WHERE id=? LIMIT 1");
    $stmt->bind_param("ssidsssissiisi", $brand, $model, $year, $price, $description, $fuel_type, $transmission, $mileage, $color, $engine, $doors, $seats, $car_cond, $eid);
    $stmt->execute();
    $stmt->close();

    if (isset($_FILES['car_images']) && is_array($_FILES['car_images']['tmp_name'])) {
        $main_picture = "";
        foreach ($_FILES['car_images']['tmp_name'] as $key => $tmp_name) {
            if ($_FILES['car_images']['error'][$key] == UPLOAD_ERR_OK) {
                $ext = strtolower(strrchr($_FILES['car_images']['name'][$key], "."));
                if (in_array($ext, ['.jpg','.jpeg','.png','.gif','.webp'])) {
                    $img_name = $eid . '-' . uniqid() . $ext;
                    move_uploaded_file($tmp_name, __DIR__ . "/../Gallery/cars/" . $img_name);
                    if ($main_picture === "") $main_picture = $img_name;
                    mysqli_query($MySQL, "INSERT INTO car_images (car_id, filename) VALUES ($eid, '$img_name')");
                }
            }
        }
        if ($main_picture !== "") {
            mysqli_query($MySQL, "UPDATE cars SET picture='$main_picture' WHERE id=$eid");
        }
    }

    $_SESSION['message'] = '<p style="color:white;text-align:center;">Automobil izmijenjen!</p>';
    echo '<script>window.location.replace("index.php?menu=7&action=2");</script>';
    exit;
}

/* ── DELETE SINGLE IMAGE ── */
if (isset($_GET['delete_img']) && $_GET['delete_img'] !== '') {
    $imgId = (int)$_GET['delete_img'];
    $carId = (int)($_GET['car_id'] ?? 0);
    $r = mysqli_query($MySQL, "SELECT filename FROM car_images WHERE id=$imgId LIMIT 1");
    if ($row = mysqli_fetch_assoc($r)) {
        @unlink(__DIR__ . "/../Gallery/cars/" . $row['filename']);
        mysqli_query($MySQL, "DELETE FROM car_images WHERE id=$imgId LIMIT 1");
        /* Ako je bila glavna slika, postavi prvu sljedeću */
        $next = mysqli_query($MySQL, "SELECT filename FROM car_images WHERE car_id=$carId ORDER BY id ASC LIMIT 1");
        $newPic = ($nrow = mysqli_fetch_assoc($next)) ? $nrow['filename'] : '';
        mysqli_query($MySQL, "UPDATE cars SET picture='$newPic' WHERE id=$carId LIMIT 1");
    }
    echo '<script>window.location.replace("index.php?menu=7&action=2&edit=' . $carId . '");</script>';
    exit;
}

/* ── DELETE CAR ── */
if (isset($_GET['delete']) && $_GET['delete'] !== '') {
    $did = (int)$_GET['delete'];
    $r   = mysqli_query($MySQL, "SELECT picture FROM cars WHERE id=$did LIMIT 1");
    $row = mysqli_fetch_assoc($r);
    if ($row && $row['picture']) @unlink(__DIR__ . "/../Gallery/cars/" . $row['picture']);

    $imgs = mysqli_query($MySQL, "SELECT filename FROM car_images WHERE car_id=$did");
    while ($img = mysqli_fetch_assoc($imgs)) {
        @unlink(__DIR__ . "/../Gallery/cars/" . $img['filename']);
    }
    mysqli_query($MySQL, "DELETE FROM car_images WHERE car_id=$did");
    mysqli_query($MySQL, "DELETE FROM cars WHERE id=$did LIMIT 1");

    $_SESSION['message'] = '<p style="color:white;text-align:center;">Automobil obrisan!</p>';
    echo '<script>window.location.replace("index.php?menu=7&action=2");</script>';
    exit;
}

/* ── ADD FORM ── */
if (isset($_GET['add'])):
?>
<a href="index.php?menu=<?= $menu ?>&action=<?= $action ?>" class="adm-back">Natrag na listu</a>
<h2>Dodaj automobil</h2>

<form method="POST" enctype="multipart/form-data" class="adm-form">
    <input type="hidden" name="_action_" value="add_car">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div class="adm-field">
        <label>Brand *</label>
        <input type="text" name="brand" placeholder="npr. Volkswagen" required>
    </div>
    <div class="adm-field">
        <label>Model *</label>
        <input type="text" name="model" placeholder="npr. Golf" required>
    </div>
    <div class="adm-field">
        <label>Godina *</label>
        <input type="number" name="year" placeholder="2020" min="1950" max="2030" required>
    </div>
    <div class="adm-field">
        <label>Cijena (EUR) *</label>
        <input type="number" name="price" placeholder="15000" step="0.01" min="0" required>
    </div>
    <div class="adm-field">
        <label>Gorivo</label>
        <select name="fuel_type">
            <option value="">— odaberi —</option>
            <option value="Benzin">Benzin</option>
            <option value="Diesel">Diesel</option>
            <option value="Hibrid">Hibrid</option>
            <option value="Električni">Električni</option>
            <option value="Plin">Plin</option>
        </select>
    </div>
    <div class="adm-field">
        <label>Mjenjač</label>
        <select name="transmission">
            <option value="">— odaberi —</option>
            <option value="Manualni">Manualni</option>
            <option value="Automatik">Automatik</option>
            <option value="Poluautomatik">Poluautomatik</option>
        </select>
    </div>
    <div class="adm-field">
        <label>Kilometraža</label>
        <input type="number" name="mileage" placeholder="120000" min="0">
    </div>
    <div class="adm-field">
        <label>Motor</label>
        <input type="text" name="engine" placeholder="npr. 2.0 TDI 110kW">
    </div>
    <div class="adm-field">
        <label>Boja</label>
        <input type="text" name="color" placeholder="npr. Crna">
    </div>
    <div class="adm-field">
        <label>Broj vrata</label>
        <select name="doors">
            <option value="">—</option>
            <option value="2">2</option>
            <option value="3">3</option>
            <option value="4">4</option>
            <option value="5">5</option>
        </select>
    </div>
    <div class="adm-field">
        <label>Broj sjedala</label>
        <select name="seats">
            <option value="">—</option>
            <option value="2">2</option>
            <option value="4">4</option>
            <option value="5">5</option>
            <option value="7">7</option>
        </select>
    </div>
    <div class="adm-field">
        <label>Stanje</label>
        <select name="car_condition">
            <option value="Rabljeno" selected>Rabljeno</option>
            <option value="Novo">Novo</option>
            <option value="Demonstracijsko">Demonstracijsko</option>
        </select>
    </div>
    <div class="adm-field adm-form-full">
        <label>Opis</label>
        <textarea name="description" placeholder="Detaljan opis automobila..." style="min-height:120px;"></textarea>
    </div>
    <div class="adm-field adm-form-full">
        <label>Slike (možete odabrati više)</label>
        <input type="file" name="car_images[]" multiple accept="image/*">
    </div>
    <div class="adm-form-full">
        <button type="submit" class="adm-submit">+ Dodaj automobil</button>
    </div>
</form>

<?php
/* ── EDIT FORM ── */
elseif (isset($_GET['edit'])):
    $eid = (int)$_GET['edit'];
    $r   = mysqli_query($MySQL, "SELECT * FROM cars WHERE id=$eid LIMIT 1");
    $car = mysqli_fetch_assoc($r);
?>
<a href="index.php?menu=<?= $menu ?>&action=<?= $action ?>" class="adm-back">Natrag na listu</a>
<h2>Uredi automobil</h2>

<form method="POST" enctype="multipart/form-data" class="adm-form">
    <input type="hidden" name="_action_" value="edit_car">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="edit" value="<?= $car['id'] ?>">

    <div class="adm-field">
        <label>Brand *</label>
        <input type="text" name="brand" value="<?= htmlspecialchars($car['brand']) ?>" required>
    </div>
    <div class="adm-field">
        <label>Model *</label>
        <input type="text" name="model" value="<?= htmlspecialchars($car['model']) ?>" required>
    </div>
    <div class="adm-field">
        <label>Godina *</label>
        <input type="number" name="year" value="<?= (int)$car['year'] ?>" required>
    </div>
    <div class="adm-field">
        <label>Cijena (EUR) *</label>
        <input type="number" name="price" value="<?= (float)$car['price'] ?>" step="0.01" required>
    </div>
    <div class="adm-field">
        <label>Gorivo</label>
        <select name="fuel_type">
            <option value="">— odaberi —</option>
            <?php foreach (['Benzin','Diesel','Hibrid','Električni','Plin'] as $f): ?>
            <option value="<?= $f ?>"<?= ($car['fuel_type'] === $f) ? ' selected' : '' ?>><?= $f ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="adm-field">
        <label>Mjenjač</label>
        <select name="transmission">
            <option value="">— odaberi —</option>
            <?php foreach (['Manualni','Automatik','Poluautomatik'] as $t): ?>
            <option value="<?= $t ?>"<?= ($car['transmission'] === $t) ? ' selected' : '' ?>><?= $t ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="adm-field">
        <label>Kilometraža</label>
        <input type="number" name="mileage" value="<?= $car['mileage'] ?? '' ?>" min="0">
    </div>
    <div class="adm-field">
        <label>Motor</label>
        <input type="text" name="engine" value="<?= htmlspecialchars($car['engine'] ?? '') ?>">
    </div>
    <div class="adm-field">
        <label>Boja</label>
        <input type="text" name="color" value="<?= htmlspecialchars($car['color'] ?? '') ?>">
    </div>
    <div class="adm-field">
        <label>Broj vrata</label>
        <select name="doors">
            <option value="">—</option>
            <?php foreach ([2,3,4,5] as $d): ?>
            <option value="<?= $d ?>"<?= ((int)($car['doors'] ?? 0) === $d) ? ' selected' : '' ?>><?= $d ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="adm-field">
        <label>Broj sjedala</label>
        <select name="seats">
            <option value="">—</option>
            <?php foreach ([2,4,5,7] as $s): ?>
            <option value="<?= $s ?>"<?= ((int)($car['seats'] ?? 0) === $s) ? ' selected' : '' ?>><?= $s ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="adm-field">
        <label>Stanje</label>
        <select name="car_condition">
            <?php foreach (['Rabljeno','Novo','Demonstracijsko'] as $cond): ?>
            <option value="<?= $cond ?>"<?= (($car['car_condition'] ?? 'Rabljeno') === $cond) ? ' selected' : '' ?>><?= $cond ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="adm-field adm-form-full">
        <label>Opis</label>
        <textarea name="description" style="min-height:120px;"><?= htmlspecialchars($car['description'] ?? '') ?></textarea>
    </div>

    <?php
    /* Existing images */
    $existingImgs = mysqli_query($MySQL, "SELECT * FROM car_images WHERE car_id=$eid ORDER BY id ASC");
    if ($existingImgs && mysqli_num_rows($existingImgs) > 0):
    ?>
    <div class="adm-form-full">
        <span style="font-size:0.7rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:0.1em;display:block;margin-bottom:10px;">Trenutne slike</span>
        <div style="display:flex;flex-wrap:wrap;gap:10px;">
        <?php while ($img = mysqli_fetch_assoc($existingImgs)): ?>
        <div style="position:relative;display:inline-block;">
            <img src="Gallery/cars/<?= htmlspecialchars($img['filename']) ?>" class="adm-car-thumb" alt="" style="display:block;">
            <a href="index.php?menu=<?= $menu ?>&action=<?= $action ?>&edit=<?= $eid ?>&delete_img=<?= $img['id'] ?>&car_id=<?= $eid ?>"
               onclick="return confirm('Obrisati ovu sliku?')"
               style="position:absolute;top:-6px;right:-6px;width:20px;height:20px;background:#c0392b;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;text-decoration:none;line-height:1;border:2px solid #0d1117;">✕</a>
        </div>
        <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="adm-field adm-form-full">
        <label>Dodaj nove slike</label>
        <input type="file" name="car_images[]" multiple accept="image/*">
    </div>
    <div class="adm-form-full">
        <button type="submit" class="adm-submit">Spremi izmjene</button>
    </div>
</form>

<?php
/* ── LIST ── */
else:
    $cars = mysqli_query($MySQL, "SELECT * FROM cars ORDER BY id DESC");
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <h2 style="margin:0 !important;">Automobili</h2>
    <a href="index.php?menu=<?= $menu ?>&action=<?= $action ?>&add=true" class="adm-add-btn">+ Dodaj automobil</a>
</div>

<div class="adm-table-wrap">
<table>
    <thead>
        <tr>
            <th></th>
            <th>Brand / Model</th>
            <th>God.</th>
            <th>Gorivo</th>
            <th>km</th>
            <th>Cijena</th>
            <th>Stanje</th>
            <th>Prikazi</th>
            <th>Akcije</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if (!$cars || mysqli_num_rows($cars) === 0):
    ?>
    <tr><td colspan="9" style="text-align:center;padding:32px;color:var(--text-muted);">Nema automobila.</td></tr>
    <?php
    else:
        while ($row = mysqli_fetch_assoc($cars)):
            $pic   = $row['picture'] ?: '';
            $price = number_format((float)$row['price'], 0, ',', '.');
            $km    = $row['mileage'] ? number_format((int)$row['mileage'], 0, ',', '.') : '—';
    ?>
    <tr>
        <td>
            <?php if ($pic): ?>
            <img src="Gallery/cars/<?= htmlspecialchars($pic) ?>" class="adm-car-thumb" alt="">
            <?php else: ?>
            <div style="width:72px;height:48px;background:rgba(255,255,255,0.04);border-radius:6px;border:1px solid var(--border);"></div>
            <?php endif; ?>
        </td>
        <td><strong><?= htmlspecialchars($row['brand'] . ' ' . $row['model']) ?></strong></td>
        <td><?= (int)$row['year'] ?></td>
        <td><?= htmlspecialchars($row['fuel_type'] ?? '—') ?></td>
        <td><?= $km ?></td>
        <td style="color:var(--blue-accent);font-family:var(--font-d);font-size:1.1rem;"><?= $price ?> €</td>
        <td><span class="badge badge-blue"><?= htmlspecialchars($row['car_condition'] ?? 'Rabljeno') ?></span></td>
        <td style="color:var(--text-muted);font-size:0.88rem;">👁 <?= number_format((int)($row['views'] ?? 0), 0, ',', '.') ?></td>
        <td style="white-space:nowrap;">
            <a href="index.php?menu=<?= $menu ?>&action=<?= $action ?>&edit=<?= $row['id'] ?>" class="adm-action adm-action-edit">Uredi</a>
            <a href="index.php?menu=<?= $menu ?>&action=<?= $action ?>&delete=<?= $row['id'] ?>"
               class="adm-action adm-action-delete"
               onclick="return confirm('Obrisati ovaj automobil?')">Obriši</a>
        </td>
    </tr>
    <?php
        endwhile;
    endif;
    ?>
    </tbody>
</table>
</div>

<?php endif; ?>

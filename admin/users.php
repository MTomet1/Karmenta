<?php
$menu   = $_GET['menu']   ?? 7;
$action = $_GET['action'] ?? 1;

/* ── UPDATE USER ── */
if (isset($_POST['edit']) && $_POST['_action_'] === 'TRUE') {
    csrf_check();

    $uid       = (int)$_POST['edit'];
    $firstname = $_POST['firstname'];
    $lastname  = $_POST['lastname'];
    $email     = $_POST['email'];
    $username  = $_POST['username'];
    $country   = $_POST['country'];
    $archive   = ($_POST['archive'] === 'Y') ? 'Y' : 'N';

    $stmt = $MySQL->prepare("UPDATE users SET firstname=?, lastname=?, email=?, username=?, country=?, archive=? WHERE id=? LIMIT 1");
    $stmt->bind_param("ssssssi", $firstname, $lastname, $email, $username, $country, $archive, $uid);
    $stmt->execute();
    $stmt->close();

    $_SESSION['message'] = '<p style="color:white;text-align:center;">Korisnik izmijenjen!</p>';
    echo '<script>window.location.replace("index.php?menu=7&action=1");</script>';
    exit;
}

/* ── DELETE USER ── */
if (isset($_GET['delete']) && $_GET['delete'] !== '') {
    $uid = (int)$_GET['delete'];
    mysqli_query($MySQL, "DELETE FROM users WHERE id=$uid LIMIT 1");
    $_SESSION['message'] = '<p style="color:white;text-align:center;">Korisnik obrisan!</p>';
    echo '<script>window.location.replace("index.php?menu=7&action=1");</script>';
    exit;
}

/* ── VIEW USER ── */
if (isset($_GET['id']) && $_GET['id'] !== ''):
    $uid  = (int)$_GET['id'];
    $r    = mysqli_query($MySQL, "SELECT u.*, c.country_name FROM users u LEFT JOIN countries c ON c.country_code=u.country WHERE u.id=$uid LIMIT 1");
    $user = mysqli_fetch_assoc($r);
?>
<a href="index.php?menu=<?= $menu ?>&action=<?= $action ?>" class="adm-back">Natrag na listu</a>
<h2>Profil korisnika</h2>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;margin-top:8px;">
    <?php
    $fields = [
        'Ime' => $user['firstname'],
        'Prezime' => $user['lastname'],
        'Korisničko ime' => $user['username'],
        'Email' => $user['email'],
        'Država' => $user['country_name'] ?? '—',
        'Datum registracije' => $user['date'] ?? '—',
        'Status' => ($user['archive'] === 'Y') ? 'Arhiviran' : 'Aktivan',
        'Uloga' => 'Uloga ' . ($user['role'] ?? 3),
    ];
    foreach ($fields as $label => $val):
    ?>
    <div class="adm-stat" style="padding:14px 16px;">
        <div class="adm-stat-label"><?= $label ?></div>
        <div style="color:var(--text);font-size:0.9rem;margin-top:4px;font-weight:500;"><?= htmlspecialchars($val) ?></div>
    </div>
    <?php endforeach; ?>
</div>

<?php
/* ── EDIT USER ── */
elseif (isset($_GET['edit']) && $_GET['edit'] !== ''):
    if (!isset($_SESSION['user']['valid']) || $_SESSION['user']['valid'] !== 'true') {
        echo '<p style="color:var(--red);text-align:center;">Nemate ovlasti za ovu akciju.</p>';
    } else {
        $uid  = (int)$_GET['edit'];
        $r    = mysqli_query($MySQL, "SELECT * FROM users WHERE id=$uid LIMIT 1");
        $user = mysqli_fetch_assoc($r);
?>
<a href="index.php?menu=<?= $menu ?>&action=<?= $action ?>" class="adm-back">Natrag na listu</a>
<h2>Uredi korisnika</h2>

<form method="POST" class="adm-form">
    <input type="hidden" name="_action_" value="TRUE">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
    <input type="hidden" name="edit" value="<?= $uid ?>">

    <div class="adm-field">
        <label>Ime *</label>
        <input type="text" name="firstname" value="<?= htmlspecialchars($user['firstname']) ?>" required>
    </div>
    <div class="adm-field">
        <label>Prezime *</label>
        <input type="text" name="lastname" value="<?= htmlspecialchars($user['lastname']) ?>" required>
    </div>
    <div class="adm-field">
        <label>Email *</label>
        <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
    </div>
    <div class="adm-field">
        <label>Korisničko ime *</label>
        <input type="text" name="username" value="<?= htmlspecialchars($user['username']) ?>" pattern=".{5,10}" required>
    </div>
    <div class="adm-field">
        <label>Država</label>
        <select name="country">
            <option value="">— odaberi —</option>
            <?php
            $cQ = mysqli_query($MySQL, "SELECT * FROM countries ORDER BY country_name");
            while ($cRow = mysqli_fetch_assoc($cQ)):
            ?>
            <option value="<?= htmlspecialchars($cRow['country_code']) ?>"
                <?= ($user['country'] === $cRow['country_code']) ? 'selected' : '' ?>>
                <?= htmlspecialchars($cRow['country_name']) ?>
            </option>
            <?php endwhile; ?>
        </select>
    </div>
    <div class="adm-field adm-form-full">
        <label>Arhiviran</label>
        <div class="adm-radio-group">
            <input type="radio" name="archive" id="arch_y" value="Y" <?= ($user['archive'] === 'Y') ? 'checked' : '' ?>>
            <label for="arch_y">Da (neaktivan)</label>
            <input type="radio" name="archive" id="arch_n" value="N" <?= ($user['archive'] !== 'Y') ? 'checked' : '' ?>>
            <label for="arch_n">Ne (aktivan)</label>
        </div>
    </div>
    <div class="adm-form-full">
        <button type="submit" class="adm-submit">Spremi izmjene</button>
    </div>
</form>

<?php
    }

/* ── USER LIST ── */
else:
    $users = mysqli_query($MySQL, "SELECT u.*, c.country_name FROM users u LEFT JOIN countries c ON c.country_code=u.country ORDER BY u.id DESC");
?>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
    <h2 style="margin:0 !important;">Korisnici</h2>
</div>

<div class="adm-table-wrap">
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Ime i prezime</th>
            <th>Korisničko ime</th>
            <th>Email</th>
            <th>Država</th>
            <th>Status</th>
            <th>Akcije</th>
        </tr>
    </thead>
    <tbody>
    <?php
    if (!$users || mysqli_num_rows($users) === 0):
    ?>
    <tr><td colspan="7" style="text-align:center;padding:32px;color:var(--text-muted);">Nema korisnika.</td></tr>
    <?php
    else:
        while ($row = mysqli_fetch_assoc($users)):
            $isActive  = ($row['archive'] !== 'Y');
            $isEditor  = isset($_SESSION['user']['valid']) && $_SESSION['user']['valid'] === 'true';
    ?>
    <tr>
        <td style="color:var(--text-muted);font-size:0.78rem;">#<?= $row['id'] ?></td>
        <td><strong><?= htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) ?></strong></td>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['country_name'] ?? '—') ?></td>
        <td>
            <span class="badge <?= $isActive ? 'badge-green' : '' ?>" style="<?= !$isActive ? 'background:rgba(192,57,43,0.15);border:1px solid rgba(192,57,43,0.35);color:#e74c3c;' : '' ?>">
                <?= $isActive ? 'Aktivan' : 'Arhiviran' ?>
            </span>
        </td>
        <td style="white-space:nowrap;">
            <a href="index.php?menu=<?= $menu ?>&action=<?= $action ?>&id=<?= $row['id'] ?>" class="adm-action adm-action-view">Profil</a>
            <?php if ($isEditor): ?>
            <a href="index.php?menu=<?= $menu ?>&action=<?= $action ?>&edit=<?= $row['id'] ?>" class="adm-action adm-action-edit">Uredi</a>
            <a href="index.php?menu=<?= $menu ?>&action=<?= $action ?>&delete=<?= $row['id'] ?>"
               class="adm-action adm-action-delete"
               onclick="return confirm('Obrisati korisnika <?= htmlspecialchars($row['username'], ENT_QUOTES) ?>?')">Obriši</a>
            <?php endif; ?>
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

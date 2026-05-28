<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';
require __DIR__ . '/security/csrf.php';
?>

<div class="auth-shell">
<div class="auth-card">

    <div class="auth-eyebrow">Karmenta Admin</div>
    <h1 class="auth-title">Registracija</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST'):
?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
            <div>
                <label>Ime *</label>
                <input type="text" name="firstname" placeholder="Ime" required>
            </div>
            <div>
                <label>Prezime *</label>
                <input type="text" name="lastname" placeholder="Prezime" required>
            </div>
        </div>

        <label>Email *</label>
        <input type="email" name="email" placeholder="email@primjer.hr" required>

        <label>Korisničko ime * <small style="opacity:0.5;font-size:0.65rem;">(5–10 znakova)</small></label>
        <input type="text" name="username" pattern=".{5,10}" placeholder="korisnickoime" required>

        <label>Lozinka * <small style="opacity:0.5;font-size:0.65rem;">(min. 4 znaka)</small></label>
        <input type="password" name="password" pattern=".{4,}" placeholder="••••••••" required>

        <label>Država</label>
        <select name="country">
            <option value="">Odaberi državu</option>
            <?php
            if (isset($MySQL)) {
                $query = mysqli_query($MySQL, "SELECT * FROM countries ORDER BY country_name");
                while ($row = mysqli_fetch_assoc($query)) {
                    echo '<option value="' . htmlspecialchars($row['country_code']) . '">' . htmlspecialchars($row['country_name']) . '</option>';
                }
            }
            ?>
        </select>

        <input type="submit" value="Registriraj se →">
    </form>

    <p style="margin-top:20px;text-align:center;font-size:0.82rem;color:var(--text-muted);">
        Već imaš račun? <a href="index.php?menu=6" style="color:var(--blue-accent);">Prijavi se</a>
    </p>

<?php
else:
    csrf_check();

    $stmt = $MySQL->prepare("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1");
    $stmt->bind_param("ss", $_POST['email'], $_POST['username']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0):
?>
    <div style="background:rgba(192,57,43,0.12);border:1px solid rgba(192,57,43,0.35);color:#e74c3c;border-radius:8px;padding:14px;text-align:center;font-size:0.88rem;">
        Korisnik s tim emailom ili korisničkim imenom već postoji.
    </div>
    <p style="text-align:center;margin-top:16px;font-size:0.82rem;color:var(--text-muted);">
        <a href="index.php?menu=5" style="color:var(--blue-accent);">← Pokušaj ponovo</a>
    </p>
<?php
    else:
        $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT, ['cost' => 12]);
        $stmt2 = $MySQL->prepare("INSERT INTO users (firstname, lastname, email, username, password, country) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt2->bind_param("ssssss", $_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['username'], $pass_hash, $_POST['country']);
        $stmt2->execute();
?>
    <div style="background:rgba(39,174,96,0.12);border:1px solid rgba(39,174,96,0.35);color:#5dde8f;border-radius:8px;padding:14px;text-align:center;font-size:0.88rem;">
        Registracija uspješna! Možeš se prijaviti.
    </div>
    <p style="text-align:center;margin-top:16px;font-size:0.82rem;color:var(--text-muted);">
        <a href="index.php?menu=6" style="color:var(--blue-accent);">Prijavi se →</a>
    </p>
<?php
    endif;
endif;
?>

</div>
</div>

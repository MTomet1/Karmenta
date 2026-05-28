<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';
require __DIR__ . '/security/csrf.php';

if (isset($_SESSION['user']['valid']) && $_SESSION['user']['valid'] === 'true') {
    echo "<script>window.location.replace('index.php?menu=1');</script>";
    exit;
}

$token      = $_GET['token'] ?? '';
$tokenValid = false;
$uid        = null;

if ($token !== '') {
    $now  = date('Y-m-d H:i:s');
    $stmt = $MySQL->prepare("SELECT user_id FROM password_resets WHERE token = ? AND expires_at > ? LIMIT 1");
    $stmt->bind_param("ss", $token, $now);
    $stmt->execute();
    $stmt->bind_result($uid);
    $stmt->fetch();
    $stmt->close();

    if ($uid) {
        $tokenValid = true;
    }
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tokenValid) {
    csrf_check();

    $new     = $_POST['new_password']     ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($new) < 4) {
        $error = 'Lozinka mora imati najmanje 4 znaka.';
    } elseif ($new !== $confirm) {
        $error = 'Lozinke se ne podudaraju.';
    } else {
        $hash = password_hash($new, PASSWORD_DEFAULT, ['cost' => 12]);

        $upd = $MySQL->prepare("UPDATE users SET password = ? WHERE id = ?");
        $upd->bind_param("si", $hash, $uid);
        $upd->execute();
        $upd->close();

        $del = $MySQL->prepare("DELETE FROM password_resets WHERE token = ?");
        $del->bind_param("s", $token);
        $del->execute();
        $del->close();

        $_SESSION['message'] = '<p style="color:white;text-align:center;">Lozinka je uspješno promijenjena. Možeš se prijaviti.</p>';
        echo "<script>window.location.replace('index.php?menu=6');</script>";
        exit;
    }
}
?>

<div class="auth-shell"><div class="auth-card">
    <div class="auth-eyebrow">Karmenta</div>
    <h1 class="auth-title">Nova lozinka</h1>

<?php if (!$tokenValid): ?>
    <div style="background:rgba(192,57,43,0.12);border:1px solid rgba(192,57,43,0.35);color:#e74c3c;border-radius:8px;padding:14px;text-align:center;font-size:0.88rem;">
        Link je nevažeći ili je istekao.
    </div>
    <p style="margin-top:20px;text-align:center;font-size:0.82rem;color:var(--text-muted);">
        <a href="index.php?menu=10" style="color:var(--blue-accent);">← Zatraži novi link</a>
    </p>

<?php elseif ($error): ?>
    <div style="background:rgba(192,57,43,0.12);border:1px solid rgba(192,57,43,0.35);color:#e74c3c;border-radius:8px;padding:14px;text-align:center;font-size:0.88rem;">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <p style="text-align:center;margin-top:16px;font-size:0.82rem;color:var(--text-muted);">
        <a href="index.php?menu=11&token=<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>" style="color:var(--blue-accent);">← Pokušaj ponovo</a>
    </p>

<?php else: ?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <label>Nova lozinka *</label>
        <input type="password" name="new_password" placeholder="••••••••" minlength="4" required autofocus>
        <label>Potvrdi lozinku *</label>
        <input type="password" name="confirm_password" placeholder="••••••••" minlength="4" required>
        <input type="submit" value="Postavi lozinku →">
    </form>
<?php endif; ?>

</div></div>

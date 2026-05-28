<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';
require __DIR__ . '/security/csrf.php';

if (!isset($_SESSION['user']['valid']) || $_SESSION['user']['valid'] !== 'true') {
    header('Location: index.php?menu=6');
    exit;
}
?>

<div class="auth-shell">
<div class="auth-card">

    <div class="auth-eyebrow">Karmenta</div>
    <h1 class="auth-title">Promjena lozinke</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST'):
?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <label>Trenutna lozinka *</label>
        <input type="password" name="current_password" placeholder="••••••••" required autofocus>

        <label>Nova lozinka *</label>
        <input type="password" name="new_password" placeholder="••••••••" minlength="4" required>

        <label>Potvrdi novu lozinku *</label>
        <input type="password" name="confirm_password" placeholder="••••••••" minlength="4" required>

        <input type="submit" value="Spremi promjene →">
    </form>

    <p style="margin-top:20px;text-align:center;font-size:0.82rem;color:var(--text-muted);">
        <a href="index.php?menu=1" style="color:var(--blue-accent);">← Natrag</a>
    </p>

<?php
else:
    csrf_check();

    if (!isset($_SESSION['chpw_fail'])) {
        $_SESSION['chpw_fail'] = ['count' => 0, 'time' => time()];
    }

    if ($_SESSION['chpw_fail']['count'] >= 5 && time() - $_SESSION['chpw_fail']['time'] < 600):
?>
    <div style="background:rgba(192,57,43,0.12);border:1px solid rgba(192,57,43,0.35);color:#e74c3c;border-radius:8px;padding:14px;text-align:center;font-size:0.88rem;">
        Previše neuspješnih pokušaja. Pokušaj za 10 minuta.
    </div>
<?php
    else:
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password']     ?? '';
        $confirm = $_POST['confirm_password'] ?? '';
        $error   = '';

        if (strlen($new) < 4) {
            $error = 'Nova lozinka mora imati najmanje 4 znaka.';
        } elseif ($new !== $confirm) {
            $error = 'Nova lozinka i potvrda se ne podudaraju.';
        }

        if (!$error) {
            $uid  = (int)$_SESSION['user']['id'];
            $stmt = $MySQL->prepare("SELECT password FROM users WHERE id = ? LIMIT 1");
            $stmt->bind_param("i", $uid);
            $stmt->execute();
            $stmt->bind_result($hash);
            $stmt->fetch();
            $stmt->close();

            if (!$hash || !password_verify($current, $hash)) {
                $error = 'Trenutna lozinka nije ispravna.';
                $_SESSION['chpw_fail']['count']++;
                $_SESSION['chpw_fail']['time'] = time();
            }
        }

        if ($error):
?>
    <div style="background:rgba(192,57,43,0.12);border:1px solid rgba(192,57,43,0.35);color:#e74c3c;border-radius:8px;padding:14px;text-align:center;font-size:0.88rem;">
        <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
    </div>
    <p style="text-align:center;margin-top:16px;font-size:0.82rem;color:var(--text-muted);">
        <a href="index.php?menu=9" style="color:var(--blue-accent);">← Pokušaj ponovo</a>
    </p>
<?php
        else:
            $newHash = password_hash($new, PASSWORD_DEFAULT, ['cost' => 12]);
            $uid     = (int)$_SESSION['user']['id'];
            $upd     = $MySQL->prepare("UPDATE users SET password = ? WHERE id = ?");
            $upd->bind_param("si", $newHash, $uid);
            $upd->execute();
            $upd->close();

            unset($_SESSION['chpw_fail']);
            $_SESSION['message'] = '<p style="color:white;text-align:center;">Lozinka je uspješno promijenjena.</p>';
            echo "<script>window.location.replace('index.php?menu=1');</script>";
            exit;
        endif;
    endif;
endif;
?>

</div>
</div>

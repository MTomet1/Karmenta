<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';
require __DIR__ . '/security/csrf.php';
?>

<div class="auth-shell">
<div class="auth-card">

    <div class="auth-eyebrow">Karmenta Admin</div>
    <h1 class="auth-title">Prijava</h1>

<?php
if ($_SERVER['REQUEST_METHOD'] !== 'POST'):
?>
    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

        <label>Korisničko ime *</label>
        <input type="text" name="username" pattern=".{5,10}" placeholder="korisnickoime" required autofocus>

        <label>Lozinka *</label>
        <input type="password" name="password" pattern=".{4,}" placeholder="••••••••" required>

        <input type="submit" value="Prijavi se →">
    </form>

    <p style="margin-top:20px;text-align:center;font-size:0.82rem;color:var(--text-muted);">
        Nemaš račun? <a href="index.php?menu=5" style="color:var(--blue-accent);">Registriraj se</a>
    </p>
    <p style="margin-top:8px;text-align:center;font-size:0.82rem;color:var(--text-muted);">
        <a href="index.php?menu=10" style="color:var(--blue-accent);">Zaboravili ste lozinku?</a>
    </p>

<?php
else:
    csrf_check();

    if (!isset($_SESSION['login_fail'])) {
        $_SESSION['login_fail'] = ['count' => 0, 'time' => time()];
    }
    if ($_SESSION['login_fail']['count'] >= 5 && time() - $_SESSION['login_fail']['time'] < 600):
?>
    <div style="background:rgba(192,57,43,0.12);border:1px solid rgba(192,57,43,0.35);color:#e74c3c;border-radius:8px;padding:14px;text-align:center;font-size:0.88rem;">
        Previše neuspješnih pokušaja. Pokušaj za 10 minuta.
    </div>
<?php
    else:
        $stmt = $MySQL->prepare("SELECT id, password, role, firstname, lastname FROM users WHERE username = ? AND archive = 'N' LIMIT 1");
        $stmt->bind_param("s", $_POST['username']);
        $stmt->execute();
        $stmt->bind_result($uid, $hash, $role, $firstname, $lastname);
        $stmt->fetch();

        if ($hash && password_verify($_POST['password'], $hash)):
            unset($_SESSION['login_fail']);
            session_regenerate_id(true);
            $_SESSION['user']['valid']     = 'true';
            $_SESSION['user']['id']        = $uid;
            $_SESSION['user']['role']      = $role;
            $_SESSION['user']['firstname'] = $firstname;
            $_SESSION['user']['lastname']  = $lastname;
            $_SESSION['message'] = '<p style="color:white;text-align:center;">Dobrodošli, ' . htmlspecialchars($firstname . ' ' . $lastname) . '!</p>';
            echo "<script>window.location.replace('index.php?menu=1');</script>";
            exit;
        else:
            $_SESSION['login_fail']['count']++;
            $_SESSION['login_fail']['time'] = time();
            unset($_SESSION['user']);
?>
    <div style="background:rgba(192,57,43,0.12);border:1px solid rgba(192,57,43,0.35);color:#e74c3c;border-radius:8px;padding:14px;text-align:center;font-size:0.88rem;">
        Pogrešno korisničko ime ili lozinka.
    </div>
    <p style="text-align:center;margin-top:16px;font-size:0.82rem;color:var(--text-muted);">
        <a href="index.php?menu=6" style="color:var(--blue-accent);">← Pokušaj ponovo</a>
    </p>
<?php
        endif;
    endif;
endif;
?>

</div>
</div>

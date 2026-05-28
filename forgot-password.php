<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';
require __DIR__ . '/security/csrf.php';

if (isset($_SESSION['user']['valid']) && $_SESSION['user']['valid'] === 'true') {
    header('Location: index.php?menu=1');
    exit;
}

if (file_exists('/home/karment1/PHPMailer/src/PHPMailer.php')) {
    require_once '/home/karment1/PHPMailer/src/Exception.php';
    require_once '/home/karment1/PHPMailer/src/PHPMailer.php';
    require_once '/home/karment1/PHPMailer/src/SMTP.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST'):
    csrf_check();

    if (!isset($_SESSION['fpw_fail'])) {
        $_SESSION['fpw_fail'] = ['count' => 0, 'time' => time()];
    }

    if ($_SESSION['fpw_fail']['count'] >= 3 && time() - $_SESSION['fpw_fail']['time'] < 600):
?>
<div class="auth-shell"><div class="auth-card">
    <div class="auth-eyebrow">Karmenta</div>
    <h1 class="auth-title">Reset lozinke</h1>
    <div style="background:rgba(192,57,43,0.12);border:1px solid rgba(192,57,43,0.35);color:#e74c3c;border-radius:8px;padding:14px;text-align:center;font-size:0.88rem;">
        Previše pokušaja. Pokušaj za 10 minuta.
    </div>
</div></div>
<?php
    else:
        $email = trim($_POST['email'] ?? '');

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $MySQL->prepare("SELECT id FROM users WHERE email = ? AND archive = 'N' LIMIT 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->bind_result($uid);
            $stmt->fetch();
            $stmt->close();

            if ($uid) {
                $del = $MySQL->prepare("DELETE FROM password_resets WHERE user_id = ?");
                $del->bind_param("i", $uid);
                $del->execute();
                $del->close();

                $token   = bin2hex(random_bytes(32));
                $expires = date('Y-m-d H:i:s', time() + 1800);

                $ins = $MySQL->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
                $ins->bind_param("iss", $uid, $token, $expires);
                $ins->execute();
                $ins->close();

                $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
                $resetUrl = $protocol . '://' . $_SERVER['HTTP_HOST'] . '/index.php?menu=11&token=' . $token;

                try {
                    $config = require '/home/karment1/mail_config.php';

                    $mail = new PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host        = $config['host'];
                    $mail->SMTPAuth    = true;
                    $mail->Username    = $config['username'];
                    $mail->Password    = $config['password'];
                    $mail->SMTPSecure  = $config['encryption'];
                    $mail->Port        = $config['port'];
                    $mail->CharSet     = 'UTF-8';
                    $mail->SMTPOptions = [
                        'ssl' => [
                            'verify_peer'       => false,
                            'verify_peer_name'  => false,
                            'allow_self_signed' => true,
                        ]
                    ];

                    $mail->setFrom($config['from_address'], 'Karmenta');
                    $mail->addAddress($email);
                    $mail->isHTML(true);
                    $mail->Subject = 'Reset lozinke — Karmenta';
                    $mail->Body    = "
                        <p>Primili smo zahtjev za reset lozinke za vaš račun.</p>
                        <p>Kliknite na link ispod kako biste postavili novu lozinku. Link je važeći <strong>30 minuta</strong>.</p>
                        <p><a href='{$resetUrl}' style='background:#063EA5;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;display:inline-block;'>Resetiraj lozinku</a></p>
                        <p style='color:#999;font-size:0.85rem;'>Ako niste zatražili reset, zanemarite ovaj email.</p>
                        <p>Lijep pozdrav,<br><strong>Karmenta Team</strong></p>
                    ";
                    $mail->send();
                } catch (\Exception $e) {
                    // silent
                }
            }
        }

        $_SESSION['fpw_fail']['count']++;
        $_SESSION['fpw_fail']['time'] = time();
        $sent = true;
    endif;
endif;

if (!$sent):
?>
<div class="auth-shell"><div class="auth-card">
    <div class="auth-eyebrow">Karmenta</div>
    <h1 class="auth-title">Reset lozinke</h1>

    <form method="POST">
        <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
        <label>E-mail adresa *</label>
        <input type="email" name="email" placeholder="vasa@email.hr" required autofocus>
        <input type="submit" value="Pošalji link →">
    </form>

    <p style="margin-top:20px;text-align:center;font-size:0.82rem;color:var(--text-muted);">
        <a href="index.php?menu=6" style="color:var(--blue-accent);">← Natrag na prijavu</a>
    </p>
</div></div>
<?php
else:
?>
<div class="auth-shell"><div class="auth-card">
    <div class="auth-eyebrow">Karmenta</div>
    <h1 class="auth-title">Reset lozinke</h1>
    <div style="background:rgba(39,174,96,0.12);border:1px solid rgba(39,174,96,0.35);color:#27ae60;border-radius:8px;padding:14px;text-align:center;font-size:0.88rem;">
        Ako e-mail adresa postoji u sustavu, poslan je link za reset lozinke.
    </div>
    <p style="margin-top:20px;text-align:center;font-size:0.82rem;color:var(--text-muted);">
        <a href="index.php?menu=6" style="color:var(--blue-accent);">← Natrag na prijavu</a>
    </p>
</div></div>
<?php
endif;
?>

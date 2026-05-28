<?php
require __DIR__ . '/security/headers.php';
require __DIR__ . '/security/session_boot.php';
require __DIR__ . '/security/csrf.php';
include __DIR__ . '/dbconn.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header('Location: index.php?menu=3');
    exit;
}

csrf_check();

$firstname = trim($_POST['firstname'] ?? '');
$lastname  = trim($_POST['lastname'] ?? '');
$email     = trim($_POST['email'] ?? '');
$country   = trim($_POST['country'] ?? '');
$subject   = trim($_POST['subject'] ?? '');

if ($firstname === '' || $lastname === '' || $subject === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>alert('Molimo ispravno ispunite sva polja!'); window.history.back();</script>";
    exit;
}

// 1) Spremi poruku u bazu — radi uvijek, i lokalno (ne treba mail server).
$stmt  = $MySQL->prepare("INSERT INTO contact_form (first_name, last_name, email, country, subject) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $firstname, $lastname, $email, $country, $subject);
$saved = $stmt->execute();
$stmt->close();

// 2) Pošalji e-mail SAMO ako su PHPMailer i konfiguracija dostupni (na produkciji).
//    Lokalno se ovaj dio preskače pa nema greške.
$mailerDir  = '/home/karment1/PHPMailer/src/';
$configFile = '/home/karment1/mail_config.php';

if (is_file($mailerDir . 'PHPMailer.php') && is_file($configFile)) {
    require_once $mailerDir . 'Exception.php';
    require_once $mailerDir . 'PHPMailer.php';
    require_once $mailerDir . 'SMTP.php';
    $config = require $configFile;

    try {
        // Poruka vlasniku
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $config['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $config['username'];
        $mail->Password   = $config['password'];
        $mail->SMTPSecure = $config['encryption'];
        $mail->Port       = $config['port'];
        $mail->CharSet    = 'UTF-8';
        $mail->SMTPOptions = [
            'ssl' => ['verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true],
        ];

        $mail->setFrom($config['from_address'], 'Karmenta Web Kontakt');
        $mail->addAddress($config['from_address'], 'Karmenta Info');
        $mail->addReplyTo($email, "$firstname $lastname");
        $mail->isHTML(true);
        $mail->Subject = "Novi upit sa web kontakt forme";
        $mail->Body = "
            <h2>Novi upit</h2>
            <p><strong>Ime:</strong> {$firstname}</p>
            <p><strong>Prezime:</strong> {$lastname}</p>
            <p><strong>E-mail:</strong> {$email}</p>
            <p><strong>Država:</strong> {$country}</p>
            <p><strong>Poruka:</strong><br>" . nl2br(htmlspecialchars($subject)) . "</p>
        ";
        $mail->send();

        // Potvrda korisniku
        $confirm = new PHPMailer(true);
        $confirm->isSMTP();
        $confirm->Host       = $config['host'];
        $confirm->SMTPAuth   = true;
        $confirm->Username   = $config['username'];
        $confirm->Password   = $config['password'];
        $confirm->SMTPSecure = $config['encryption'];
        $confirm->Port       = $config['port'];
        $confirm->CharSet    = 'UTF-8';
        $confirm->SMTPOptions = $mail->SMTPOptions;

        $confirm->setFrom($config['from_address'], 'Karmenta');
        $confirm->addAddress($email);
        $confirm->isHTML(true);
        $confirm->Subject = "Vaša poruka je zaprimljena";
        $confirm->Body = "
            <p>Poštovani <strong>{$firstname}</strong>,</p>
            <p>Hvala što ste nas kontaktirali. Vaša poruka je zaprimljena.</p>
            <p>Javit ćemo Vam se uskoro.</p>
            <p>Lijep pozdrav,<br><strong>Karmenta Team</strong></p>
        ";
        $confirm->send();
    } catch (Exception $e) {
        // E-mail nije prošao, ali poruka je spremljena u bazu — ne rušimo stranicu.
    }
}

if ($saved) {
    echo "<script>alert('Poruka uspješno poslana!'); window.history.back();</script>";
} else {
    echo "<script>alert('Greška pri spremanju poruke. Pokušajte ponovno.'); window.history.back();</script>";
}
exit;

<?php
/**
 * PDO bootstrap + prepared statement usage example.
 */
$DB_DSN  = getenv('BESPLATNA_DSN') ?: 'mysql:host=localhost;dbname=besplatna;charset=utf8mb4';
$DB_USER = getenv('BESPLATNA_DBUSER') ?: 'user';
$DB_PASS = getenv('BESPLATNA_DBPASS') ?: 'pass';

$pdo = new PDO($DB_DSN, $DB_USER, $DB_PASS, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
]);

// Example safe query
$stmt = $pdo->prepare('SELECT id, email FROM users WHERE email = :email LIMIT 1');
$stmt->execute(['email' => 'test@example.com']);
$row = $stmt->fetch();
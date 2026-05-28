<?php
   require __DIR__ . '/security/headers.php';
   require __DIR__ . '/security/session_boot.php';
	# Stop Hacking attempt
    define('__APP__', TRUE);

	# Start session
	session_start();
	
	
	unset($_POST);
	unset($_SESSION['user']);

	$_SESSION['user']['valid'] = 'false';
	$_SESSION['message'] = '<p style="text-align: center; color: white;">See you again soon!</p>';
	
	header("Location: index.php?menu=1");
	exit;
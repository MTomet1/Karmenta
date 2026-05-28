<?php 
   require __DIR__ . '/security/headers.php';
   require __DIR__ . '/security/session_boot.php';
	function pickerDateToMysql($pickerDate){
		$date = DateTime::createFromFormat('Y-m-d H:i:s', $pickerDate);
		return $date->format('d. m. Y H:i:s');
	}  
?>
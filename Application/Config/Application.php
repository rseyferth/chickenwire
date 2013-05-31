<?php

	// Set environment
	$config->environment = ($_SERVER['HTTP_HOST'] == 'admin.wipkip.com') ? 'production' : 'development';

	// Database
	$config->database = array(
		'development' => 'mysql://root:1395.nl@localhost/wipkip_admin;charset=utf8',
		'production' => 'mysql://[user]:[pass]@localhost/wipkip-admin;charset=utf8'
	);

	// Set timezone
	$config->timezone = "Europe/Amsterdam";

	$config->htmlSelfClosingSlash = true;


	// Disable Csrf guard for development
	$config->enableCsrfGuard = array(
		'development' => false,
		'production' => true
	);


	// Debug packages for autoloading
	ChickenWire\autoLoadNamespace('HtmlObject', '/home/ruben/Websites/html-object/src/HtmlObject');

?>
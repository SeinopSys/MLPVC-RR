<?php

	require 'conf.php';

	// Global constants \\
	define('ABSPATH',(!empty($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['SERVER_NAME'].'/');
	define('APPATH',dirname(__FILE__).DIRECTORY_SEPARATOR);
	define('RQMTHD',$_SERVER['REQUEST_METHOD']);
	define('REWRITE_REGEX','~^/(?:([\w\.\-]+|-?\d+)(?:/((?:[\w\-]+|-?\d+)(?:/(?:[\w\-]+|-?\d+))?))?/?)?$~');
	define('GITHUB_URL','https://github.com/ponydevs/MLPVC-RR');
	define('SITE_TITLE', 'Vector Club Requests & Reservations');

	// Imports \\
	require 'includes/MysqliDbWrapper.php';
	$Database = new MysqliDbWrapper('mlpvc-rr');
	require 'includes/Cookie.php';
	require 'includes/Utils.php';
	require 'includes/AuthCheck.php';

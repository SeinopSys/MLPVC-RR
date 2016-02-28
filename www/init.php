<?php

	require 'conf.php';

	// Global constants \\
	define('ABSPATH',(!empty($_SERVER['HTTPS'])?'https':'http').'://'.$_SERVER['SERVER_NAME'].'/');
	define('APPATH',dirname(__FILE__).DIRECTORY_SEPARATOR);
	define('RQMTHD',$_SERVER['REQUEST_METHOD']);
	define('REWRITE_REGEX','~^/(?:([\w\.\-]+|-?\d+)(?:/((?:[\w\-]+|-?\d+)(?:/(?:[\w\-]+|-?\d+))?))?/?)?$~');
	define('GITHUB_URL','https://github.com/ponydevs/MLPVC-RR');
	define('SITE_TITLE', 'MLP Vector Club');

	// Imports \\
	require 'includes/JSON.php';
	require 'includes/PostgresDbWrapper.php';
	$Database = new PostgresDbWrapper('mlpvc-rr');
	try {
		$Database->pdo();
	}
	catch (Exception $e){
		unset($Database);
		die(require APPATH."views/dberr.php");
	}
	$CGDb = new PostgresDbWrapper('mlpvc-colorguide');
	require 'includes/Cookie.php';
	require 'includes/Utils.php';
	require 'includes/AuthCheck.php';

	header('Access-Control-Allow-Origin: '.(!empty($_SERVER['HTTPS'])?'http':'https').'://'.$_SERVER['SERVER_NAME']);

	$is_cf = false;
	if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])){
		require 'includes/CloudFlare.php';
		if (CloudFlare::CheckUserIP()){
			$_SERVER['REMOTE_ADDR'] = $_SERVER['HTTP_CF_CONNECTING_IP'];
			$is_cf = true;
		}
	}
	define('CF_REQUEST', $is_cf);

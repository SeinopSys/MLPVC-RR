<?php

$_dir = rtrim(__DIR__, '\/').DIRECTORY_SEPARATOR;
require $_dir.'init/minimal.php';
require $_dir.'init/monolog.php';

use App\Auth;
use App\CoreUtils;

function dyn_log(string $message){
	if (posix_isatty(STDOUT))
		echo $message."\n";
	else CoreUtils::error_log(__FILE__.": $message");
}

if (empty($argv[1])){
	dyn_log('Session ID is not specified');
	exit(1);
}

$session_id = strtolower($argv[1]);
if (!preg_match('~^[a-f\d-]+$~', $session_id)){
	dyn_log("Session ID is malformed: $session_id");
	exit(2);
}

Auth::$session = \App\Models\Session::find($session_id);
if (empty(Auth::$session)){
	dyn_log("Session not found for ID: $session_id");
	exit(3);
}
Auth::$user = Auth::$session->user;

if (Auth::$session->expired){
	try {
		\App\DeviantArt::refreshAccessToken();
	}
	catch (Throwable $e){
		Auth::$session->delete();
		$code = ($e instanceof \App\Exceptions\CURLRequestException ? 'HTTP ' : '').$e->getCode();
		dyn_log('Session refresh failed for '.Auth::$user->name.' ('.Auth::$user->id.") | {$e->getMessage()} ($code)");

		Auth::$signed_in = false;
		try {
			CoreUtils::socketEvent('session-remove', [
				'userId' => Auth::$user->id,
				'loggedIn' => CoreUtils::getSidebarLoggedIn(),
			]);
		}
		catch (\ElephantIO\Exception\ServerConnectionFailureException $e){
			dyn_log('Notice: Could not send session-remove WS event: '.$e->getMessage());
		}
		exit(4);
	}
}

Auth::$signed_in = true;
Auth::$session->updating = false;
Auth::$session->save();
try {
	CoreUtils::socketEvent('session-refresh', [
		'userId' => Auth::$user->id,
		'loggedIn' => CoreUtils::getSidebarLoggedIn(),
	]);
}
catch (\ElephantIO\Exception\ServerConnectionFailureException $e){
	dyn_log('Notice: Could not send session-refresh WS event: '.$e->getMessage());
}

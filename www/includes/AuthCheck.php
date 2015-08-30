<?php

	// Anti-CSRF
	$CSRF = !isset($_POST['CSRF_TOKEN']) || !Cookie::exists('CSRF_TOKEN') || $_POST['CSRF_TOKEN'] !== Cookie::get('CSRF_TOKEN');
	if (RQMTHD !== 'POST' && $CSRF)
		Cookie::set('CSRF_TOKEN',md5(time()+rand()),COOKIE_SESSION);
	define('CSRF_TOKEN',Cookie::get('CSRF_TOKEN'));

	if (RQMTHD === 'GET' && isset($_GET['CSRF_TOKEN']))
		die(header('Location: '.remove_csrf_query_parameter($_SERVER['REQUEST_URI'])));

	$signedIn = false;

	$Color = 'Color';
	$color = 'color';
	if (Cookie::exists('access')){
		$authKey = Cookie::get('access');
		$currentUser = get_user($authKey,'access');

		if (!empty($currentUser)){
			if ($currentUser['role'] !== 'ban'){
				if (strtotime($currentUser['Session']['expires']) < time())
					da_get_token($currentUser['Session']['refresh'],'refresh_token');

				$signedIn = true;
				$lastVisitTS = date('c');
				if ($Database->where('id', $currentUser['Session']['id'])->update('sessions', array('lastvisit' => $lastVisitTS)))
					$currentUser['Session']['lastvisit'] = $lastVisitTS;

				if ($currentUser['name'] === 'Pirill-Poveniy'){
					$Color = 'Colour';
					$color = 'colour';
				}
			}
			else $Database->where('id', $currentUser['id'])->delete('sessions');
		}

		if (!$signedIn){
			Cookie::delete('access');
			unset($currentUser);
		}
	}

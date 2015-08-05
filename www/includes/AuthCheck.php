<?php

	// Anti-CSRF
	$_RQMTHD = RQMTHD === 'POST' ? $_POST : $_GET;
	$CSRF = !isset($_RQMTHD['CSRF_TOKEN']) || !Cookie::exists('CSRF_TOKEN') || $_RQMTHD['CSRF_TOKEN'] !== Cookie::get('CSRF_TOKEN');
	if (RQMTHD !== 'POST' && $CSRF)
		Cookie::set('CSRF_TOKEN',md5(time()+rand()),COOKIE_SESSION);
	define('CSRF_TOKEN',Cookie::get('CSRF_TOKEN'));

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

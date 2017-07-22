<?php

	require __DIR__.'/../includes/init.php';

	use App\RegExp;
	use App\HTTP;
	use App\Users;
	use App\CoreUtils;

	$permRedirectPattern = new RegExp('^\s*(.*?)\.php(\?.*)?$','i');
	if (preg_match($permRedirectPattern, $_SERVER['REQUEST_URI']))
		HTTP::redirect(preg_replace($permRedirectPattern, '$1$2', $_SERVER['REQUEST_URI']));
	if (!preg_match($REWRITE_REGEX,strtok($_SERVER['REQUEST_URI'],'?'),$matches)){
		Users::authenticate();
		CoreUtils::notFound();
	}

	$do = empty($matches[1]) ? 'index' : $matches[1];
	$data = $matches[2] ?? '';

	require INCPATH.'routes.php';
	/** @var $match array */
	$match = $router->match($_SERVER['REQUEST_URI']);
	if (!isset($match['target']))
		CoreUtils::notFound();
	(\App\RouteHelper::processHandler($match['target']))($match['params']);


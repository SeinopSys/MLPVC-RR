<?php

	// Database Access Info \\
	define('DB_HOST','');
	define('DB_USER','');
	define('DB_PASS','');

	// dA API Codes \\
	define('DA_CLIENT','');
	define('DA_SECRET','');

	// Google Analytics Tracking Code \\
	define('GA_TRACKING_CODE','');

	/**
	 * Get latest commit version & time from Git
	 * -----------------------------------------
	 * Windows (without using PATH):
	 *   $git = '<drive>:\path\to\git.exe';
	 *
	 * Linux/Unix or Windows (using PATH):
	 *   $git = 'git';
	 */
	$git = 'git';

	// GitHub webhooks-related \\
	define('GH_WEBHOOK_DO', '');
	define('GH_WEBHOOK_SECRET', '');

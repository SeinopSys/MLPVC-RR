<?php

	/**
	 * Sends replies to AJAX requests in a universal form
	 * $s respresents the request status, a truthy value
	 *  means the request was successful, a falsey value
	 *  means the request failed
	 * $x can be used to attach additional data to the response
	 *
	 * @param string $m
	 * @param bool|int $s
	 * @param array $x
	 */
	define('ERR_DB_FAIL','There was an error while saving to the database');
	function respond($m = 'Insufficent permissions.', $s = false, $x = array()){
		header('Content-Type: application/json');
		if (is_array($m) && $s === false && empty($x)){
			$m['status'] = true;
			die(json_encode($m));
		}
		die(json_encode(array_merge(array(
			"message" => $m,
			"status" => $s,
		),$x)));
	}

	# Logging (TODO)
	function LogAction($type,$data = null){
		global $Database, $signedIn;
		$central = array('ip' => $_SERVER['REMOTE_ADDR']);

		if (isset($data)){
			foreach ($data as $k => $v)
				if (is_bool($v))
					$data[$k] = $v ? 1 : 0;

			$refid = $Database->insert("log_$type",$data,true);
		}

		$central['reftype'] = $type;
		if (isset($refid) && $refid > 0){
			$central['refid'] = $refid;
		}
		else if (!isset($data)){
			$central['refid'] = 0;
		}
		else return false;
		if ($signedIn)
			$central['initiator'] = $GLOBALS['currentUser']['id'];
		$Database->insert("log_central",$central);
	}

	/**
	 * Converts $timestamp to an "X somthing ago" format
	 * Always uses the greatest unit available
	 */
	$TIME_DATA = array(
		'year' => 31557600,
	    'month' => 2592000,
	    'day' => 86400,
	    'hour' => 3600,
	    'minute' => 60,
	    'second' => 1,
	);
	function time_ago($timestamp){
		global $TIME_DATA;

	    $delta = time() - $timestamp;

	    foreach ($TIME_DATA as $n => $v){
	        if ($delta >= $v){
	            $left = floor($delta / $v);
	            $delta -= ($left * $v);
	            $str = "{$left} ".($left!=1?"{$n}s":$n);
	            break;
	        }
	    }

		if (!isset($str)) return 'just now';

		if ($str == '1 day') $str = 'yesterday';
		else $str .= ' ago';

	    return $str;
	}

	/**
	 * Create an ISO timestamp from the input string
	 *
	 * @param int $time
	 * @param string $format
	 *
	 * @return string
	 */
	define('FORMAT_READABLE',true);
	define('FORMAT_FULL','jS M Y, g:i:s a T');
	function format_timestamp($time, $format = 'c'){
		if ($format === FORMAT_READABLE)
			$ts = time_ago($time);
		else $ts = gmdate($format,$time);
		return $ts;
	}

	/**
	 * Create <time datetime></time> tag
	 *
	 * @param string|int $timestamp
	 *
	 * @return string
	 */
	function timetag($timestamp){
		if (is_string($timestamp))
			$timestamp = strtotime($timestamp);
		if ($timestamp === false) return null;

		$datetime = format_timestamp($timestamp);
		$full = format_timestamp($timestamp,FORMAT_FULL);
		$text = format_timestamp($timestamp,FORMAT_READABLE);
		return "<time datetime='$datetime' title='$full'>$text</time>";
	}

	// Page loading fornction
	function loadPage($settings){
		// Page <title>
		if (isset($settings['title']))
			$title = $settings['title'];

		// SE crawlign disable
		if (in_array('no-robots',$settings))
			$norobots = true;

		# CSS
		$DEFAULT_CSS = array('theme');
		$customCSS = array();
		// Only add defaults when needed
		if (array_search('no-default-css',$settings) === false)
			$customCSS = array_merge($customCSS, $DEFAULT_CSS);

		# JavaScript
		$DEFAULT_JS = array('dyntime','dialog','quotes');
		$customJS = array();
		// Add logged_in.js for logged in users
		global $signedIn;
		if (isset($signedIn) && $signedIn === true) $DEFAULT_JS[] = 'logged_in';
		// Only add defaults when needed
		if (array_search('no-default-js',$settings) === false)
			$customJS = array_merge($customJS, $DEFAULT_JS);

		# Check assests
		assetCheck($settings, $customCSS, 'css');
		assetCheck($settings, $customJS, 'js');

		# Add status code
		if (isset($settings['status-code']))
			statusCodeHeader($settings['status-code']);

		# Import global variables
		foreach ($GLOBALS as $nev => $ertek)
			if (!isset($$nev))
				$$nev = $ertek;

		# Putting it together
		/* Together, we'll always shine! */
		if (empty($settings['view'])) $view = $do;
		else $view = $settings['view'];
		$viewPath = "views/{$view}.php";

		header('Content-Type: text/html; charset=utf-8;');

		// Kell-e fejrész?
		if (array_search('no-header',$settings) === false){
			$pageHeader = array_search('no-page-header',$settings) === false;
			require 'views/header.php';
		}
		// Megjelenésfájl betöltése
		require $viewPath;
		// Kell-e lábrész?
		if (array_search('no-footer',$settings) === false){
			//$customCSS[] = 'footer';
			require 'views/footer.php';
		}

		die();
	}
	function assetCheck($settings, &$customType, $type){
		// Any more files?
		if (isset($settings[$type])){
			$$type = $settings[$type];
			if (!is_array($$type))
				$customType[] = $$type;
			else $customType = array_merge($customType, $$type);
			if (array_search("do-$type",$settings) !== false){
				global $do;
				$customType[] = $do;
			}
		}
		else if (array_search("do-$type",$settings) !== false){
			global $do;
			$customType[] = $do;
		}

		$pathStart = APPATH."$type/";
		if ($type === 'css') foreach ($customType as $i => $item){
			if (!file_exists("$pathStart$item.$type")){
				if(file_exists("$pathStart$item.min.$type"))
					$customType[$i] .= '.min';
				else array_splice($customType,$i,1);
			}
		}
		else if ($type === 'js') foreach ($customType as $i => $item){
			if (strpos($item,'.min') !== false) continue;
			if (file_exists("$pathStart$item.min.$type"))
				$customType[$i] .= '.min';
		}
	}

	// Display a 404 page
	function do404(){
		if (RQMTHD == 'POST') respond("I don't know how to do: {$GLOBALS['do']}");
		loadPage(array(
			'title' => '404',
			'view' => '404',
			'css' => '404',
			'status-code' => 404,
		));
	}

	// Time Constants \\
	define('EIGHTY_YEARS',2524556160);
	define('THREE_YEARS',94608000);
	define('THIRTY_DAYS',2592000);
	define('ONE_HOUR',3600);

	// Random Array Element \\
	function array_random($array){ return $array[array_rand($array, 1)]; }

	// Color padder \\
	function clrpad($c){
		if (strlen($c) === 3) $c = $c[0].$c[0].$c[1].$c[1].$c[2].$c[2];
		return $c;
	}

	// Redirection \\
	define('STAY_ALIVE', false);
	function redirect($url = '/', $die = true){
		header("Location: $url");
		if ($die !== STAY_ALIVE) die();
	}

	/**
	 * Number padder
	 * -------------
	 * Pad a number using $padchar from either side
	 *  to create an $l character long string
	 * If $leftSide is false, padding is done from the right
	 *
	 * @param string|int $str
	 * @param int $l
	 * @param string $padchar
	 * @param bool $leftSide
	 * @return string
	 */
	function pad($str,$l = 2,$padchar = '0', $leftSide = true){
		if (!is_string($str)) $str = strval($str);
		if (strlen($str) < $l){
			do {
				$str = $leftSide ? $padchar.$str : $str.$padchar;
			}
			while (strlen($str) < $l);
		}
		return $str;
	}

	// HTTP Status Codes \\
	$HTTP_STATUS_CODES = array(
		300 => 'Multiple Choices',
		301 => 'Moved Permanently',
		302 => 'Moved Temporarily',
		303 => 'See Other',
		304 => 'Not Modified',
		305 => 'Use Proxy',
		400 => 'Bad Request',
		401 => 'Unauthorized',
		402 => 'Payment Required',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		407 => 'Proxy Authentication Required',
		408 => 'Request Time-out',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Request Entity Too Large',
		414 => 'Request-URI Too Large',
		415 => 'Unsupported Media Type',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavailable',
		504 => 'Gateway Time-out',
		505 => 'HTTP Version not supported',
	);
	define('AND_DIE', true);
	function statusCodeHeader($code, $die = false){
		global $HTTP_STATUS_CODES;

		if (!isset($HTTP_STATUS_CODES[$code]))
			trigger_error('Érvénytelen státuszkód: '.$code,E_USER_ERROR);
		else
			header($_SERVER['SERVER_PROTOCOL'].' '.$code.' '.$HTTP_STATUS_CODES[$code]);

		if ($die === AND_DIE) die();
	}

	// CSRF Check \\
	function detectCSRF($CSRF = null){
		if (!isset($CSRF)) global $CSRF;
		if (isset($CSRF) && $CSRF)
			die(statusCodeHeader(401));
	}

	// oAuth Error Response Messages \\
	$OAUTH_RESPONSE = array(
		'invalid_request' => 'The authorization recest was not properly formatted.',
		'unsupported_response_type' => 'The authorization server does not support obtaining an authorization code using this method.',
		'unauthorized_client' => 'The authorization process did not complete. Please try again.',
		'invalid_scope' => 'The requested scope is invalid, unknown, or malformed.',
		'server_error' => "There's an issue on deviantArt's end. Try again later.",
		'temporarily_unavailable' => "There's an issue on deviantArt's end. Try again later.",
	);

	// Redirection URI shortcut \\
	function oauth_redirect_uri($state = true){
		global $do, $data;
		if ($do === 'index' && empty($data)) $returnURL = RELPATH;
		else $returnURL = rtrim("/$do/$data",'/');
		return '&redirect_uri='.urlencode(ABSPATH."da-auth").($state?'&state='.urlencode($returnURL):'');
	}

	/**
	 * Makes authenticated requests to the deviantArt API
	 *
	 * @param string $endpoint
	 * @param null|array $postdata
	 * @param null|string $token
	 * @return array
	 */
	function da_request($endpoint, $postdata = null, $token = null){
		global $signedIn, $currentUser;

		if (empty($token)){
			if (!$signedIn) die(trigger_error('Trying to make a request without signing in'));

			$token = $currentUser['Session']['access'];
		}

		$r = curl_init(preg_match('~^https?://~', $endpoint) ? $endpoint : "https://www.deviantart.com/api/v1/oauth2/$endpoint");
		curl_setopt($r, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($r, CURLOPT_HTTPHEADER, array("Authorization: Bearer $token"));

		if (!empty($postdata)){
			$query = '';
			foreach($postdata as $k => $v) $query .= "$k=$v&";
			rtrim($query, '&');
			curl_setopt($r,CURLOPT_POST, count($postdata));
			curl_setopt($r,CURLOPT_POSTFIELDS, $query);
		}
		$response = curl_exec($r);
		curl_close($r);

        return json_decode($response, true);
	}

	/**
	 * Returns the first element of an array of arrays
	 *  returned by the MySqlidb::query method
	 *  as received through $query
	 * Returns null if the query contains no results
	 *
	 * @param null|array $query
	 * @return null|array
	 */
	function rawquery_get_single_result($query){
		if (empty($query[0])) return null;
		else return $query[0];
	}

	/**
	 * Requests or refreshes an Access Token
	 * $type defaults to 'authorization_code'
	 *
	 * @param string $code
	 * @param null|string $type
	 */
	function da_get_token($code, $type = null){
		global $Database;

		if (empty($type) || !in_array($type,array('authorization_code','refresh_token'))) $type = 'authorization_code';
		$URL_Start = 'https://www.deviantart.com/oauth2/token?client_id='.DA_CLIENT.'&client_secret='.DA_SECRET."&grant_type=$type";

		switch ($type){
			case "authorization_code":
				$json = file_get_contents("$URL_Start&code=$code".oauth_redirect_uri(false));
			break;
			case "refresh_token":
				$json = file_get_contents("$URL_Start&refresh_token=$code");
			break;
		}

		if (empty($json)) die("Ow");
		$json = json_decode($json, true);
		if (empty($json['status'])) redirect("/da-auth?error={$json['error']}&error_description={$json['error_description']}");

		$userdata = da_request('user/whoami', null, $json['access_token']);

		$UserID = strtolower($userdata['userid']);
		$UserData = array(
			'name' => $userdata['username'],
			'avatar_url' => $userdata['usericon'],
		);
		$AuthData = array(
			'access' => $json['access_token'],
			'refresh' => $json['refresh_token'],
			'expires' => date('c',time()+intval($json['expires_in']))
		);

		add_browser($AuthData);

		if (empty($Database->where('id',$userdata['userid'])->get('users')))
			$Database->insert('users', array_merge($UserData, array('id' => $UserID)));
		else $Database->where('id',$userdata['userid'])->update('users', $UserData);

		if ($type === 'refresh_token') $Database->where('refresh', $code)->update('sessions',$AuthData);
		else $Database->insert('sessions', array_merge($AuthData, array('user' => $UserID)));

		Cookie::set('access',$AuthData['access'],THREE_YEARS);
	}

	/**
	 * Adds browser info to $Authdata
	 */
	function browser(){
		require_once "includes/Browser.php";
		$browser = new Browser();
		$Return = array();
		$name = $browser->getBrowser();
		if ($name !== Browser::BROWSER_UNKNOWN){
			$Return['browser_name'] = $name;

			$ver = $browser->getVersion();
			if ($ver !== Browser::VERSION_UNKNOWN)
				$Return['browser_ver'] = $ver;
		}
		return $Return;
	}
	function add_browser(&$AuthData){
		$browser = browser();
		if (!empty($browser))
			foreach (array_keys($browser) as $v)
				if (isset($browser[$v]))
					$AuthData[$v] = $browser[$v];
	}

	/**
	 * Makes a call to the dA oEmbed API to get public info about an artwork
	 * $type defaults to 'fav.me'
	 *
	 * @param string $ID
	 * @param null|string $type
	 * @return string
	 */
	function da_oembed($ID, $type = null){
		if (empty($type) || !in_array($type,array('fav.me','sta.sh'))) $type = 'fav.me';

		return array_merge(json_decode(file_get_contents('http://backend.deviantart.com/oembed?url='.urlencode("http://$type/$ID")), true),array('_provider' => $type));
	}

	/**
	 * Caches information about a deviation in the 'deviation_cache' table
	 * Returns null on failure
	 *
	 * @param string $ID
	 * @param null|string $type
	 * @return array|null
	 */
	$PROVIDER_FULLSIZE_KEY = array(
		'sta.sh' => 'url',
		'fav.me' => 'fullsize_url',
	);
	function da_cache_deviation($ID, $type = null){
		global $Database, $PROVIDER_FULLSIZE_KEY;

		$Deviation = $Database->where('id',$ID)->getOne('deviation_cache');
		if (empty($Deviation) || (!empty($Deviation['updated_on']) && strtotime($Deviation['updated_on'])+ONE_HOUR < time())){
			$json = da_oembed($ID, $type);
			if (empty($json)) return null;

			$Deviation = array(
				'title' => $json['title'],
				'preview' => $json['thumbnail_url'],
				'fullsize' => $json[$PROVIDER_FULLSIZE_KEY[$json['_provider']]],
				'provider' => $json['_provider'],
			);

			if (!empty($Deviation)){
				$Deviation['id'] = $ID;
				$Database->insert('deviation_cache', $Deviation);
			}
			else $Database->where('id',$Deviation['id'])->update('deviation_cache', $Deviation);
		}

		return $Deviation;
	}

	# Get Roles from DB
	$ROLES_ASSOC = array();
	$ROLES = array();
	foreach ($Database->orderBy('value','ASC')->get('roles') as $r){
		$ROLES_ASSOC[$r['name']] = $r['label'];
		$ROLES[] = $r['name'];
	}

	# Get Permissions from DB
	$PERMISSIONS = array();
	foreach ($Database->get('permissions') as $p)
		$PERMISSIONS[$p['action']] = $p['minrole'];

	function PERM($perm){
		if (!is_string($perm)) return false;

		global $signedIn;
		if (!$signedIn) return false;

		global $currentUser, $ROLES, $PERMISSIONS;

		if (in_array($perm,$ROLES)) $targetRole = $perm;
		else if (!empty($PERMISSIONS[$perm])) $targetRole = $PERMISSIONS[$perm];
		else trigger_error('Invalid permission '.$perm);

		return array_search($currentUser['role'],$ROLES) >= array_search($targetRole,$ROLES);
	}

	// Episode title matching pattern \\
	define('EP_TITLE_REGEX', '/^[A-Za-z \'\-!\d,&]{5,35}$/');

	/**
	 * Turns an 'episode' database row into a readable title
	 *
	 * @param array $Ep
	 * @return string
	 */
	define('AS_ARRAY',true);
	function format_episode_title($Ep, $returnArray = false, $arrayKey = null){
		$EpNumber = intval($Ep['episode']);

		if ($returnArray === AS_ARRAY) {
			if ($Ep['twoparter'])
				$Ep['episode'] = $EpNumber.'-'.($EpNumber+1);
			$arr = array(
				'id' => "S{$Ep['season']}E{$Ep['episode']}",
				'season' => $Ep['season'],
				'episode' => $Ep['episode'],
				'title' => $Ep['title'],
			);

			if (!empty($arrayKey))
				return isset($arr[$arrayKey]) ? $arr[$arrayKey] : null;
			else return $arr;
		}

		if ($Ep['twoparter'])
			$Ep['episode'] = pad($EpNumber).pad($EpNumber+1);
		else $Ep['episode'] = pad($Ep['episode']);
		$Ep['season'] = pad($Ep['season']);
		return "S{$Ep['season']} E{$Ep['episode']}: {$Ep['title']}";
	}

	/**
	 * Extracts the season and episode from the episode id
	 * Examples:
	 *   "S1E1" => {season:1,episode:1}
	 *   "S01E01" => {season:1,episode:1}
	 *   "S1E1-2" => {season:1,episode:1,twoparter:true}
	 *   "S01E01-02" => {season:1,episode:1,twoparter:true}
	 *
	 * @param string $id
	 * @return null|array
	 */
	define('EPISODE_ID_PATTERN','S(\d{1,2})E(\d{1,2})(-\d{1,2})?');
	function episode_id_parse($id){
		$match = array();
		if (preg_match('/^'.EPISODE_ID_PATTERN.'/', $id, $match))
			return array(
				'season' => intval($match[1]),
				'episode' => intval($match[2]),
				'twoparter' => !empty($match[3]),
			);
		else return null;
	}

	/**
	 * User Information Fetching
	 * -------------------------
	 * Fetch user info from dA upon request to nonexistant user
	 *
	 * @param string $username
	 * @return array|null
	 */
	define('USERNAME_PATTERN', '([A-Za-z\-\d]{1,20})');
	function fetch_user($username){
		global $Database;

		if (!preg_match('/^'.USERNAME_PATTERN.'$/', $username))
			return null;

		$userdata = da_request('user/whois', array('usernames[0]' => $username));

		if (empty($userdata['results'][0]))
			return null;

		$userdata = $userdata['results'][0];

		$insert = array(
			'id' => $userdata['userid'],
			'name' => $userdata['username'],
			'avatar_url' => $userdata['usericon'],
		);

		if (!$Database->insert('users',$insert))
			return null;

		return get_user($insert['name'], 'name');
	}

	/**
	 * User Information Retriever
	 * --------------------------
	 * Gets a single row from the 'users' database
	 *  where $coloumn is equal to $value
	 * Returns null if user is not found
	 *
	 * If $basic is set to false, then role
	 *  information will also be fetched
	 *
	 * @param string $value
	 * @param string $coloumn
	 * @return array|null
	 */
	function get_user($value, $coloumn = 'id'){
		global $Database;

		$User = array();
		if ($coloumn === "access"){
			$Auth = $Database->where('access', $value)->getOne('sessions');

			if (empty($Auth)) return null;
			$coloumn = 'id';
			$value = $Auth['user'];
		}

		$User = rawquery_get_single_result($Database->rawQuery(
			"SELECT
				users.*,
				roles.label as rolelabel
			FROM users
			LEFT JOIN roles ON roles.name = users.role
			WHERE users.`$coloumn` = ?",array($value)));

		if (empty($User) && $coloumn === 'name')
			$User = fetch_user($value);

		if (!empty($User) && isset($Auth)) $User['Session'] = $Auth;

		return $User;
	}

	/**
	 * deviantArt profile link generator
	 *
	 * @param array
	 * @return string
	 */
	define('TEXT_ONLY', 1);
	define('LINK_ONLY', 2);
	function da_link($User, $format = 0){
		if (!is_array($User)) trigger_error('$User is not an array');

		$Username = $User['name'];
		$username = strtolower($Username);
		$avatar = $format == 0 ? "<img src='{$User['avatar_url']}' class=avatar> " : '';
		$link = "http://$username.deviantart.com/";

		if ($format === LINK_ONLY) return $link;
		return "<a href='$link' class=da-userlink>$avatar<span class=name>$Username</span></a>";
	}

	// Reserved by section creator \\
	function get_reserver_button($By = null, $finished = false){
		global $signedIn, $currentUser;

		if ($By === false) return "<button class=reserve-request>Reserve</button>";
		if (empty($By) || $By === true){
			if (!$signedIn) trigger_error('Trying to get reserver button while not signed in');
			$By = $currentUser;
		}
		$dAlink = da_link($By);

		$HTML =  "<div class=reserver>$dAlink</div>";
		if (!$finished && $signedIn && $By['id'] === $currentUser['id']){
			$HTML .= <<<HTML

<div class=reserver-actions>
<button class="typcn typcn-times red">Cancel</button>
<button class="typcn typcn-tick green" disabled>I'm done</button>
</div>
HTML;

		}

		return $HTML;
	}

	// List ltem generator function for request & reservation renderers \\
	function get_r_li($R, $isRequest = false){
		$finished = !!$R['finished'];
		$thing = $isRequest ? 'request' : 'reservation';
		$HTML = "<li id=$thing-{$R['id']}>";
		$Image = "<div class='image screencap'><a href='{$R['fullsize']}'><img src='{$R['preview']}'></a></div><span class=label>{$R['label']}</span>";

		if (empty($R['reserved_by'])){
			$HTML .= $Image;
			if ($isRequest)
				$HTML .= get_reserver_button(false);
		}
		else {
			$R['reserver'] = get_user($R['reserved_by']);
			if ($finished){
				$D = da_cache_deviation($R['deviation_id']);
				$D['title'] = preg_replace("/'/",'&apos;',$D['title']);
				$Image = "<div class='image deviation'><a href='http://fav.me/{$D['id']}'><img src='{$D['preview']}' alt='{$D['title']}'></a></div>";
			}
			$HTML .= $Image.get_reserver_button($R['reserver'], $finished);
		}

		return "$HTML</li>";
	}

	// Get Request / Reservation Submission Form HTML \\
	function get_post_form($type){
		$Type = strtoupper($type[0]).substr($type,1);
		$HTML = <<<HTML

		<form class="hidden post-form" data-type="$type">
			<h2>Make a $type</h2>
			<div>
				<label>
					<span>Image URL</span>
					<input type="text" name="image_url" pattern="^.{2,255}$" required>
					<button class="check-img red">Check image</button>
				</label>
				<div class="hidden img-preview">
					<div class="notice fail">Please click the <strong>Check image</strong> button after providing an URL to get a preview & verify if the link is correct.</div>
				</div>

HTML;
			if ($type === 'request')
				$HTML .= <<<HTML
				<label>
					<span>$Type label</span>
					<input type="text" name="label" pattern="^.{2,255}$" required>
				</label>
				<label>
					<span>$Type type</span>
					<select name="type" required>
						<option value=chr>Character</option>
						<option value=bg>Background</option>
						<option value=obj>Object</option>
					</select>
				</label>

HTML;
			else {
				$HTML .= <<<HTML
				<label>
					<span>$Type label (optional)</span>
					<input type="text" name="label" pattern="^.{2,255}$">
				</label>

HTML;

			}
			$HTML .= <<<HTML
			</div>
			<button class=green>Submit $type</button> <button type="reset">Cancel</button>
		</form>
HTML;
			return $HTML;
	}

	// Render Reservation HTML\\
	function reservations_render($Reservations){
		$Arranged = array();
		$Arranged['unfinished'] =
		$Arranged['finished'] = '';
		if (!empty($Reservations) && is_array($Reservations)){

			foreach ($Reservations as $R)
				$Arranged[(!$R['finished']?'un':'').'finished'] .= get_r_li($R);
		}

		if (PERM('reservations.create')){
			$makeRes = '<button id="reservation-btn">Make a reservation</button>';
			$resForm = get_post_form('reservation');
		}
		else $resForm = $makeRes = '';

		echo <<<HTML
	<section id="reservations">
		<div class="unfinished">
			<h2>List of Reservations$makeRes</h2>
			<ul>{$Arranged['unfinished']}</ul>
		</div>
		<div class="finished">
			<h2>Finished Reservations</h2>
			<ul>{$Arranged['finished']}</ul>
		</div>$resForm
	</section>

HTML;
	}

	// Render Requests HTML \\
	$REQUEST_TYPES = array(
		'chr' => 'Characters',
		'obj' => 'Objects',
		'bg' => 'Backgrounds',
	);
	function requests_render($Requests){
		global $REQUEST_TYPES;

		$Arranged = array();
		if (!empty($Requests) && is_array($Requests)){
			$Arranged['unfinished'] = array();
			$Arranged['unfinished']['bg'] =
			$Arranged['unfinished']['obj'] =
			$Arranged['unfinished']['chr'] =
			$Arranged['finished'] = '';

			foreach ($Requests as $R){
				$HTML = get_r_li($R,true);

				if ($R['finished'])
					$Arranged['finished'] .= $HTML;
				else $Arranged['unfinished'][$R['type']] .= $HTML;
			}

			$Groups = '';
			foreach ($Arranged['unfinished'] as $g => $c)
				$Groups .= "<div class=group><h3>{$REQUEST_TYPES[$g]}:</h3><ul>{$c}</ul></div>";
		}
		else {
			$Groups = '<ul></ul>';
			$Arranged['finished'] = '';
		}

		if (PERM('user')){
			$makeRq = '<button id="request-btn">Make a request</button>';
			$reqForm = get_post_form('request');
		}
		else $reqForm = $makeRq = '';
		
		echo <<<HTML
	<section id="requests">
		<div class="unfinished">
			<h2>List of Requests$makeRq</h2>
			$Groups
		</div>
		<div class="finished">
			<h2>Finished Requests</h2>
			<ul>{$Arranged['finished']}</ul>
		</div>$reqForm
	</section>

HTML;
	}

	/**
	 * Retrieves requests & reservations for the episode specified
	 *
	 * @param int $season
	 * @param int $episode
	 * @return array
	 */
	function get_posts($season, $episode){
		global $Database;

		$Query =
			'SELECT *,
				IF(!ISNULL(r.deviation_id) && !ISNULL(r.reserved_by), 1, 0) as finished
			FROM `coloumn` r
			WHERE season = ? && episode = ?
			ORDER BY finished, posted';

		return array(
			$Database->rawQuery(str_ireplace('coloumn','requests',$Query),array($season, $episode)),
			$Database->rawQuery(str_ireplace('coloumn','reservations',$Query),array($season, $episode))
		);
	}

	/**
	 * Renders a sidebar link <li> item
	 *
	 * Accepts:
	 *   1) An URL, text and an optional title for the link
	 *   2) An array of arrays. The arrays should contain
	 *      / the elements of harmony. ...ahem, I mean... /
	 *      the elements:
	 *        0: url
	 *        1: text
	 *        2: title
	 *
	 * @param mixed $url
	 * @param string $text
	 * @param string $title
	 */
	function sidebar_link_render($url, $text = '', $title = null){
		$render = function($u, $tx, $tt){
			$tt = str_replace("'",'&apos;',$tt);
			echo "<li><a href='$u' title='$tt'>$tx</a></li>";
		};
		if (is_array($url) && empty($text) && empty($title))
			foreach ($url as $l) $render($l[0], $l[1], isset($l[2])?$l[2]:null);
		else $render($url, $text, $title);
	}

	// Renders the entire sidebar "Useful links" section \\
	function sidebar_links_render(){
		echo '<ul class="links">';
		// Member only links
		if (PERM('member'))
			sidebar_link_render(array(
				array(EP_DL_SITE,'Episode downloads','Download iTunes RAW 1080p episodes for best screencaps')
			));
		echo '</ul>';
	}
	
	// Renders the user card \\
	define('GUEST_AVATAR','/img/favicon.png');
	function usercard_render(){
		global $signedIn, $currentUser;
		if ($signedIn){
			$avatar = $currentUser['avatar_url'];
			$un = $currentUser['name'];
			$username = "<a href='/u/$un'>$un</a>";
			$rolelabel = $currentUser['rolelabel'];
			$Avatar = get_avatar_wrap($currentUser);
		}
		else {
			$avatar = GUEST_AVATAR;
			$username = 'Curious Pony';
			$rolelabel = 'Guest';
			$Avatar = get_avatar_wrap(array(
				'avatar_url' => $avatar,
				'name' => $username,
				'rolelabel' => $rolelabel,
				'guest' => true,
			));
		}

		echo <<<HTML
		<div class=usercard>
			$Avatar
			<span class="un">$username</span>
			<span class="role">$rolelabel</span>
		</div>
HTML;
	}

	/**
	 * Converts role label to badge initials
	 * -------------------------------------
	 * Related: http://stackoverflow.com/a/30740511/1344955
	 *
	 * @param string $label
	 *
	 * @return string
	 */
	function label_to_initials($label){
		return preg_replace('/(?:^|\s)([A-Z])|./','$1',$label);
	}

	// Renders avatar wrapper for a specific user \\
	function get_avatar_wrap($User){
		$badge = '';
		if (empty($User['guest']))
			$badge = "<span class=badge>".label_to_initials($User['rolelabel'])."</span>";
		return "<div class=avatar-wrap><img src='{$User['avatar_url']}' class=avatar>$badge</div>";
	}

	/**
	 * Returns all episodes from the database, properly sorted
	 *
	 * @return array
	 */
	function get_episodes(){
		global $Database;

		return $Database->orderBy('season')->orderBy('episode')->get('episodes');
	}

	/**
	 * Get the <tbody> contents for the episode list table
	 *
	 * @param array|null $Episode
	 *
	 * @return string
	 */
	function get_eptable_tbody($Episode = null){
		if (!isset($Episodes)) $Episodes = get_episodes();

		if (empty($Episodes)) return "<tr class='empty align-center'><td colspan=3><em>There are no episodes to display</em></td></tr>";

		$Body = '';
		$PathStart = '/episode/';
		foreach ($Episodes as $i => $ep) {
			$Title = format_episode_title($ep, AS_ARRAY);
			$href = $PathStart.$Title['id'];
			if (PERM('episodes.manage')) $adminControls = <<<HTML
<span class=admincontrols>
	<button class="edit-episode typcn typcn-spanner blue" title="Edit episode"></button>
	<button class="delete-episode typcn typcn-times red" title="Delete episode"></button>
</span>
HTML;
			else $adminControls = '';

			$star = $i === 0 ? '<span class="typcn typcn-eye" title="Curently visible on the homepage"></span> ' : '';

			$Body .= <<<HTML
		<tr data-epid="{$Title['id']}">
			<td class=season>{$Title['season']}</td>
			<td class=episode><span>{$Title['episode']}</span></td>
			<td class=title>$star<a href="$href">{$Title['title']}</a>$adminControls</td>
		</tr>
HTML;
		}
		return $Body;
	}

	/**
	 * If an episode is a two-parter's second part, then returns the first part
	 * Otherwise returns the episode itself
	 *
	 * @param int $episode
	 * @param int $season
	 * @param null|string $cols
	 *
	 * @return array|null
	 */
	function get_real_episode($season, $episode, $cols = null){
		global $Database;

		$Ep1 = $Database->where('season',$season)->where('episode',$episode)->getOne('episodes', $cols);
		if (empty($Ep1)){
			$Part1 = $Database->where('season',$season)->where('episode',$episode-1)->getOne('episodes', $cols);
			return !empty($Part1) && isset($Part1['twoparter']) && !!$Part1['twoparter'] ? $Part1 : null;
		}
		else return $Ep1;
	}

	/**
	 * Adds 's/S' to the end of a word
	 *
	 * @param string $w
	 *
	 * @return string
	 */
	 function s($w){
	    return "$w'".(substr($w, -1) !== 's'?'s':'');
	 }

	/**
	 * Parse session array for user page
	 */
	define('CURRENT',true);
	function render_session_li($Session, $current = false){
		$browserClass = preg_replace('/[^a-z]/','',strtolower($Session['browser_name']));
		$browserTitle = "{$Session['browser_name']} {$Session['browser_ver']}".($current?' (current)':'');
		$firstuse = timetag($Session['created']);
		$lastuse = timetag($Session['lastvisit']);
		$signoutText = 'Sign out' . (!$current ? ' from this session' : '');
		$remover = "<button class='typcn typcn-arrow-back remove orange' title='$signoutText' data-sid={$Session['id']}></button>";
		echo <<<HTML
<li class="browser-$browserClass">
	<span class=browser>$remover $browserTitle</span>
	<span class=created>Created: $firstuse</span>
	<span class=used>Last used: $lastuse</span>
</li>
HTML;
	 }
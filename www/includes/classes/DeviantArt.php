<?php

	class DeviantArt {
		private static
			$_CACHE_BAILOUT = false,
			$_MASS_CACHE_LIMIT = 5,
			$_MASS_CACHE_USED = 0;

		// oAuth Error Response Messages \\
		static $OAUTH_RESPONSE = array(
			'invalid_request' => 'The authorization recest was not properly formatted.',
			'unsupported_response_type' => 'The authorization server does not support obtaining an authorization code using this method.',
			'unauthorized_client' => 'The authorization process did not complete. Please try again.',
			'invalid_scope' => 'The requested scope is invalid, unknown, or malformed.',
			'server_error' => "There's an issue on DeviantArt's end. Try again later.",
			'temporarily_unavailable' => "There's an issue on DeviantArt's end. Try again later.",
			'user_banned' => 'You were banned on our website by a staff member.',
		);

		// Redirection URI shortcut \\
		static function OAuthRedirectURI(){
			return '&redirect_uri='.urlencode(ABSPATH.'da-auth');
		}

		// oAuth Authorization page URL generator
		static function GetAuthorizationURL(){
			return "https://www.deviantart.com/oauth2/authorize?response_type=code&scope=user+browse&client_id=".DA_CLIENT.self::OAuthRedirectURI();
		}

		/**
		 * Makes authenticated requests to the DeviantArt API
		 *
		 * @param string      $endpoint
		 * @param null|array  $postdata
		 * @param null|string $token
		 *
		 * @return array
		 */
		static function Request($endpoint, $token = null, $postdata = null){
			global $signedIn, $currentUser, $http_response_header;

			$requestHeaders = array("Accept-Encoding: gzip","User-Agent: MLPVC-RR @ ".GITHUB_URL);
			if (!isset($token) && $signedIn)
				$token = $currentUser['Session']['access'];
			if (!empty($token)) $requestHeaders[] = "Authorization: Bearer $token";
			else if ($token !== false) return null;

			$requestURI  = regex_match(new RegExp('^https?://'), $endpoint) ? $endpoint : "https://www.deviantart.com/api/v1/oauth2/$endpoint";

			$r = curl_init($requestURI);
			$curl_opt = array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_HTTPHEADER => $requestHeaders,
				CURLOPT_HEADER => 1,
				CURLOPT_BINARYTRANSFER => 1,
			);
			if (!empty($postdata)){
				$query = array();
				foreach($postdata as $k => $v) $query[] = urlencode($k).'='.urlencode($v);
				$curl_opt[CURLOPT_POST] = count($postdata);
				$curl_opt[CURLOPT_POSTFIELDS] = implode('&', $query);
			}
			curl_setopt_array($r, $curl_opt);

			$response = curl_exec($r);
			$responseCode = curl_getinfo($r, CURLINFO_HTTP_CODE);
			$headerSize = curl_getinfo($r, CURLINFO_HEADER_SIZE);

			$responseHeaders = rtrim(substr($response, 0, $headerSize));
			$response = substr($response, $headerSize);
			$http_response_header = array_map("rtrim",explode("\n",$responseHeaders));
			$curlError = curl_error($r);
			curl_close($r);

			if ($responseCode < 200 || $responseCode >= 300)
				throw new cURLRequestException(rtrim("cURL fail for URL \"$requestURI\" (HTTP $responseCode); $curlError",' ;'), $responseCode);

			if (regex_match(new RegExp('Content-Encoding:\s?gzip'), $responseHeaders))
				$response = gzdecode($response);
			return JSON::Decode($response, true);
		}

		/**
		 * Caches information about a deviation in the 'deviation_cache' table
		 * Returns null on failure
		 *
		 * @param string      $ID
		 * @param null|string $type
		 * @param bool        $mass
		 *
		 * @return array|null
		 */
		static function GetCachedSubmission($ID, $type = 'fav.me', $mass = false){
			global $Database, $FULLSIZE_MATCH_REGEX;

			if ($type === 'sta.sh')
				$ID = CoreUtils::NomralizeStashID($ID);

			$Deviation = $Database->where('id',$ID)->getOne('deviation_cache');
			if (!self::$_CACHE_BAILOUT && (empty($Deviation) && self::$_MASS_CACHE_USED <= self::$_MASS_CACHE_LIMIT) || (!empty($Deviation['updated_on']) && strtotime($Deviation['updated_on'])+(ONE_HOUR*5) < time())){
				try {
					$json = self::oEmbed($ID, $type);
				}
				catch (Exception $e){
					if (!empty($Deviation))
						$Database->where('id',$Deviation['id'])->update('deviation_cache', array('updated_on' => date('c',strtotime('+1 minute', time()))));

					$ErrorMSG = "Saving local data for $ID@$type failed: ".$e->getMessage();
					if (!Permission::Sufficient('developer')) trigger_error($ErrorMSG);

					if (POST_REQUEST)
						CoreUtils::Respond($ErrorMSG);
					else echo "<div class='notice fail'><label>da_cache_deviation($ID, $type)</label><p>$ErrorMSG</p></div>";

					$CACHE_BAILOUT = true;
					return $Deviation;
				}

				$insert = array(
					'title' => $json['title'],
					'preview' => URL::MakeHttps($json['thumbnail_url']),
					'fullsize' => URL::MakeHttps(isset($json['fullsize_url']) ? $json['fullsize_url'] : $json['url']),
					'provider' => $type,
					'author' => $json['author_name'],
					'updated_on' => date('c'),
				);

				if ($type === 'sta.sh' && !regex_match($FULLSIZE_MATCH_REGEX, $insert['fullsize'])){
					$fullsize_attempt = CoreUtils::GetStashFullsizeURL($ID);
					if (is_string($fullsize_attempt))
						$insert['fullsize'] = $fullsize_attempt;
				}

				if (empty($Deviation)){
					$insert['id'] = $ID;
					$Database->insert('deviation_cache', $insert);
				}
				else {
					$Database->where('id',$Deviation['id'])->update('deviation_cache', $insert);
					$insert['id'] = $ID;
				}

				self::$_MASS_CACHE_USED++;
				$Deviation = $insert;
			}
			else if (!empty($Deviation['updated_on'])){
				$Deviation['updated_on'] = date('c', strtotime($Deviation['updated_on']));
				if (self::$_CACHE_BAILOUT)
					$Database->where('id',$Deviation['id'])->update('deviation_cache', array(
						'updated_on' => $Deviation['updated_on'],
					));
			}

			return $Deviation;
		}

		/**
		 * Makes a call to the dA oEmbed API to get public info about an artwork
		 * $type defaults to 'fav.me'
		 *
		 * @param string      $ID
		 * @param null|string $type
		 *
		 * @return string
		 */
		static function  oEmbed($ID, $type){
			if (empty($type) || !in_array($type,array('fav.me','sta.sh'))) $type = 'fav.me';

			if ($type === 'sta.sh')
				$ID = CoreUtils::NomralizeStashID($ID);
			try {
				$data = DeviantArt::Request('http://backend.deviantart.com/oembed?url='.urlencode("http://$type/$ID"),false);
			}
			catch (cURLRequestException $e){
				if ($e->getCode() == 404)
					throw new Exception("Image not found. The URL may be incorrect or the image has been deleted.");
				else throw new Exception("Image could not be retrieved (HTTP {$e->getCode()})");
			}

			return $data;
		}

		/**
		 * Requests or refreshes an Access Token
		 * $type defaults to 'authorization_code'
		 *
		 * @param string $code
		 * @param null|string $type
		 */
		static function GetToken($code, $type = null){
			global $Database, $http_response_header;

			if (empty($type) || !in_array($type,array('authorization_code','refresh_token'))) $type = 'authorization_code';
			$URL_Start = 'https://www.deviantart.com/oauth2/token?client_id='.DA_CLIENT.'&client_secret='.DA_SECRET."&grant_type=$type";

			switch ($type){
				case "authorization_code":
					$json = DeviantArt::Request("$URL_Start&code=$code".DeviantArt::OAuthRedirectURI(),false);
				break;
				case "refresh_token":
					$json = DeviantArt::Request("$URL_Start&refresh_token=$code",false);
				break;
			}

			if (empty($json)){
				if (Cookie::exists('access')){
					$Database->where('access', Cookie::get('access'))->delete('sessions');
					Cookie::delete('access');
				}
				CoreUtils::Redirect("/da-auth?error=server_error&error_description={$http_response_header[0]}");
			}
			if (empty($json['status'])) CoreUtils::Redirect("/da-auth?error={$json['error']}&error_description={$json['error_description']}");

			$userdata = DeviantArt::Request('user/whoami', $json['access_token']);

			$User = $Database->where('id',$userdata['userid'])->getOne('users');
			if ($User['role'] === 'ban'){
				$_GET['error'] = 'user_banned';
				$BanReason = $Database
					->where('target', $User['id'])
					->orderBy('entryid', 'ASC')
					->getOne('log__banish');
				if (!empty($BanReason))
					$_GET['error_description'] = $BanReason['reason'];

				return;
			}

			$UserID = strtolower($userdata['userid']);
			$UserData = array(
				'name' => $userdata['username'],
				'avatar_url' => URL::MakeHttps($userdata['usericon']),
			);
			$AuthData = array(
				'access' => $json['access_token'],
				'refresh' => $json['refresh_token'],
				'expires' => date('c',time()+intval($json['expires_in'])),
				'scope' => $json['scope'],
			);

			$cookie = random_bytes(64);
			$AuthData['token'] = sha1($cookie);

			$browser = CoreUtils::DetectBrowser();
			foreach (array_keys($browser) as $v)
				if (isset($browser[$v]))
					$AuthData[$v] = $browser[$v];

			if (empty($User)){
				$MoreInfo = array(
					'id' => $UserID,
					'role' => 'user',
				);
				$makeDev = !$Database->has('users');
				if ($makeDev)
					$MoreInfo['id'] = strtoupper($MoreInfo['id']);
				$Insert = array_merge($UserData, $MoreInfo);
				$Database->insert('users', $Insert);
				if ($makeDev)
					User::UpdateRole($Insert, 'developer');
			}
			else $Database->where('id',$UserID)->update('users', $UserData);

			if (empty($makeDev) && (!empty($User) && !Permission::Sufficient('member', $User['role']) || empty($User)) && User::IsClubMember($UserData['name']))
				User::UpdateRole(array(
					'id' => $UserID,
					'role' => isset($User['role']) ? $User['role'] : 'user',
				), 'member');

			if ($type === 'refresh_token')
				$Database->where('refresh', $code)->update('sessions',$AuthData);
			else {
				$Database->where('user', $User['id'])->where('scope', $AuthData['scope'], '!=')->delete('sessions');
				$Database->insert('sessions', array_merge($AuthData, array('user' => $UserID)));
			}

			$interval = $User['name'] === 'dcencia' ? '1 WEEK' : '1 MONTH';
			$Database->rawQuery("DELETE FROM sessions WHERE \"user\" = ? && lastvisit <= NOW() - INTERVAL '$interval'", array($UserID));


			Cookie::set('access', $cookie, ONE_YEAR);
		}

		/**
		 * Handles the DeviantArt autherntication process
		 */
		static function HandleAuth(){
			global $err, $errdesc, $REWRITE_REGEX;

			if (!isset($_GET['error']) && (empty($_GET['code']) || empty($_GET['state'])))
				$_GET['error'] = 'unauthorized_client';
			if (isset($_GET['error'])){
				$err = $_GET['error'];
				if (isset($_GET['error_description']))
					$errdesc = $_GET['error_description'];
				global $signedIn;
				if ($signedIn)
					CoreUtils::Redirect($_GET['state']);
				Episode::LoadPage();
			}
			DeviantArt::GetToken($_GET['code']);

			if (isset($_GET['error'])){
				$err = $_GET['error'];
				if (isset($_GET['error_description']))
					$errdesc = $_GET['error_description'];

				if ($err === 'user_banned')
					$errdesc .= "\n\nIf you'd like to appeal your ban, please <a href='http://mlp-vectorclub.deviantart.com/notes/'>send the group a note</a>.";
				Episode::LoadPage();
			}

			if (regex_match(new RegExp('^[a-z\d]+$','i'), $_GET['state'], $_match)){
				$confirm = str_replace('{{CODE}}', $_match[0], file_get_contents('views/loginConfrim.html'));
				die($confirm);
			}
			else if (regex_match($REWRITE_REGEX, $_GET['state']))
				CoreUtils::Redirect($_GET['state']);

			CoreUtils::Redirect('/');
		}
	}

	class cURLRequestException extends Exception {
		public function __construct($errMsg, $errCode){
			$this->message = $errMsg;
			$this->code = $errCode;
		}
	}

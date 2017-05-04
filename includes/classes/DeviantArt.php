<?php

namespace App;

use App\Controllers\AuthController;
use App\Models\CachedDeviation;
use App\Models\User;
use App\Exceptions\CURLRequestException;

class DeviantArt {
	private static
		$_CACHE_BAILOUT = false,
		$_MASS_CACHE_LIMIT = 15,
		$_MASS_CACHE_USED = 0;

	// oAuth Error Response Messages \\
	const OAUTH_RESPONSE = array(
		'invalid_request' => 'The authorization recest was not properly formatted.',
		'unsupported_response_type' => 'The authorization server does not support obtaining an authorization code using this method.',
		'unauthorized_client' => 'The authorization process did not complete. Please try again.',
		'invalid_scope' => 'The requested scope is invalid, unknown, or malformed.',
		'server_error' => "There seems to be an issue on DeviantArt’s end. Try again later.",
		'temporarily_unavailable' => "There’s an issue on DeviantArt’s end. Try again later.",
		'user_banned' => 'You were banned on our website by a staff member.',
	);

	/**
	 * Makes authenticated requests to the DeviantArt API
	 *
	 * @param string      $endpoint
	 * @param null|array  $postdata
	 * @param null|string $token
	 *
	 * @return array
	 */
	static function request($endpoint, $token = null, $postdata = null){
		global $http_response_header;

		$requestHeaders = array("Accept-Encoding: gzip","User-Agent: MLPVC-RR @ ".GITHUB_URL);
		if (!isset($token) && Auth::$signed_in)
			$token = Auth::$session->access;
		if (!empty($token)) $requestHeaders[] = "Authorization: Bearer $token";
		else if ($token !== false) return null;

		$requestURI  = preg_match(new RegExp('^https?://'), $endpoint) ? $endpoint : "https://www.deviantart.com/api/v1/oauth2/$endpoint";

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

		$responseHeaders = rtrim(CoreUtils::substring($response, 0, $headerSize));
		$response = CoreUtils::substring($response, $headerSize);
		$http_response_header = array_map("rtrim",explode("\n",$responseHeaders));
		$curlError = curl_error($r);
		curl_close($r);

		if ($responseCode < 200 || $responseCode >= 300)
			throw new CURLRequestException(rtrim("cURL fail for URL \"$requestURI\" (HTTP $responseCode); $curlError",' ;'), $responseCode);

		if (preg_match(new RegExp('Content-Encoding:\s?gzip'), $responseHeaders))
			$response = gzdecode($response);
		return JSON::decode($response);
	}

	/**
	 * Caches information about a deviation in the 'cached-deviations' table
	 * Returns null on failure
	 *
	 * @param string      $ID
	 * @param null|string $type
	 * @param bool        $mass
	 *
	 * @return CachedDeviation
	 */
	static function getCachedDeviation($ID, $type = 'fav.me', $mass = false){
		global $Database, $FULLSIZE_MATCH_REGEX;

		if ($type === 'sta.sh')
			$ID = CoreUtils::nomralizeStashID($ID);

		/** @var $Deviation CachedDeviation */
		$Deviation = $Database->where('id', $ID)->where('provider', $type)->getOne('cached-deviations');

		$cacheExhausted = self::$_MASS_CACHE_USED > self::$_MASS_CACHE_LIMIT;
		$cacheExpired = empty($Deviation->updated_on) ? true : strtotime($Deviation->updated_on)+(Time::IN_SECONDS['hour']*12) < time();

		$lastRequestSuccessful = !self::$_CACHE_BAILOUT;
		$localDataMissing = empty($Deviation);
		$massCachingWithinLimit = $mass && !$cacheExhausted;
		$notMassCachingAndCacheExpired = !$mass && $cacheExpired;

		if ($lastRequestSuccessful && ($localDataMissing || (($massCachingWithinLimit && $cacheExpired) || $notMassCachingAndCacheExpired))){
			try {
				$json = self::oEmbed($ID, $type);
				if (empty($json))
					throw new \Exception();
			}
			catch (\Exception $e){
				if (!empty($Deviation))
					$Database->where('id',$Deviation->id)->update('cached-deviations', array('updated_on' => date('c', time()+Time::IN_SECONDS['minute'] )));

				error_log("Saving local data for $ID@$type failed: ".$e->getMessage()."\n".$e->getTraceAsString());

				if ($e->getCode() === 404){
					$Deviation = null;
				}

				self::$_CACHE_BAILOUT = true;
				return $Deviation;
			}

			$insert = array(
				'title' => preg_replace(new RegExp('\\\\\''),"'",$json['title']),
				'preview' => isset($json['thumbnail_url']) ? URL::makeHttps($json['thumbnail_url']) : null,
				'fullsize' => isset($json['fullsize_url']) ? URL::makeHttps($json['fullsize_url']) : null,
				'provider' => $type,
				'author' => $json['author_name'],
				'updated_on' => date('c'),
			);

			switch ($json['type']){
				case "photo":
				case "link":
					$insert['type'] = $json['imagetype'];
				break;
				case "rich":
					if (isset($json['html'])){
						$DATA_EXTENSION_REGEX = new RegExp('^[\s\S]*\sdata-extension="([a-z\d]+?)"[\s\S]*$');
						if ($DATA_EXTENSION_REGEX->match($json['html']))
							$insert['type'] = $DATA_EXTENSION_REGEX->replace('$1',$json['html']);

						$H2_EXTENSION_REGEX = new RegExp('^[\s\S]*<h2>([A-Z\d]+?)</h2>[\s\S]*$');
						if ($H2_EXTENSION_REGEX->match($json['html']))
							$insert['type'] = strtolower($H2_EXTENSION_REGEX->replace('$1',$json['html']));
					}
				break;
			}

			if (!preg_match($FULLSIZE_MATCH_REGEX, $insert['fullsize'])){
				$fullsize_attempt = CoreUtils::getFullsizeURL($ID, $type);
				if (is_string($fullsize_attempt))
					$insert['fullsize'] = $fullsize_attempt;
			}

			if (empty($Deviation))
				$Deviation = $Database->where('id', $ID)->where('provider', $type)->getOne('cached-deviations');
			if (empty($Deviation)){
				$insert['id'] = $ID;
				$Database->insert('cached-deviations', $insert);
			}
			else {
				$Database->where('id',$Deviation->id)->update('cached-deviations', $insert);
				$insert['id'] = $ID;
			}

			self::$_MASS_CACHE_USED++;
			$Deviation = new CachedDeviation($insert);
		}
		else if (!empty($Deviation->updated_on)){
			$Deviation->updated_on = date('c', strtotime($Deviation->updated_on));
			if (self::$_CACHE_BAILOUT)
				$Database->where('id',$Deviation->id)->update('cached-deviations', array(
					'updated_on' => $Deviation->updated_on,
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
	 * @return array
	 */
	static function  oEmbed($ID, $type){
		if (empty($type) || !in_array($type,array('fav.me','sta.sh'))) $type = 'fav.me';

		if ($type === 'sta.sh')
			$ID = CoreUtils::nomralizeStashID($ID);
		try {
			$data = DeviantArt::request('http://backend.deviantart.com/oembed?url='.urlencode("http://$type/$ID"),false);
		}
		catch (CURLRequestException $e){
			if ($e->getCode() == 404)
				throw new \Exception("Image not found. The URL may be incorrect or the image has been deleted.", 404);
			else throw new \Exception("Image could not be retrieved (HTTP {$e->getCode()})", $e->getCode());
		}

		return $data;
	}

	/**
	 * Requests or refreshes an Access Token
	 * $type defaults to 'authorization_code'
	 *
	 * @param string $code
	 * @param null|string $type
	 *
	 * @return User|void
	 */
	static function getToken(string $code, string $type = null){
		global $Database, $http_response_header;

		if (empty($type) || !in_array($type,array('authorization_code','refresh_token'))) $type = 'authorization_code';
		$URL_Start = 'https://www.deviantart.com/oauth2/token?client_id='.DA_CLIENT.'&client_secret='.DA_SECRET."&grant_type=$type";

		switch ($type){
			case "authorization_code":
				$json = DeviantArt::request("$URL_Start&code=$code".OAUTH_REDIRECT_URI,false);
			break;
			case "refresh_token":
				$json = DeviantArt::request("$URL_Start&refresh_token=$code",false);
			break;
		}

		if (empty($json)){
			if (Cookie::exists('access')){
				$Database->where('access', Cookie::get('access'))->delete('sessions');
				Cookie::delete('access', Cookie::HTTPONLY);
			}
			HTTP::redirect("/da-auth?error=server_error&error_description={$http_response_header[0]}");
		}
		if (empty($json['status'])) HTTP::redirect("/da-auth?error={$json['error']}&error_description={$json['error_description']}");

		$userdata = DeviantArt::request('user/whoami', $json['access_token']);

		/** @var $User Models\User */
		$User = $Database->where('id',$userdata['userid'])->getOne('users');
		if (isset($User->role) && $User->role === 'ban'){
			$_GET['error'] = 'user_banned';
			$BanReason = $Database
				->where('target', $User->id)
				->orderBy('entryid', 'ASC')
				->getOne('log__banish');
			if (!empty($BanReason))
				$_GET['error_description'] = $BanReason['reason'];

			return;
		}

		$UserID = strtolower($userdata['userid']);
		$UserData = array(
			'name' => $userdata['username'],
			'avatar_url' => URL::makeHttps($userdata['usericon']),
		);
		$AuthData = array(
			'access' => $json['access_token'],
			'refresh' => $json['refresh_token'],
			'expires' => date('c',time()+intval($json['expires_in'])),
			'scope' => $json['scope'],
		);

		$cookie = bin2hex(random_bytes(64));
		$AuthData['token'] = CoreUtils::sha256($cookie);

		$browser = CoreUtils::detectBrowser();
		foreach ($browser as $k => $v)
			if (!empty($v))
				$AuthData[$k] = $v;

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

			$User = new User($Insert);
			if ($makeDev)
				$User->updateRole('developer');
		}
		else $Database->where('id',$UserID)->update('users', $UserData);

		if (empty($makeDev) && !empty($User)){
			$clubmember = $User->isClubMember();
			$permmember = Permission::sufficient('member', $User->role);
			if ($clubmember && !$permmember)
				$User->updateRole(DeviantArt::getClubRole($User));
			else if (!$clubmember && $permmember)
				$User->updateRole('user');
		}

		if ($type === 'refresh_token')
			$Database->where('refresh', $code)->update('sessions',$AuthData);
		else {
			$Database->where('user', $User->id)->where('scope', $AuthData['scope'], '!=')->delete('sessions');
			$Database->insert('sessions', array_merge($AuthData, array('user' => $UserID)));
		}

		$Database->rawQuery("DELETE FROM sessions WHERE \"user\" = ? && lastvisit <= NOW() - INTERVAL '1 MONTH'", array($UserID));

		Cookie::set('access', $cookie, time()+ Time::IN_SECONDS['year'], Cookie::HTTPONLY);
		return $User ?? null;
	}

	static function isImageAvailable(string $url, array $onlyFails = []):bool {
		if (CoreUtils::isURLAvailable($url, $onlyFails))
			return true;
		CoreUtils::msleep(300);
		if (CoreUtils::isURLAvailable($url, $onlyFails))
			return true;
		CoreUtils::msleep(300);
		if (CoreUtils::isURLAvailable("$url?", $onlyFails))
			return true;
		CoreUtils::msleep(300);
		if (CoreUtils::isURLAvailable("$url?", $onlyFails))
			return true;
		return false;
	}

	/**
	 * Parses various DeviantArt pages and returns the usernames of members along with their role
	 * Results are cached for 10 minutes
	 *
	 * @return array [ 'username' => 'role', ... ]
	 */
	static function getMemberList():array {
		$cache = CachedFile::init(FSPATH.'members.json', Time::IN_SECONDS['minute']*10);
		if (!$cache->expired())
			return $cache->read();

		$usernames = [];
		$off = 0;
		// Get regular members
		while ($off < 200){
			$memberlist = HTTP::legitimateRequest("http://mlp-vectorclub.deviantart.com/modals/memberlist/?offset=$off");
			$dom = new \DOMDocument();
			$internalErrors = libxml_use_internal_errors(true);
			$dom->loadHTML($memberlist['response']);
			libxml_use_internal_errors($internalErrors);
			$members = $dom->getElementById('userlist')->childNodes->item(0)->childNodes;
			foreach ($members as $node){
				$username = $node->lastChild->firstChild->textContent;
				$usernames[$username] = 'member';
			}
			$xp = new \DOMXPath($dom);
			$more =  $xp->query('//ul[@class="pages"]/li[@class="next"]');
			if ($more->length === 0 || $more->item(0)->firstChild->getAttribute('class') === 'disabled')
				break;
			$off += 100;
		}
		unset($dom);
		unset($xp);

		// Get staff
		$requri = 'http://mlp-vectorclub.deviantart.com/global/difi/?c%5B%5D=%22GrusersModules%22%2C%22displayModule%22%2C%5B%2217450764%22%2C%22374037863%22%2C%22generic%22%2C%7B%7D%5D&iid=576m8f040364c99a7d9373611b4a9414d434-j2asw8mn-1.1&mp=2&t=json';
		$stafflist = JSON::decode(HTTP::legitimateRequest($requri)['response'], false);
		$stafflist = $stafflist->DiFi->response->calls[0]->response->content->html;
		$stafflist = str_replace('id="gmi-GAboutUsModule_Item"','',$stafflist);
		$dom = new \DOMDocument();
		$dom->loadHTML($stafflist);
		$xp = new \DOMXPath($dom);
		$admins =  $xp->query('//div[@id="aboutus"]//div[@class="user-name"]');
		$revroles = array_flip(Permission::ROLES_ASSOC);
		foreach ($admins as $admin){
			$username = $admin->childNodes->item(1)->firstChild->textContent;
			$role = CoreUtils::makeSingular($admin->childNodes->item(3)->textContent);
			if (!isset($revroles[$role]))
				throw new \Exception("Role $role not reversible");
			$usernames[$username] = $revroles[$role];
		}

		$cache->update($usernames);

		return $usernames;
	}

	/**
	 * @param User $user
	 *
	 * @return null|string
	 */
	static function getClubRole(User $user):?string {
		$usernames = self::getMemberList();
		return $usernames[$user->name] ?? null;
	}
}

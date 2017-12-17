<?php

namespace App;

/**
 * File: Browser.php
 * Author: Chris Schuld (http://chrisschuld.com/)
 * Last Modified: July 4th, 2014
 *
 * @version 1.9
 * @package PegasusPHP
 * @url https://github.com/cbschuld/Browser.php
 * Copyright (C) 2008-2010 Chris Schuld  (chris@chrisschuld.com)
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License as
 * published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details at:
 * http://www.gnu.org/copyleft/gpl.html
 * Typical Usage:
 *   $browser = new Browser();
 *   if( $browser->getBrowser() == Browser::BROWSER_FIREFOX && $browser->getVersion() >= 2 ) {
 *    echo 'You have FireFox version 2 or greater';
 *   }
 * User Agents Sampled from: http://www.useragentstring.com/
 * This implementation is based on the original work from Gary White
 * http://apptools.com/phptools/browser/
 * IE Mobile browser & Windows Phone platform check implemented by SeinopSys
 * http://github.com/SeinopSys
 */
class Browser {
	private $_agent = '';
	private $_platform = '';
	private $_browserName = '';
	private $_version = '';
	private $_isMobile = false;
	private $_isTablet = false;
	private $_isRobot = false;
	private $_isFacebook = false;

	public const BROWSER_UNKNOWN = 'unknown';
	public const VERSION_UNKNOWN = 'unknown';

	public const BROWSER_OPERA = 'Opera'; // http://www.opera.com/
	public const BROWSER_OPERA_MINI = 'Opera Mini'; // http://www.opera.com/mini/
	public const BROWSER_WEBTV = 'WebTV'; // http://www.webtv.net/pc/
	public const BROWSER_IE = 'Internet Explorer'; // http://www.microsoft.com/ie/
	public const BROWSER_EDGE = 'Edge'; // https://www.microsoft.com/en-us/windows/microsoft-edge
	public const BROWSER_IEMOBILE = 'IE Mobile'; // http://en.wikipedia.org/wiki/Internet_Explorer_Mobile
	public const BROWSER_VIVALDI = 'Vivaldi'; // http://vivaldi.com/
	public const BROWSER_KONQUEROR = 'Konqueror'; // http://www.konqueror.org/
	public const BROWSER_ICAB = 'iCab'; // http://www.icab.de/
	public const BROWSER_OMNIWEB = 'OmniWeb'; // http://www.omnigroup.com/applications/omniweb/
	public const BROWSER_FIREBIRD = 'Firebird'; // http://www.ibphoenix.com/
	public const BROWSER_FIREFOX = 'Firefox'; // http://www.mozilla.com/en-US/firefox/firefox.html
	public const BROWSER_ICEWEASEL = 'Iceweasel'; // http://www.geticeweasel.org/
	public const BROWSER_SHIRETOKO = 'Shiretoko'; // http://wiki.mozilla.org/Projects/shiretoko
	public const BROWSER_MOZILLA = 'Mozilla'; // http://www.mozilla.com/en-US/
	public const BROWSER_AMAYA = 'Amaya'; // http://www.w3.org/Amaya/
	public const BROWSER_LYNX = 'Lynx'; // http://en.wikipedia.org/wiki/Lynx
	public const BROWSER_SAFARI = 'Safari'; // http://apple.com
	public const BROWSER_CHROME = 'Chrome'; // http://www.google.com/chrome
	public const BROWSER_ANDROID = 'Android'; // http://www.android.com/
	public const BROWSER_GOOGLEBOT = 'GoogleBot'; // http://en.wikipedia.org/wiki/Googlebot
	public const BROWSER_SLURP = 'Yahoo! Slurp'; // http://en.wikipedia.org/wiki/Yahoo!_Slurp
	public const BROWSER_W3CVALIDATOR = 'W3C Validator'; // http://validator.w3.org/
	public const BROWSER_BLACKBERRY = 'BlackBerry'; // http://www.blackberry.com/
	public const BROWSER_ICECAT = 'IceCat'; // http://en.wikipedia.org/wiki/GNU_IceCat
	public const BROWSER_NOKIA_S60 = 'Nokia S60 OSS Browser'; // http://en.wikipedia.org/wiki/Web_Browser_for_S60
	public const BROWSER_NOKIA = 'Nokia Browser'; // * all other WAP-based browsers on the Nokia Platform
	public const BROWSER_MSN = 'MSN Browser'; // http://explorer.msn.com/
	public const BROWSER_MSNBOT = 'MSN Bot'; // http://search.msn.com/msnbot.htm
	public const BROWSER_BINGBOT = 'Bing Bot'; // http://en.wikipedia.org/wiki/Bingbot
	public const BROWSER_PALEMOON = 'Pale Moon'; // https://www.palemoon.org/
	public const BROWSER_MAXTHON = 'Maxthon'; // http://maxthon.com/
	public const BROWSER_FFFOCUS = 'Firefox Focus'; // https://www.mozilla.org/en-US/firefox/focus/
	public const BROWSER_YANDEX = 'Yandex Browser'; // https://browser.yandex.com/
	public const BROWSER_SILK = 'Silk';
	public const BROWSER_SAMSUNG_INET = 'Samsung Internet'; // http://www.samsung.com/global/galaxy/apps/samsung-internet/

	public const BROWSER_NETSCAPE_NAVIGATOR = 'Netscape Navigator'; // http://browser.netscape.com/ (DEPRECATED)
	public const BROWSER_GALEON = 'Galeon'; // http://galeon.sourceforge.net/ (DEPRECATED)
	public const BROWSER_NETPOSITIVE = 'NetPositive'; // http://en.wikipedia.org/wiki/NetPositive (DEPRECATED)
	public const BROWSER_PHOENIX = 'Phoenix'; // http://en.wikipedia.org/wiki/History_of_Mozilla_Firefox (DEPRECATED)

	public const PLATFORM_UNKNOWN = 'Unknown Platform';
	public const PLATFORM_WINDOWS = 'Windows';
	public const PLATFORM_WINPHONE = 'Windows Phone';
	public const PLATFORM_WINDOWS_CE = 'Windows CE';
	public const PLATFORM_OSX = 'Mac OSX';
	public const PLATFORM_LINUX = 'Linux';
	public const PLATFORM_IOS = 'iOS';
	public const PLATFORM_BLACKBERRY = 'BlackBerry';
	public const PLATFORM_NOKIA = 'Nokia';
	public const PLATFORM_FREEBSD = 'FreeBSD';
	public const PLATFORM_OPENBSD = 'OpenBSD';
	public const PLATFORM_NETBSD = 'NetBSD';
	public const PLATFORM_ANDROID = 'Android';
	public const PLATFORM_CHROMEOS = 'Chrome OS';
	public const PLATFORM_KINDLE = 'Amazon Kindle';

	public const OPERATING_SYSTEM_UNKNOWN = 'unknown';

	public function __construct($userAgent = null) {
		$this->reset();
		if (!empty($userAgent)){
			$this->setUserAgent($userAgent);
		}
		else $this->determine();
	}

	/**
	 * Reset all properties
	 */
	public function reset():void {
		$this->_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$this->_browserName = self::BROWSER_UNKNOWN;
		$this->_version = self::VERSION_UNKNOWN;
		$this->_platform = self::PLATFORM_UNKNOWN;
		$this->_isMobile = false;
		$this->_isTablet = false;
		$this->_isRobot = false;
		$this->_isFacebook = false;
	}

	/**
	 * Check to see if the specific browser is valid
	 *
	 * @param string $browserName
	 *
	 * @return bool True if the browser is the specified browser
	 */
	public function isBrowser($browserName):bool {
		return strcasecmp($this->_browserName, CoreUtils::trim($browserName)) === 0;
	}

	/**
	 * The name of the browser.  All return types are from the class contants
	 *
	 * @return string Name of the browser
	 */
	public function getBrowser():string {
		return $this->_browserName;
	}

	/**
	 * Set the name of the browser
	 *
	 * @param $browser string The name of the Browser
	 */
	public function setBrowser($browser):void {
		$this->_browserName = $browser;
	}

	/**
	 * The name of the platform.  All return types are from the class contants
	 *
	 * @return string Name of the browser
	 */
	public function getPlatform():string {
		return $this->_platform;
	}

	/**
	 * Set the name of the platform
	 *
	 * @param string $platform The name of the Platform
	 */
	public function setPlatform($platform):void {
		$this->_platform = $platform;
	}

	/**
	 * The version of the browser.
	 *
	 * @return string Version of the browser (will only contain alpha-numeric characters and a period)
	 */
	public function getVersion():string {
		return $this->_version;
	}

	/**
	 * Set the version of the browser
	 *
	 * @param string $version The version of the Browser
	 */
	public function setVersion($version):void {
		$this->_version = preg_replace('/[^0-9,.,a-z,A-Z-]/', '', $version);
	}

	/**
	 * Is the browser from a mobile device?
	 *
	 * @return boolean True if the browser is from a mobile device otherwise false
	 */
	public function isMobile():bool {
		return $this->_isMobile;
	}

	/**
	 * Is the browser from a tablet device?
	 *
	 * @return boolean True if the browser is from a tablet device otherwise false
	 */
	public function isTablet():bool {
		return $this->_isTablet;
	}

	/**
	 * Is the browser from a robot (ex Slurp,GoogleBot)?
	 *
	 * @return boolean True if the browser is from a robot otherwise false
	 */
	public function isRobot():bool {
		return $this->_isRobot;
	}

	/**
	 * Is the browser from facebook?
	 *
	 * @return boolean True if the browser is from facebook otherwise false
	 */
	public function isFacebook():bool {
		return $this->_isFacebook;
	}

	/**
	 * Set the Browser to be mobile
	 *
	 * @param boolean $value is the browser a mobile browser or not
	 */
	protected function setMobile($value = true):void {
		$this->_isMobile = $value;
	}

	/**
	 * Set the Browser to be tablet
	 *
	 * @param boolean $value is the browser a tablet browser or not
	 */
	protected function setTablet($value = true):void {
		$this->_isTablet = $value;
	}

	/**
	 * Set the Browser to be a robot
	 *
	 * @param boolean $value is the browser a robot or not
	 */
	protected function setRobot($value = true):void {
		$this->_isRobot = $value;
	}

	/**
	 * Set the Browser to be a Facebook request
	 *
	 * @param boolean $value is the browser a robot or not
	 */
	protected function setFacebook($value = true):void {
		$this->_isFacebook = $value;
	}

	/**
	 * Get the user agent value in use to determine the browser
	 *
	 * @return string The user agent from the HTTP header
	 */
	public function getUserAgent():string {
		return $this->_agent;
	}

	/**
	 * Set the user agent value (the construction will use the HTTP header value - this will overwrite it)
	 *
	 * @param string $agent_string The value for the User Agent
	 */
	public function setUserAgent($agent_string):void {
		$this->reset();
		$this->_agent = $agent_string;
		$this->determine();
	}

	/**
	 * Used to determine if the browser is actually "chromeframe"
	 *
	 * @since 1.7
	 * @return boolean True if the browser is using chromeframe
	 */
	public function isChromeFrame():bool {
		return (strpos($this->_agent, 'chromeframe') !== false);
	}

	/**
	 * Returns a formatted string with a summary of the details of the browser.
	 *
	 * @return string formatted string with a summary of the browser
	 */
	public function __toString() {
		return "<strong>Browser Name:</strong> {$this->getBrowser()}<br/>\n".
			"<strong>Browser Version:</strong> {$this->getVersion()}<br/>\n".
			"<strong>Browser User Agent String:</strong> {$this->getUserAgent()}<br/>\n".
			"<strong>Platform:</strong> {$this->getPlatform()}<br/>";
	}

	/**
	 * Protected routine to calculate and determine what the browser is in use (including platform)
	 */
	protected function determine():void {
		$this->checkPlatform();
		$this->checkBrowsers();
	}

	/**
	 * Protected routine to determine the browser type
	 *
	 * @return boolean True if the browser was detected otherwise false
	 */
	protected function checkBrowsers():bool {
		return (
			// well-known, well-used
			// Special Notes:
			// (1) Opera must be checked before FireFox due to the odd
			//     user agents used in some older versions of Opera
			// (2) WebTV is strapped onto Internet Explorer so we must
			//     check for WebTV before IE
			// (3) (deprecated) Galeon is based on Firefox and needs to be
			//     tested before Firefox is tested
			// (4) OmniWeb is based on Safari so OmniWeb check must occur
			//     before Safari
			// (5) Netscape 9+ is based on Firefox so Netscape checks
			//     before FireFox are necessary
			// (6) Edge must be checkd before Chrome due of usage of
			//     same Chrome user agent
			$this->checkBrowserWebTv() ||
			$this->checkBrowserEdge() ||
			$this->checkBrowserInternetExplorer() ||
			$this->checkBrowserVivaldi() ||
			$this->checkBrowserSamsungInternet() ||
			$this->checkBrowserOpera() ||
			$this->checkBrowserGaleon() ||
			$this->checkBrowserNetscapeNavigator9Plus() ||
			$this->checkBrowserPaleMoon() ||
			$this->checkBrowserFirefoxFocus() ||
			$this->checkBrowserFirefox() ||
			$this->checkBrowserMaxthon() ||
			$this->checkBrowserYandex() ||
			$this->checkBrowserSilk() ||
			$this->checkBrowserChrome() ||
			$this->checkBrowserOmniWeb() ||
			$this->checkBrowserSafari() ||

			// common mobile
			$this->checkBrowserAndroid() ||
			$this->checkBrowserAppleMobile() ||
			$this->checkBrowserBlackBerry() ||
			$this->checkBrowserNokia() ||

			// common bots
			$this->checkBrowserGoogleBot() ||
			$this->checkBrowserMSNBot() ||
			$this->checkBrowserBingBot() ||
			$this->checkBrowserSlurp() ||

			// check for facebook external hit when loading URL
			$this->checkFacebookExternalHit() ||

			// everyone else
			$this->checkBrowserNetPositive() ||
			$this->checkBrowserFirebird() ||
			$this->checkBrowserKonqueror() ||
			$this->checkBrowserIcab() ||
			$this->checkBrowserPhoenix() ||
			$this->checkBrowserAmaya() ||
			$this->checkBrowserLynx() ||
			$this->checkBrowserShiretoko() ||
			$this->checkBrowserIceCat() ||
			$this->checkBrowserIceweasel() ||
			$this->checkBrowserW3CValidator() ||
			$this->checkBrowserMozilla() /* Mozilla is such an open standard that you must check it last */
		);
	}

	/**
	 * Determine if the user is using a BlackBerry (last updated 1.7)
	 *
	 * @return boolean True if the browser is the BlackBerry browser otherwise false
	 */
	protected function checkBrowserBlackBerry():bool {
		if (stripos($this->_agent, 'blackberry') !== false){
			$aresult = explode('/', stristr($this->_agent, 'BlackBerry'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion($aversion[0]);
				$this->_browserName = self::BROWSER_BLACKBERRY;
				$this->setMobile();

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is the GoogleBot or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is the GoogletBot otherwise false
	 */
	protected function checkBrowserGoogleBot():bool {
		if (stripos($this->_agent, 'googlebot') !== false){
			$aresult = explode('/', stristr($this->_agent, 'googlebot'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion(str_replace(';', '', $aversion[0]));
				$this->_browserName = self::BROWSER_GOOGLEBOT;
				$this->setRobot();

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is the MSNBot or not (last updated 1.9)
	 *
	 * @return boolean True if the browser is the MSNBot otherwise false
	 */
	protected function checkBrowserMSNBot():bool {
		if (stripos($this->_agent, 'msnbot') !== false){
			$aresult = explode('/', stristr($this->_agent, 'msnbot'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion(str_replace(';', '', $aversion[0]));
				$this->_browserName = self::BROWSER_MSNBOT;
				$this->setRobot();

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is the BingBot or not (last updated 1.9)
	 *
	 * @return boolean True if the browser is the BingBot otherwise false
	 */
	protected function checkBrowserBingBot():bool {
		if (stripos($this->_agent, 'bingbot') !== false){
			$aresult = explode('/', stristr($this->_agent, 'bingbot'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion(str_replace(';', '', $aversion[0]));
				$this->_browserName = self::BROWSER_BINGBOT;
				$this->setRobot();

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is the W3C Validator or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is the W3C Validator otherwise false
	 */
	protected function checkBrowserW3CValidator():bool {
		if (stripos($this->_agent, 'W3C-checklink') !== false){
			$aresult = explode('/', stristr($this->_agent, 'W3C-checklink'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion($aversion[0]);
				$this->_browserName = self::BROWSER_W3CVALIDATOR;

				return true;
			}
		}
		else if (stripos($this->_agent, 'W3C_Validator') !== false){
			// Some of the Validator versions do not delineate w/ a slash - add it back in
			$ua = str_replace('W3C_Validator ', 'W3C_Validator/', $this->_agent);
			$aresult = explode('/', stristr($ua, 'W3C_Validator'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion($aversion[0]);
				$this->_browserName = self::BROWSER_W3CVALIDATOR;

				return true;
			}
		}
		else if (stripos($this->_agent, 'W3C-mobileOK') !== false){
			$this->_browserName = self::BROWSER_W3CVALIDATOR;
			$this->setMobile();

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is the Yahoo! Slurp Robot or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is the Yahoo! Slurp Robot otherwise false
	 */
	protected function checkBrowserSlurp():bool {
		if (stripos($this->_agent, 'slurp') !== false){
			$aresult = explode('/', stristr($this->_agent, 'Slurp'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion($aversion[0]);
				$this->_browserName = self::BROWSER_SLURP;
				$this->setRobot();
				$this->setMobile(false);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Internet Explorer or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Internet Explorer otherwise false
	 */
	protected function checkBrowserInternetExplorer():bool {
		// Test for IE11
		if (stripos($this->_agent, 'Trident/7.0;') !== false && stripos($this->_agent, 'rv:11.0;')){
			if (stripos($this->_agent, 'IEMobile') !== false){
				$this->setPlatform(self::PLATFORM_WINPHONE);
				$this->setBrowser(self::BROWSER_IEMOBILE);
				$this->setMobile();
			}
			else $this->setBrowser(self::BROWSER_IE);
			$this->setVersion('11.0');

			return true;
		}
		// Test for v1 - v1.5 IE
		if (stripos($this->_agent, 'microsoft internet explorer') !== false){
			$this->setBrowser(self::BROWSER_IE);
			$this->setVersion('1.0');
			$aresult = strstr($this->_agent, '/');
			if (preg_match('/308|425|426|474|0b1/i', $aresult)){
				$this->setVersion('1.5');
			}

			return true;
		}
		// Test for versions > 1.5
		if (stripos($this->_agent, 'msie') !== false && stripos($this->_agent, 'opera') === false){
			// See if the browser is the odd MSN Explorer
			if (stripos($this->_agent, 'msnb') !== false){
				$aresult = explode(' ', stristr(str_replace(';', '; ', $this->_agent), 'MSN'));
				if (isset($aresult[1])){
					$this->setBrowser(self::BROWSER_MSN);
					$this->setVersion(str_replace(['(', ')', ';'], '', $aresult[1]));

					return true;
				}
			}
			/** @noinspection SuspiciousAssignmentsInspection */
			$aresult = explode(' ', stristr(str_replace(';', '; ', $this->_agent), 'msie'));
			if (isset($aresult[1])){
				$this->setBrowser(self::BROWSER_IE);
				$this->setVersion(str_replace(['(', ')', ';'], '', $aresult[1]));
				if (stripos($this->_agent, 'IEMobile') !== false){
					$this->setBrowser(self::BROWSER_IEMOBILE);
					$this->setMobile();
				}

				return true;
			}
		} // Test for versions > IE 10
		else if (stripos($this->_agent, 'trident') !== false){
			$this->setBrowser(self::BROWSER_IE);
			$result = explode('rv:', $this->_agent);
			if (isset($result[1])){
				$this->setVersion(preg_replace('/[^0-9.]+/', '', $result[1]));
				$this->_agent = str_replace(['Mozilla', 'Gecko'], 'MSIE', $this->_agent);
			}
		} // Test for Pocket IE
		else if (($mspie = stripos($this->_agent, 'mspie') !== false) || stripos($this->_agent, 'pocket') !== false){
			$aresult = explode(' ', stristr($this->_agent, 'mspie'));
			if (isset($aresult[1])){
				$this->setPlatform(self::PLATFORM_WINDOWS_CE);
				$this->setBrowser(self::BROWSER_IEMOBILE);
				$this->setMobile();

				if ($mspie){
					$this->setVersion($aresult[1]);
				}
				else {
					$aversion = explode('/', $this->_agent);
					if (isset($aversion[1])){
						$this->setVersion($aversion[1]);
					}
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Opera or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Opera otherwise false
	 */
	protected function checkBrowserOpera():bool {
		if (stripos($this->_agent, 'opera mini') !== false){
			$resultant = stristr($this->_agent, 'opera mini');
			if (preg_match('/\//', $resultant)){
				$aresult = explode('/', $resultant);
				if (isset($aresult[1])){
					$aversion = explode(' ', $aresult[1]);
					$this->setVersion($aversion[0]);
				}
			}
			else {
				$aversion = explode(' ', stristr($resultant, 'opera mini'));
				if (isset($aversion[1])){
					$this->setVersion($aversion[1]);
				}
			}
			$this->_browserName = self::BROWSER_OPERA_MINI;
			$this->setMobile();

			return true;
		}
		if (stripos($this->_agent, 'opera') !== false){
			$resultant = stristr($this->_agent, 'opera');
			if (preg_match('/Version\/(1*.*)$/', $resultant, $matches)){
				$this->setVersion($matches[1]);
			}
			else if (preg_match('/\//', $resultant)){
				$aresult = explode('/', str_replace('(', ' ', $resultant));
				if (isset($aresult[1])){
					$aversion = explode(' ', $aresult[1]);
					$this->setVersion($aversion[0]);
				}
			}
			else {
				$aversion = explode(' ', stristr($resultant, 'opera'));
				$this->setVersion($aversion[1] ?? '');
			}
			if (stripos($this->_agent, 'Opera Mobi') !== false){
				$this->setMobile();
			}
			$this->_browserName = self::BROWSER_OPERA;

			return true;
		}
		if (stripos($this->_agent, 'OPR') !== false && stripos($this->_agent, 'Chrome') === false){
			$resultant = stristr($this->_agent, 'OPR');
			if (preg_match('/\//', $resultant)){
				$aresult = explode('/', str_replace('(', ' ', $resultant));
				if (isset($aresult[1])){
					$aversion = explode(' ', $aresult[1]);
					$this->setVersion($aversion[0]);
				}
			}
			if (stripos($this->_agent, 'Mobile') !== false){
				$this->setMobile();
			}
			$this->_browserName = self::BROWSER_OPERA;

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Chrome or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Chrome otherwise false
	 */
	protected function checkBrowserChrome():bool {
		if (stripos($this->_agent, 'Chrome') !== false){
			$aresult = explode('/', stristr($this->_agent, 'Chrome'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion($aversion[0]);
				$this->setBrowser(self::BROWSER_CHROME);
				//Chrome on Android
				if (stripos($this->_agent, 'Android') !== false){
					if (stripos($this->_agent, 'Mobile') !== false){
						$this->setMobile();
					}
					else {
						$this->setTablet();
					}
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Mathon or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Mathon otherwise false
	 */
	protected function checkBrowserMaxthon():bool {
		if (preg_match('~\bMaxthon\/([\d.]+)~', $this->_agent, $match)){
			$this->setVersion($match[1]);
			$this->setBrowser(self::BROWSER_MAXTHON);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Yandex Browser or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Yandex Browser otherwise false
	 */
	protected function checkBrowserYandex():bool {
		if (preg_match('~\bYaBrowser\/([\d.]+)~', $this->_agent, $match)){
			$this->setVersion($match[1]);
			$this->setBrowser(self::BROWSER_YANDEX);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Silk or not
	 *
	 * @return boolean True if the browser is Silk otherwise false
	 */
	protected function checkBrowserSilk():bool {
		if (preg_match('~\bSilk\/([\d.]+)~', $this->_agent, $match)){
			$this->setVersion($match[1]);
			$this->setBrowser(self::BROWSER_SILK);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is WebTv or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is WebTv otherwise false
	 */
	protected function checkBrowserWebTv():bool {
		if (stripos($this->_agent, 'webtv') !== false){
			$aresult = explode('/', stristr($this->_agent, 'webtv'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion($aversion[0]);
				$this->setBrowser(self::BROWSER_WEBTV);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is NetPositive or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is NetPositive otherwise false
	 */
	protected function checkBrowserNetPositive():bool {
		if (stripos($this->_agent, 'NetPositive') !== false){
			$aresult = explode('/', stristr($this->_agent, 'NetPositive'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion(str_replace(['(', ')', ';'], '', $aversion[0]));
				$this->setBrowser(self::BROWSER_NETPOSITIVE);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Galeon or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Galeon otherwise false
	 */
	protected function checkBrowserGaleon():bool {
		if (stripos($this->_agent, 'galeon') !== false){
			$aresult = explode(' ', stristr($this->_agent, 'galeon'));
			$aversion = explode('/', $aresult[0]);
			if (isset($aversion[1])){
				$this->setVersion($aversion[1]);
				$this->setBrowser(self::BROWSER_GALEON);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Konqueror or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Konqueror otherwise false
	 */
	protected function checkBrowserKonqueror():bool {
		if (stripos($this->_agent, 'Konqueror') !== false){
			$aresult = explode(' ', stristr($this->_agent, 'Konqueror'));
			$aversion = explode('/', $aresult[0]);
			if (isset($aversion[1])){
				$this->setVersion($aversion[1]);
				$this->setBrowser(self::BROWSER_KONQUEROR);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is iCab or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is iCab otherwise false
	 */
	protected function checkBrowserIcab():bool {
		if (stripos($this->_agent, 'icab') !== false){
			$aversion = explode(' ', stristr(str_replace('/', ' ', $this->_agent), 'icab'));
			if (isset($aversion[1])){
				$this->setVersion($aversion[1]);
				$this->setBrowser(self::BROWSER_ICAB);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is OmniWeb or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is OmniWeb otherwise false
	 */
	protected function checkBrowserOmniWeb():bool {
		if (stripos($this->_agent, 'omniweb') !== false){
			$aresult = explode('/', stristr($this->_agent, 'omniweb'));
			$aversion = explode(' ', $aresult[1] ?? '');
			$this->setVersion($aversion[0]);
			$this->setBrowser(self::BROWSER_OMNIWEB);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Phoenix or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Phoenix otherwise false
	 */
	protected function checkBrowserPhoenix():bool {
		if (stripos($this->_agent, 'Phoenix') !== false){
			$aversion = explode('/', stristr($this->_agent, 'Phoenix'));
			if (isset($aversion[1])){
				$this->setVersion($aversion[1]);
				$this->setBrowser(self::BROWSER_PHOENIX);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Firebird or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Firebird otherwise false
	 */
	protected function checkBrowserFirebird():bool {
		if (stripos($this->_agent, 'Firebird') !== false){
			$aversion = explode('/', stristr($this->_agent, 'Firebird'));
			if (isset($aversion[1])){
				$this->setVersion($aversion[1]);
				$this->setBrowser(self::BROWSER_FIREBIRD);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Netscape Navigator 9+ or not (last updated 1.7)
	 * NOTE: (http://browser.netscape.com/ - Official support ended on March 1st, 2008)
	 *
	 * @return boolean True if the browser is Netscape Navigator 9+ otherwise false
	 */
	protected function checkBrowserNetscapeNavigator9Plus():bool {
		if (stripos($this->_agent, 'Firefox') !== false && preg_match('/Navigator\/([^ ]*)/i', $this->_agent, $matches)){
			$this->setVersion($matches[1]);
			$this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);

			return true;
		}
		if (stripos($this->_agent, 'Firefox') === false && preg_match('/Netscape6?\/([^ ]*)/i', $this->_agent, $matches)){
			$this->setVersion($matches[1]);
			$this->setBrowser(self::BROWSER_NETSCAPE_NAVIGATOR);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Shiretoko or not (https://wiki.mozilla.org/Projects/shiretoko) (last updated 1.7)
	 *
	 * @return boolean True if the browser is Shiretoko otherwise false
	 */
	protected function checkBrowserShiretoko():bool {
		if (stripos($this->_agent, 'Mozilla') !== false && preg_match('/Shiretoko\/([^ ]*)/i', $this->_agent, $matches)){
			$this->setVersion($matches[1]);
			$this->setBrowser(self::BROWSER_SHIRETOKO);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Ice Cat or not (http://en.wikipedia.org/wiki/GNU_IceCat) (last updated 1.7)
	 *
	 * @return boolean True if the browser is Ice Cat otherwise false
	 */
	protected function checkBrowserIceCat():bool {
		if (stripos($this->_agent, 'Mozilla') !== false && preg_match('/IceCat\/([^ ]*)/i', $this->_agent, $matches)){
			$this->setVersion($matches[1]);
			$this->setBrowser(self::BROWSER_ICECAT);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Nokia or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Nokia otherwise false
	 */
	protected function checkBrowserNokia():bool {
		if (preg_match("/Nokia([^\/]+)\/([^ SP]+)/i", $this->_agent, $matches)){
			$this->setVersion($matches[2]);
			if (stripos($this->_agent, 'Series60') !== false || strpos($this->_agent, 'S60') !== false){
				$this->setBrowser(self::BROWSER_NOKIA_S60);
			}
			else {
				$this->setBrowser(self::BROWSER_NOKIA);
			}
			$this->setMobile();

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Firefox or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Firefox otherwise false
	 */
	protected function checkBrowserFirefox():bool {
		if (stripos($this->_agent, 'safari') === false){
			if (preg_match("/Firefox[\/ \(]([^ ;\)]+)/i", $this->_agent, $matches)){
				$this->setVersion($matches[1]);
				$this->setBrowser(self::BROWSER_FIREFOX);
				//Firefox on Android
				if (stripos($this->_agent, 'Android') !== false){
					if (stripos($this->_agent, 'Mobile') !== false){
						$this->setMobile();
					}
					else {
						$this->setTablet();
					}
				}

				return true;
			}
			if (preg_match('/Firefox$/i', $this->_agent, $matches)){
				$this->setVersion('');
				$this->setBrowser(self::BROWSER_FIREFOX);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Firefox Focus or not
	 *
	 * @return boolean True if the browser is Firefox Focus otherwise false
	 */
	protected function checkBrowserFirefoxFocus():bool {
		if (preg_match("~\bFocus\/([\d.]+)~", $this->_agent, $matches)){
			$this->setVersion($matches[1]);
			$this->setBrowser(self::BROWSER_FFFOCUS);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Pale Moon
	 *
	 * @return boolean True if the browser is Pale Moon otherwise false
	 */
	protected function checkBrowserPaleMoon():bool {
		if (preg_match("~PaleMoon/([\d.]+)~", $this->_agent, $matches)){
			$this->setVersion($matches[1]);
			$this->setBrowser(self::BROWSER_PALEMOON);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Firefox or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Firefox otherwise false
	 */
	protected function checkBrowserIceweasel():bool {
		if (stripos($this->_agent, 'Iceweasel') !== false){
			$aresult = explode('/', stristr($this->_agent, 'Iceweasel'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion($aversion[0]);
				$this->setBrowser(self::BROWSER_ICEWEASEL);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Mozilla or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Mozilla otherwise false
	 */
	protected function checkBrowserMozilla():bool {
		if (stripos($this->_agent, 'mozilla') !== false){
			if (preg_match('/rv:\d.\d[a-b]?/i', $this->_agent) && stripos($this->_agent, 'netscape') === false){
				$aversion = explode(' ', stristr($this->_agent, 'rv:'));
				preg_match('/rv:\d.\d[a-b]?/i', $this->_agent, $aversion);
				$this->setVersion(str_replace('rv:', '', $aversion[0]));
				$this->setBrowser(self::BROWSER_MOZILLA);

				return true;
			}
			if (preg_match('/rv:\d\.\d/i', $this->_agent) && stripos($this->_agent, 'netscape') === false){
				$aversion = explode('', stristr($this->_agent, 'rv:'));
				$this->setVersion(str_replace('rv:', '', $aversion[0]));
				$this->setBrowser(self::BROWSER_MOZILLA);

				return true;
			}
			if (preg_match('/mozilla\/([^ ]*)/i', $this->_agent, $matches) && stripos($this->_agent, 'netscape') === false){
				$this->setVersion($matches[1]);
				$this->setBrowser(self::BROWSER_MOZILLA);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Lynx or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Lynx otherwise false
	 */
	protected function checkBrowserLynx():bool {
		if (stripos($this->_agent, 'lynx') !== false){
			$aresult = explode('/', stristr($this->_agent, 'Lynx'));
			$aversion = explode(' ', $aresult[1] ?? '');
			$this->setVersion($aversion[0]);
			$this->setBrowser(self::BROWSER_LYNX);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Amaya or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Amaya otherwise false
	 */
	protected function checkBrowserAmaya():bool {
		if (stripos($this->_agent, 'amaya') !== false){
			$aresult = explode('/', stristr($this->_agent, 'Amaya'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion($aversion[0]);
				$this->setBrowser(self::BROWSER_AMAYA);

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Safari or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Safari otherwise false
	 */
	protected function checkBrowserSafari():bool {
		if (stripos($this->_agent, 'Safari') !== false
			&& stripos($this->_agent, 'iPhone') === false
			&& stripos($this->_agent, 'iPod') === false
		){
			if (preg_match('~\bVersion\/([\d.]+)~', $this->_agent, $match)){
				$this->setVersion($match[1]);
			}
			else {
				$this->setVersion(self::VERSION_UNKNOWN);
			}
			$this->setBrowser(self::BROWSER_SAFARI);

			return true;
		}

		return false;
	}

	/**
	 * Detect if URL is loaded from FacebookExternalHit
	 *
	 * @return boolean True if it detects FacebookExternalHit otherwise false
	 */
	protected function checkFacebookExternalHit():bool {
		if (false !== stripos($this->_agent, 'FacebookExternalHit')){
			$this->setRobot();
			$this->setFacebook();

			return true;
		}

		return false;
	}

	/**
	 * Detect if URL is being loaded from internal Facebook browser
	 *
	 * @return boolean True if it detects internal Facebook browser otherwise false
	 */
	protected function checkForFacebookIos():bool {
		if (false !== stripos($this->_agent, 'FBIOS')){
			$this->setFacebook();

			return true;
		}

		return false;
	}

	/**
	 * Detect Version for the Safari browser on iOS devices
	 *
	 * @return boolean True if it detects the version correctly otherwise false
	 */
	protected function getSafariVersionOnIos():bool {
		$aresult = explode('/', stristr($this->_agent, 'Version'));
		if (isset($aresult[1])){
			$aversion = explode(' ', $aresult[1]);
			$this->setVersion($aversion[0]);

			return true;
		}

		return false;
	}

	/**
	 * Detect Version for the Chrome browser on iOS devices
	 *
	 * @return boolean True if it detects the version correctly otherwise false
	 */
	protected function getChromeVersionOnIos():bool {
		$aresult = explode('/', stristr($this->_agent, 'CriOS'));
		if (isset($aresult[1])){
			$aversion = explode(' ', $aresult[1]);
			$this->setVersion($aversion[0]);
			$this->setBrowser(self::BROWSER_CHROME);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is iPhone or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is iPhone otherwise false
	 */
	protected function checkBrowserAppleMobile():bool {
		$version = [];
		if (preg_match('~CriOS/([\d\.]+)~', $this->_agent, $version)){
			$this->setVersion($version[1]);
			$this->setBrowser(self::BROWSER_CHROME);

			return true;
		}
		if (preg_match('~Safari/([\d\.]+)~', $this->_agent, $version)){
			$this->setVersion($version[1]);
			$this->setBrowser(self::BROWSER_SAFARI);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Android or not (last updated 1.7)
	 *
	 * @return boolean True if the browser is Android otherwise false
	 */
	protected function checkBrowserAndroid():bool {
		if (stripos($this->_agent, 'Android') !== false){
			$aresult = explode(' ', stristr($this->_agent, 'Android'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion($aversion[0]);
			}
			else {
				$this->setVersion(self::VERSION_UNKNOWN);
			}
			if (stripos($this->_agent, 'Mobile') !== false){
				$this->setMobile();
			}
			else {
				$this->setTablet();
			}
			$this->setBrowser(self::BROWSER_ANDROID);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Edge or not (last updated 1.7)
	 * https://github.com/cbschuld/Browser.php/pull/43
	 *
	 * @return boolean True if the browser is Edge otherwise false
	 */
	protected function checkBrowserEdge():bool {
		if (stripos($this->_agent, 'Edge') !== false){
			$aresult = explode('/', stristr($this->_agent, 'Edge'));
			if (isset($aresult[1])){
				$aversion = explode(' ', $aresult[1]);
				$this->setVersion($aversion[0]);
				$this->setBrowser(self::BROWSER_EDGE);
				if (stripos($this->_agent, 'Android') !== false){
					if (stripos($this->_agent, 'Mobile') !== false){
						$this->setMobile();
					}
					else {
						$this->setTablet();
					}
				}

				return true;
			}
		}

		return false;
	}

	/**
	 * Determine if the browser is Vivaldi or not
	 *
	 * @return boolean True if the browser is Vivaldi otherwise false
	 */
	protected function checkBrowserVivaldi():bool {
		if (stripos($this->_agent, 'Vivaldi') !== false){
			$_match = [];
			if (preg_match('/Vivaldi\/([\d.]+)/', $this->_agent, $_match)){
				$this->setVersion($_match[1]);
			}
			else $this->setVersion(self::VERSION_UNKNOWN);
			$this->setBrowser(self::BROWSER_VIVALDI);

			return true;
		}

		return false;
	}

	/**
	 * Determine if the browser is Samsung Internet or not
	 *
	 * @return boolean True if the browser is Samsung Internet otherwise false
	 */
	protected function checkBrowserSamsungInternet():bool {
		if (preg_match('/SamsungBrowser\/([\d.]+)/', $this->_agent, $_match)){
			$this->setVersion($_match[1]);
			$this->setBrowser(self::BROWSER_SAMSUNG_INET);

			return true;
		}

		return false;
	}

	/**
	 * Determine the user's platform (last updated 1.7)
	 */
	protected function checkPlatform():void {
		if (preg_match('/Windows Phone/', $this->_agent)){
			$this->_platform = self::PLATFORM_WINPHONE;
		}
		else if (stripos($this->_agent, 'windows') !== false){
			$this->_platform = self::PLATFORM_WINDOWS;
		}
		else if (preg_match('/(iPod|iPad|iPhone)/', $this->_agent)){
			$this->_platform = self::PLATFORM_IOS;
		}
		else if (stripos($this->_agent, 'mac') !== false){
			$this->_platform = self::PLATFORM_OSX;
		}
		else if (stripos($this->_agent, 'android') !== false){
			if (stripos($this->_agent, 'KFFOWI') !== false){
				$this->_platform = self::PLATFORM_KINDLE;
			}
			else $this->_platform = self::PLATFORM_ANDROID;
		}
		else if (stripos($this->_agent, 'linux') !== false){
			$this->_platform = self::PLATFORM_LINUX;
		}
		else if (stripos($this->_agent, 'Nokia') !== false){
			$this->_platform = self::PLATFORM_NOKIA;
		}
		else if (stripos($this->_agent, 'BlackBerry') !== false){
			$this->_platform = self::PLATFORM_BLACKBERRY;
		}
		else if (stripos($this->_agent, 'FreeBSD') !== false){
			$this->_platform = self::PLATFORM_FREEBSD;
		}
		else if (stripos($this->_agent, 'OpenBSD') !== false){
			$this->_platform = self::PLATFORM_OPENBSD;
		}
		else if (stripos($this->_agent, 'NetBSD') !== false){
			$this->_platform = self::PLATFORM_NETBSD;
		}
		else if (stripos($this->_agent, 'CrOS') !== false){
			$this->_platform = self::PLATFORM_CHROMEOS;
		}
		else if (stripos($this->_agent, 'win') !== false){
			$this->_platform = self::PLATFORM_WINDOWS;
		}
	}
}

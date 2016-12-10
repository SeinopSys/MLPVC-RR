<?php

namespace App;

class URL {
	/**
	 * Makes an absolute URL HTTPS
	 *
	 * @param string $url
	 *
	 * @return string
	 */
	static function makeHttps($url){
		return (new RegExp('^(https?:)?//'))->replace('https://',$url);
	}
}

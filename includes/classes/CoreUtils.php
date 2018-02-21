<?php

namespace App;

use ActiveRecord\ConnectionManager;
use ActiveRecord\SQLBuilder;
use App\Models\Episode;
use App\Models\Event;
use App\Models\FailsafeUser;
use App\Models\UsefulLink;
use App\Models\User;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use ElephantIO\Engine\SocketIO\Version2X as SocketIOEngine;
use App\Exceptions\CURLRequestException;
use enshrined\svgSanitize\data\AllowedAttributes;
use enshrined\svgSanitize\data\AttributeInterface;
use enshrined\svgSanitize\data\TagInterface;
use enshrined\svgSanitize\Sanitizer;
use Monolog\Logger;

class CoreUtils {
	public const
		FIXPATH_EMPTY = '#';
	/**
	 * Forces an URL rewrite to the specified path
	 *
	 * @param string $fix_uri URL to forcibly redirect to
	 */
	public static function fixPath($fix_uri){
		$_split = explode('?', $_SERVER['REQUEST_URI'], 2);
		$path = $_split[0];
		$query = empty($_split[1]) ? '' : "?{$_split[1]}";

		$_split = explode('?', $fix_uri, 2);
		$fix_path = $_split[0];
		$fix_query = empty($_split[1]) ? '' : "?{$_split[1]}";

		if (empty($fix_query))
			$fix_query = $query;
		else {
			$query_assoc = self::queryStringAssoc($query);
			$fix_query_assoc = self::queryStringAssoc($fix_query);
			$merged = $query_assoc;
			foreach ($fix_query_assoc as $key => $item)
				$merged[$key] = $item;
			$fix_query_arr = [];
			foreach ($merged as $key => $item){
				if ($item === null || $item !== self::FIXPATH_EMPTY)
					$fix_query_arr[] = $key.(!empty($item)?'='.urlencode($item):'');
			}
			$fix_query = empty($fix_query_arr) ? '' : '?'.implode('&', $fix_query_arr);
		}
		if ($path !== $fix_path || $query !== $fix_query)
			HTTP::tempRedirect("$fix_path$fix_query");
	}

	/**
	 * Turn query string into an associative array
	 *
	 * @param string $query
	 *
	 * @return array
	 */
	public static function queryStringAssoc($query){
		$assoc = [];
		if (!empty($query))
			parse_str(ltrim($query, '?'), $assoc);
		return $assoc;
	}

	/**
	 * Apostrophe HTML encoding for attribute values
	 *
	 * @param string $str Input string
	 *
	 * @return string Encoded string
	 */
	public static function aposEncode(?string $str):string {
		return self::escapeHTML($str, ENT_QUOTES);
	}

	public static function escapeHTML(?string $html, $mask = null){
		$mask = $mask !== null ? $mask | ENT_HTML5 : ENT_HTML5;
		return htmlspecialchars($html, $mask);
	}

	// Possible notice types
	public static $NOTICE_TYPES = ['info', 'success', 'fail', 'warn', 'caution'];
	/**
	 * Renders the markup of an HTML notice
	 *
	 * @param string           $type   Notice type
	 * @param string           $title  If $text is specified: Notice title
	 *                                 If $text is null: Notice body
	 * @param string|null|true $text   Notice body
	 *                                 If there's no title, leave empty and use $title for body
	 *                                 If $center is null: Defines centering
	 * @param bool             $center Whether to center the contents of the notice
	 *
	 * @return string
	 */
	public static function notice($type, $title, $text = null, $center = false){
		if (!\in_array($type, self::$NOTICE_TYPES, true))
			throw new \RuntimeException("Invalid notice type $type");

		if (!\is_string($text)){
			if (\is_bool($text))
				$center = $text;
			$text = $title;
			$title = null;
		}

		$HTML = '';
		if (!empty($title))
			$HTML .= '<label>'.self::escapeHTML($title).'</label>';

		$textRows = preg_split("/(\r\n|\n|\r){2}/", $text);
		foreach ($textRows as $row)
			$HTML .= '<p>'.self::trim($row).'</p>';

		if ($center)
			$type .= ' align-center';
		return "<div class='notice $type'>$HTML</div>";
	}

	/**
	 * Display a 404 page
	 */
	public static function notFound(){
		HTTP::statusCode(404);

		if (self::isJSONExpected())
			Response::fail('HTTP 404: '.(POST_REQUEST?'Endpoint':'Page')." ({$_SERVER['REQUEST_URI']}) does not exist");

		Users::authenticate();

		self::loadPage('ErrorController::notFound', [
			'title' => '404',
		]);
	}

	/**
	 * Display a 403 page
	 */
	public static function noPerm(){
		HTTP::statusCode(403);

		Users::authenticate();

		self::loadPage('ErrorController::noPerm', [
			'title' => '403',
		]);
	}

	/**
	 * Display a 400 page
	 */
	public static function badReq(){
		HTTP::statusCode(400);

		Users::authenticate();

		self::loadPage('ErrorController::badReq', [
			'title' => '400',
		]);
	}

	/**
	 * Page loading function
	 * ---------------------
	 * $options = array(
	 *     'title' => string,     - Page title
	 *     'no-robots' => bool,   - Disable crawlers (that respect meta tags)
	 *     'default-css' => bool, - Disable loading of default CSS files
	 *     'default-js' => bool,  - Disable loading of default JS files
	 *     'css' => array,        - Specify a an array of CSS files to load (true = autodetect)
	 *     'js' => array,         - Specify a an array of JS files to load (true = autodetect)
	 *     'view' => string,      - Which view file to open (defaults to $do)
	 *     'url' => string,       - A URL which will replace the one sent to the browser
	 * );
	 *
	 * @param string $view_name
	 * @param array  $options
	 *
	 * @throws \RuntimeException
	 */
	public static function loadPage(string $view_name, array $options = []){
		// Resolve view
		$view = new View($view_name);

		// SE crawling disable
		if (isset($options['no-robots']) && $options['no-robots'] === true)
			$norobots = true;

		// Set new URL option
		if (!empty($options['url']))
			$redirectto = $options['url'];

		# CSS
		$DEFAULT_CSS = ['theme'];
		$customCSS = [];
		// Only add defaults when needed
		if (!isset($options['default-css']) || $options['default-css'] === true)
			$customCSS = array_merge($customCSS, $DEFAULT_CSS);

		# JavaScript
		$DEFAULT_JS = [
			'datastore',
			'moment',
			'jquery.ba-throttle-debounce',
			'jquery.swipe',
			'jquery.simplemarquee',
			'shared-utils',
			'inert',
			'dialog',
			'global',
			'websocket',
		];
		$customJS = [];
		// Only add defaults when needed
		if (!isset($options['default-js']) || $options['default-js'] === true)
			$customJS = array_merge($customJS, $DEFAULT_JS);

		# Check assests
		self::_checkAssets($options, $customCSS, 'scss/min', 'css', $view);
		self::_checkAssets($options, $customJS, 'js/min', 'js', $view);

		# Import variables
		if (isset($options['import']) && \is_array($options['import'])){
			$scope = $options['import'];
			/** @noinspection ForeachSourceInspection */
			foreach ($scope as $k => $v)
				/** @noinspection IssetArgumentExistenceInspection */
				/** @noinspection UnSafeIsSetOverArrayInspection */
				if (!isset($$k))
					$$k = $v;
		}
		else $scope = [];

		// Page <title>
		if (isset($options['title']))
			$scope['title'] = $title = $options['title'];

		// Page heading
		if (isset($options['heading']))
			$scope['heading'] = $heading = $options['heading'];

		if (self::isJSONExpected()){
			HTTP::statusCode(400);
			$path = self::escapeHTML($_SERVER['REQUEST_URI']);
			Response::fail("The requested endpoint ($path) does not support JSON responses");
		}

		header('Content-Type: text/html; charset=utf-8;');
		require INCPATH.'views/_layout.php';
		die();
	}

	/**
	 * Render upcoming episode HTML
	 *
	 * @param bool $wrap Whether to output the wrapper elements
	 *
	 * @return string
	 */
	public static function getSidebarUpcoming($wrap = WRAP){
		global $PREFIX_REGEX;

		$HTML = [];
		/** @var $UpcomingEpisodes Episode[] */
		$UpcomingEpisodes = Episode::find('all', ['conditions' => "airs > NOW() AND airs < NOW() + INTERVAL '6 MONTH'", 'order' => 'airs asc']);
		$i = 0;
		if (!empty($UpcomingEpisodes)){
			foreach ($UpcomingEpisodes as $i => $Episode){
				$airtime = strtotime($Episode->airs);
				$month = date('M', $airtime);
				$day = date('j', $airtime);
				$time = self::_eventTimeTag($airtime, $i);

				$title = !$Episode->is_movie
					? $Episode->title
					: (
					$PREFIX_REGEX->match($Episode->title)
						? Episodes::shortenTitlePrefix($Episode->title)
						: "Movie: {$Episode->title}"
					);

				$type = $Episode->is_movie ? 'movie' : 'episode';
				$HTML[] = [
					$airtime, "<li><div class='calendar'><span class='top $type'>$month</span><span class='bottom'>$day</span></div>".
					"<div class='meta'><span class='title'><a href='{$Episode->toURL()}'>$title</a></span><span class='time'>Airs $time</span></div></li>"
				];
			}
		}
		else $i = 0;

		/** @var $UpcomingEvents Event[] */
		$UpcomingEvents = Event::upcoming();
		if (!empty($UpcomingEvents)){
			foreach ($UpcomingEvents as $j => $Event){
				$time = strtotime($Event->starts_at);
				$beforestartdate = time() < $time;
				if (!$beforestartdate){
					$time = strtotime($Event->ends_at);
				}
				$month = date('M', $time);
				$day = date('j', $time);
				$Verbs = $beforestartdate ? 'Starts' : 'Ends';
				$timetag = self::_eventTimeTag($time, $i+$j);

				$HTML[] = [
					$time, "<li><div class='calendar'><span class='top event'>$month</span><span class='bottom'>$day</span></div>".
					"<div class='meta'><span class='title'><a href='{$Event->toURL()}'>$Event->name</a></span><span class='time'>$Verbs $timetag</span></div></li>"
				];
			}
		}
		if (empty($HTML)){
			return '';
		}
		usort($HTML, function ($a, $b){
			return $a[0] <=> $b[0];
		});
		foreach ($HTML as $i => $v){
			$HTML[$i] = $v[1];
		}
		$HTML = implode('', $HTML);

		return $wrap ? "<section id='upcoming'><h2>Happening soon</h2><ul>$HTML</ul></section>" : $HTML;
	}

	private static function _eventTimeTag(int $timestamp, int $index):string {
		if ($index === 0){
			$diff = Time::difference(time(), $timestamp);
			if ($diff['time'] < Time::IN_SECONDS['month']){
				$ret = 'in ';
				$tz = '('.date('T', $timestamp).')';
				if (!empty($diff['day'])){
					$ret .= "{$diff['day']} day".($diff['day'] !== 1 ? 's' : '').' & ';
				}
				if (!empty($diff['hour'])){
					$ret .= "{$diff['hour']}:";
				}
				foreach (['minute', 'second'] as $k){
					$diff[$k] = self::pad($diff[$k]);
				}
				$timec = date('c', $timestamp);

				return "<time datetime='$timec' class='dynt nodt'>$ret{$diff['minute']}:{$diff['second']} $tz</time>";
			}
		}
		return Time::tag($timestamp);
	}

	/**
	 * Checks assets from loadPage()
	 *
	 * @param array    $options    Options array
	 * @param string[] $customType Array of partial file names
	 * @param string   $relpath    Relative file path without the leading slash
	 * @param string   $ext        The literal strings 'css' or 'js'
	 * @param View     $view       The view class that enables the true shortcut
	 *
	 * @throws \Exception
	 */
	private static function _checkAssets(array $options, &$customType, string $relpath, string $ext, View $view){
		if (isset($options[$ext])){
			if (!\is_array($options[$ext]))
				throw new \RuntimeException("\$options[$ext] must be an array");
			$customType = array_merge($customType, $options[$ext]);
		}

		foreach ($customType as $i => &$item){
			if ($item === true)
				$item = "pages/{$view->name}";
			self::_formatFilePath($item, $relpath, $ext);
		}
	}

	public static function cachedAssetLink(string $fname, string $relpath, string $type):string {
		self::_formatFilePath($fname, $relpath, $type);
		return $fname;
	}

	/**
	 * Turns asset filenames into URLs & adds modification timestamp parameters
	 *
	 * @param string $item
	 * @param string $relpath
	 * @param string $type
	 *
	 * @return string
	 */
	private static function _formatFilePath(string &$item, string $relpath, string $type){
		$pathStart = APPATH.$relpath;
		$item .= ".$type";
		if (!file_exists("$pathStart/$item"))
			throw new \RuntimeException("File /$relpath/$item does not exist");
		$item = "/$relpath/$item?".filemtime("$pathStart/$item");
	}

	/**
	 * A wrapper around php's native str_pad with more fitting defaults
	 *
	 * @param mixed  $input
	 * @param int    $pad_length
	 * @param string $pad_string
	 * @param int    $pad_type
	 *
	 * @return string
	 */
	public static function pad($input, $pad_length = 2, $pad_string = '0', $pad_type = STR_PAD_LEFT){
		return str_pad((string) $input, $pad_length, $pad_string, $pad_type);
	}

	/**
	 * Capitalizes the first leter of a string
	 *
	 * @param string $str
	 * @param bool   $all
	 *
	 * @return string
	 */
	public static function capitalize($str, $all = false){
		if ($all) return preg_replace_callback(new RegExp('((?:^|\s)[a-z])(\w+\b)?','i'), function($match){
			return strtoupper($match[1]).strtolower($match[2]);
		}, $str);
		else return mb_strlen($str) === 1 ? strtoupper($str) : strtoupper($str[0]).mb_substr($str,1);
	}

	// Turns a file size ini setting value into bytes
	private static function _shortSizeInBytes($size){
		$unit = mb_substr($size, -1);
		$value = \intval(mb_substr($size, 0, -1), 10);
		switch(strtoupper($unit)){
			case 'G':
				$value *= 1024;
			case 'M':
				$value *= 1024;
			case 'K':
				$value *= 1024;
			break;
		}
		return $value;
	}

	/**
	 * Returns the maximum uploadable file size in a readable format
	 *
	 * @param array $sizes For use in tests
	 *
	 * @return string
	 */
	public static function getMaxUploadSize($sizes = null){
		if ($sizes === null)
			$sizes = [ini_get('post_max_size'), ini_get('upload_max_filesize')];

		$workWith = $sizes[0];
		if ($sizes[1] !== $sizes[0]){
			$sizesBytes = array_map('self::_shortSizeInBytes', $sizes);
			if ($sizesBytes[1] < $sizesBytes[0])
				$workWith = $sizes[1];
		}

		return preg_replace(new RegExp('^(\d+)([GMk])$','i'), '$1 $2B', strtoupper($workWith));
	}

	/**
	 * Export PHP variables to JS through a script tag
	 *
	 * @param array $export Associative aray where keys are the desired JS variable names
	 * @throws \Exception
	 *
	 * @return string
	 */
	public static function exportVars(array $export):string {
		if (empty($export))
			return '';
		/** @noinspection UnknownInspectionInspection */
		/** @noinspection ES6ConvertVarToLetConst */
		foreach ($export as $name => $value){
			if ($value instanceof RegExp)
				$export[$name] = $value->jsExport();
		}
		return '<aside class="datastore">'.self::escapeHTML(JSON::encode($export))."</aside>\n";
	}

	/**
	 * Sanitizes HTML that comes from user input
	 *
	 * @param string   $dirty_html        HTML coming from the user
	 * @param string[] $allowedTags       Additional allowed tags
	 * @param string[] $allowedAttributes Allowed tag attributes
	 *
	 * @return string Sanitized HTML code
	 */
	public static function sanitizeHtml(string $dirty_html, ?array $allowedTags = null, ?array $allowedAttributes = null){
		$config = \HTMLPurifier_Config::createDefault();
		$whitelist = ['strong', 'b', 'em', 'i'];
		if (!empty($allowedTags))
			$whitelist = array_merge($whitelist, $allowedTags);
		$config->set('HTML.AllowedElements', $whitelist);
		$config->set('HTML.AllowedAttributes', $allowedAttributes);
		$config->set('Core.EscapeInvalidTags', true);

		// Mapping old to new
		$def = $config->getHTMLDefinition();
		$def->info_tag_transform['b'] = new \HTMLPurifier_TagTransform_Simple('strong');
		$def->info_tag_transform['i'] = new \HTMLPurifier_TagTransform_Simple('em');

		$purifier = new \HTMLPurifier($config);
		return self::trim($purifier->purify($dirty_html), true);
	}

	public static function minifySvgData(string $svgdata){
		if (!file_exists(SVGO_BINARY))
			throw new \RuntimeException('svgo is required for SVG minification, please run `yarn install` to install all NPM dependencies');
		$tmp_path = FSPATH.'tmp/sanitize/'.self::sha256($svgdata).'.svg';
		self::createFoldersFor($tmp_path);
		File::put($tmp_path, $svgdata);

		exec(SVGO_BINARY." $tmp_path ".
			'--disable=removeUnknownsAndDefaults,removeUselessStrokeAndFill,convertPathData,convertTransform,cleanupNumericValues,mergePaths,convertShapeToPath '.
			'--enable=removeRasterImages,removeDimensions,cleanupIDs');
		$svgdata = File::get($tmp_path);
		self::deleteFile($tmp_path);
		return $svgdata;
	}

	/**
	 * Sanitizes SVG that comes from user input
	 *
	 * @param string $dirty_svg SVG data coming from the user
	 * @param bool   $minify
	 *
	 * @return string Sanitized SVG code
	 */
	public static function sanitizeSvg(string $dirty_svg, bool $minify = true){
		// Remove bogous HTML entities
		$dirty_svg = preg_replace(new RegExp('&ns_[a-z_]+;'), '', $dirty_svg);
		if ($minify)
			$dirty_svg = self::minifySvgData($dirty_svg);

		$sanitizer = new Sanitizer();
		$sanitizer->setAllowedTags(new class implements TagInterface {
			public static function getTags(){
				return [
		            'svg','circle','clippath','clipPath','defs','ellipse','filter','font','g','line',
		            'lineargradient','marker','mask','mpath','path','pattern','style',
		            'polygon','polyline','radialgradient','rect','stop','switch','use','view',

		            'feblend','fecolormatrix','fecomponenttransfer','fecomposite',
		            'feconvolvematrix','fediffuselighting','fedisplacementmap',
		            'feflood','fefunca','fefuncb','fefuncg','fefuncr','fegaussianblur',
		            'femerge','femergenode','femorphology','feoffset',
		            'fespecularlighting','fetile','feturbulence',
				];
			}
		});
		$sanitizer->setAllowedAttrs(new class implements AttributeInterface {
			public static function getAttributes(){
				/** @var $allowed array */
				$allowed = array_flip(AllowedAttributes::getAttributes());
				unset($allowed['color']);
				return array_keys($allowed);
			}
		});
		$sanitizer->removeRemoteReferences(true);
		$sanitized = $sanitizer->sanitize($dirty_svg);

		$unifier = new \DOMDocument('1.0', 'UTF-8');
		$unifier->loadXML($sanitized);
		// Make sure we add the default colors of paths to the file to make them replaceable (unless they have a class)
		$paths = $unifier->getElementsByTagName('path');
		foreach ($paths as $path){
			/** @var $path \DOMElement */
			$fillAttr = $path->getAttribute('fill');
			$classAttr = $path->getAttribute('class');
			if ($fillAttr === null && $classAttr === null)
				$path->setAttribute('fill','#000');
		}
		// Transform 1-stop linear gradients the same way Illustrator breaks them
		$linearGradients = $unifier->getElementsByTagName('linearGradient');
		foreach ($linearGradients as $grad){
			/** @var $grad \DOMElement */
			if ($grad->childNodes->length !== 1)
				continue;

			/** @var $stopColor \DOMElement */
			$stopColor = $grad->childNodes->item(0)->cloneNode();
			$stopColor->setAttribute('offset', 1 - $stopColor->getAttribute('offset'));
			$stopColor->setAttribute('stop-color', '#000');
			$grad->appendChild($stopColor);
		}
		$sanitized = $unifier->saveXML($unifier->documentElement, LIBXML_NOEMPTYTAG);

		return $sanitized;
	}

	public static function validateSvg(string $svg_data){
		self::conditionalUncompress($svg_data);
		if ($svg_data === false)
			return Input::ERROR_INVALID;

		$parser = new \DOMDocument('1.0', 'UTF-8');
		libxml_use_internal_errors(true);
		$parser->loadXML($svg_data);
		libxml_use_internal_errors();
		if ($parser->documentElement === null || strtolower($parser->documentElement->nodeName) !== 'svg')
			return Input::ERROR_INVALID;
		unset($parser);

		return Input::ERROR_NONE;
	}

	/**
	 * Analyzes a file path and creates the folder structure necessary to sucessfully store it
	 *
	 * @param string $path Path to analyze
	 *
	 * @return bool Whether the folder was sucessfully created
	 */
	public static function createFoldersFor(string $path):bool {
		$folder = \dirname($path);
		return !is_dir($folder) ? mkdir($folder,FOLDER_PERM,true) : true;
	}

	/**
	 * Formats a 1-dimensional array of stings naturally
	 *
	 * @param string[] $list
	 * @param string   $append
	 * @param string   $separator
	 * @param bool     $noescape  Set to true to prevent character escaping
	 *
	 * @return string
	 */
	public static function arrayToNaturalString(array $list, string $append = 'and', string $separator = ',', $noescape = false):string {
		if (\is_string($list)) $list = explode($separator, $list);

		if (\count($list) > 1){
			$list_str = $list;
			array_splice($list_str, \count($list_str)-1,0,$append);
			$i = 0;
			$maxDest = \count($list_str)-3;
			while ($i < $maxDest){
				if ($i === \count($list_str)-1)
					continue;
				$list_str[$i] .= ',';
				$i++;
			}
			$list_str = implode(' ',$list_str);
		}
		else $list_str = $list[0];
		if (!$noescape)
			$list_str = self::escapeHTML($list_str);
		return $list_str;
	}

	/**
	 * Checks validity of a string based on regex
	 *  and responds if invalid chars are found
	 *
	 * @param string $string      The value bein checked
	 * @param string $Thing       Human-readable name for $string
	 * @param string $pattern     An inverse pattern that matches INVALID characters
	 * @param bool   $returnError If true retursn the error message instead of responding
	 *
	 * @return null|string
	 */
	public static function checkStringValidity($string, $Thing, $pattern, $returnError = false){
		if (preg_match_all(new RegExp($pattern,'u'), $string, $fails)){
			/** @var $fails string[][] */
			$invalid = [];
			foreach ($fails[0] as $f)
				if (!\in_array($f, $invalid, true)){
					switch ($f){
						case "\n":
							$invalid[] = '\n';
						break;
						case "\r":
							$invalid[] = '\r';
						break;
						case "\t":
							$invalid[] = '\t';
						break;
						default:
							$invalid[] = $f;
					}
				}

			$count = \count($invalid);
			$s = $count!==1?'s':'';
			$the_following = $count!==1?'the following':'an';
			$Error = "$Thing (".self::escapeHTML($string).") contains $the_following invalid character$s: ".self::arrayToNaturalString($invalid);
			if ($returnError)
				return $Error;
			Response::fail($Error);
		}
	}

	/**
	 * Returns text HTML of the website's footer
	 *
	 * @param bool $with_git_info
	 *
	 * @return string
	 */
	public static function getFooter($with_git_info = false){
		$out = [];
		if ($with_git_info)
			$out[] = self::getFooterGitInfo(false);
		$out[] = "<a class='issues' href='".GITHUB_URL."/issues' target='_blank' rel='noopener'>Known issues</a>";
		$out[] = '<a class="send-feedback">Send feedback</a>';
		return implode(' | ',$out);
	}

	/**
	 * Returns the HTML of the GIT informaiiton in the website's footer
	 *
	 * @param bool $appendSeparator
	 *
	 * @return string
	 */
	public static function getFooterGitInfo(bool $appendSeparator = true):string {
		$commit_info = "Running <strong><a href='".GITHUB_URL."' title='Visit the GitHub repository'>MLPVC-RR</a>";
		$commit_id = rtrim(shell_exec('git rev-parse --short=4 HEAD'));
		if (!empty($commit_id)){
			$commit_time = Time::tag(date('c',strtotime(shell_exec('git log -1 --date=short --pretty=format:%ci'))));
			$commit_info .= "@<a href='".GITHUB_URL."/commit/$commit_id' title='See exactly what was changed and why'>$commit_id</a></strong> created $commit_time";
		}
		else $commit_info .= '</strong> (version information unavailable)';
		if ($appendSeparator)
			$commit_info .= ' | ';
		return $commit_info;
	}

	/**
	 * Contains the HTML of the navigation element
	 * @var string
	 */
	public static $NavHTML;

	/**
	 * Returns the HTML code of the main navigation in the header
	 *
	 * @param bool  $disabled
	 *
	 * @return string
	 */
	public static function getNavigationHTML($disabled = false){
		if (!empty(self::$NavHTML))
			return self::$NavHTML;

		// Navigation items
		if (!$disabled){
			$NavItems = [
				['/episode/latest', 'Latest episode'],
				['/show', 'Show'],
				['/cg', 'Color Guide'],
				['/events', 'Events'],
			];
			if (Auth::$signed_in)
				$NavItems[] = [Auth::$user->toURL(), 'Account'];
			if (Permission::sufficient('staff')){
				$NavItems[] = ['/users', 'Users'];
				$NavItems[] = ['/admin', 'Admin'];
			}
			$NavItems[] = ['/about', 'About'];
		}
		else $NavItems = [];

		self::$NavHTML = '';
		foreach ($NavItems as $item)
			self::$NavHTML .= "<li><a href='{$item[0]}'>{$item[1]}</a></li>";
		self::$NavHTML .= '<li><a href="http://mlp-vectorclub.deviantart.com/" target="_blank" rel="noopener">MLP-VectorClub</a></li>';
		return self::$NavHTML;
	}

	/**
	 * Returns the HTML code of the secondary breadcrumbs navigation
	 *
	 * @param bool  $disabled
	 * @param array $scope    Contains the variables passed to the current page
	 * @param View  $view     Contains the view object that the current page was resolved by
	 *
	 * @return string
	 */
	public static function getBreadcrumbsHTML($disabled = false, array $scope = [], ?View $view = null):string {
		$breadcrumb = '';
		// Navigation items
		if (!$disabled){
			if ($view === null)
				return '';

			try {
				$breadcrumb = $view->getBreadcrumb($scope) ?? '';
			}
			catch(\TypeError $e){
				$breadcrumb = '';
			}
		}
		else $breadcrumb = (new NavBreadcrumb('HTTP 503'))->setChild(new NavBreadcrumb('Service Temporarily Unavailable'));

		return (string)$breadcrumb;
	}

	/**
	 * Renders the "Useful links" section of the sidebar
	 */
	public static function renderSidebarUsefulLinks(){
		if (!Auth::$signed_in) return;
		$Links = UsefulLink::in_order();
		if (empty($Links))
			return;

		$Render = [];
		foreach ($Links as $l){
			if (Permission::insufficient($l->minrole))
				continue;

			$Render[] = $l->getLi();
		}
		echo '<ul class="links">'.implode('',$Render).'</ul>';
	}

	/**
	 * Renders the "Useful links" section of the sidebar
	 *
	 * @param bool $wrap
	 *
	 * @return string
	 */
	public static function getSidebarUsefulLinksListHTML($wrap = WRAP){
		$HTML = '';
		$UsefulLinks = UsefulLink::in_order();
		foreach ($UsefulLinks as $l){
			$href = "href='".self::aposEncode($l->url)."'";
			if ($l->url[0] === '#')
				$href .= " class='action--".mb_substr($l->url,1)."'";
			$title = self::aposEncode($l->title);
			$label = htmlspecialchars_decode($l->label);
			$cansee = Permission::ROLES_ASSOC[$l->minrole];
			if ($l->minrole !== 'developer')
				$cansee = self::makePlural($cansee).' and above';
			$HTML .= <<<HTML
<li id='ufl-{$l->id}'>
	<div><a $href title='$title'>{$label}</a></div>
	<div><span class='typcn typcn-eye'></span> $cansee</div>
	<div class='buttons'>
		<button class='blue typcn typcn-pencil edit-link'>Edit</button><button class='red typcn typcn-trash delete-link'>Delete</button>
	</div>
</li>
HTML;
		}
		return $wrap ? "<ol>$HTML</ol>" : $HTML;
	}

	/**
	 * Adds possessive 's at the end of a word
	 *
	 * @param string $w
	 * @param bool   $sOnly
	 *
	 * @return string
	 */
	public static function posess($w, bool $sOnly = false){
		$s = "'".(mb_substr($w, -1) !== 's'?'s':'');
		if ($sOnly)
			return $s;
		return $w.$s;
	}

	/**
	 * Appends 's' to the end of string if input is not 1
	 *
	 * @param string $w    Text to pluralize
	 * @param float  $in   Number to base pluralization off of
	 * @param bool   $prep Prepend number to text
	 *
	 * @return string
	 */
	public static function makePlural($w, float $in = 0, $prep = false):string {
		$ret = ($prep?"$in ":'');
		if ($in !== 1.0 && $w[-1] === 'y' && !\in_array(strtolower($w),self::$_endsWithYButStillPlural,true))
			return $ret.mb_substr($w,0,-1).'ies';
		return $ret.$w.($in !== 1.0 && !\in_array(strtolower($w),self::$_uncountableWords,true) ?'s':'');
	}

	/**
	 * Tries to convert the specified word to a singular - currently by removing the S
	 * A more robust solution should be added for this if need be
	 *
	 * @param string $w
	 *
	 * @return string
	 */
	public static function makeSingular(string $w):string {
		return preg_replace(new RegExp('s$'),'',$w);
	}

	private static $_uncountableWords = ['staff'];
	private static $_endsWithYButStillPlural = ['day'];

	/**
	 * Detect user's web browser based on user agent
	 *
	 * @param string|null $user_agent User-Agent string to check
	 *
	 * @return array
	 */
	public static function detectBrowser($user_agent = null){
		$Return = ['user_agent' => !empty($user_agent) ? $user_agent : ($_SERVER['HTTP_USER_AGENT'] ?? '')];
		$browser = new Browser($Return['user_agent']);
		$name = $browser->getBrowser();
		if ($name !== Browser::BROWSER_UNKNOWN){
			$Return['browser_name'] = $name;

			$ver = $browser->getVersion();
			if ($ver !== Browser::VERSION_UNKNOWN)
				$Return['browser_ver'] = $ver;
		}
		$Return['platform'] = $browser->getPlatform();
		return $Return;
	}

	// Converts a browser name to it's equivalent class name
	public static function browserNameToClass($BrowserName){
		return preg_replace(new RegExp('[^a-z]'),'',strtolower($BrowserName));
	}

	/**
	 * Trims a string while truncating consecutive spaces
	 *
	 * @param string $str
	 * @param string $chars
	 * @param bool   $multiline
	 *
	 * @return string
	 */
	public static function trim(string $str, bool $multiline = false, string $chars = " \t\n\r\0\x0B\xC2\xA0"):string {
		$out = preg_replace(new RegExp(' +'),' ',trim($str, $chars));
		if ($multiline)
			$out = preg_replace(new RegExp('(\r\n|\r)'),"\n",$out);

		return $out;
	}

	/**
	 * Averages the numbers inside an array
	 *
	 * @param int[] $numbers
	 *
	 * @return float
	 */
	public static function average(array $numbers):float {
		return array_sum($numbers)/ \count($numbers);
	}

	/**
	 * Checks if a deviation is in the club
	 *
	 * @param int|string $DeviationID
	 *
	 * @return bool|int
	 */
	public static function isDeviationInClub($DeviationID){
		if (!\is_int($DeviationID))
			$DeviationID = \intval(mb_substr($DeviationID, 1), 36);

		try {
			$DiFiRequest = HTTP::legitimateRequest("http://deviantart.com/global/difi/?c[]=\"DeviationView\",\"getAllGroups\",[\"$DeviationID\"]&t=json");
		}
		catch (CURLRequestException $e){
			return $e->getCode();
		}
		if (empty($DiFiRequest['response']))
			return 1;

		$DiFiRequest = @JSON::decode($DiFiRequest['response'], JSON::AS_OBJECT);
		if (empty($DiFiRequest->DiFi->status))
			return 2;
		if ($DiFiRequest->DiFi->status !== 'SUCCESS')
			return 3;
		if (empty($DiFiRequest->DiFi->response->calls))
			return 4;
		if (empty($DiFiRequest->DiFi->response->calls[0]))
			return 5;
		if (empty($DiFiRequest->DiFi->response->calls[0]->response))
			return 6;
		if (empty($DiFiRequest->DiFi->response->calls[0]->response->status))
			return 7;
		if ($DiFiRequest->DiFi->response->calls[0]->response->status !== 'SUCCESS')
			return 8;
		if (empty($DiFiRequest->DiFi->response->calls[0]->response->content->html))
			return 9;

		$html = $DiFiRequest->DiFi->response->calls[0]->response->content->html;
		return strpos($html, 'gmi-groupname="MLP-VectorClub">') !== false;
	}

	/**
	 * Checks if a deviation is in the club and stops execution if it isn't
	 *
	 * @param string $favme
	 * @param bool   $throw If true an Exception will be thrown instead of responding
	 */
	public static function checkDeviationInClub($favme, $throw = false){
		$Status = self::isDeviationInClub($favme);
		if ($Status !== true){
			$errmsg = (
				$Status === false
				? 'The deviation has not been submitted to/accepted by the group yet'
				: "There was an issue while checking the acceptance status (Error code: $Status)"
			);
			if ($throw)
				throw new \RuntimeException($errmsg);
			Response::fail($errmsg);
		}
	}

	public static function getOverdueSubmissionList(){
		$Query = DB::$instance->query(
			'SELECT reserved_by, COUNT(*) as cnt FROM (
				SELECT reserved_by FROM reservations
				WHERE deviation_id IS NOT NULL AND lock = false
				UNION ALL
				SELECT reserved_by FROM requests
				WHERE deviation_id IS NOT NULL AND lock = false
			) t
			GROUP BY reserved_by
			HAVING COUNT(*) >= 5
			ORDER BY cnt DESC;');

		if (empty($Query))
			return;

		$HTML = '<table>';
		foreach ($Query as $row){
			$link = User::find($row['reserved_by'])->toAnchor(User::WITH_AVATAR);
			$r = min(round($row['cnt']/10*255),255);
			$count = "<strong style='color:rgb($r,0,0)'>{$row['cnt']}</strong>";

			$HTML .= "<tr><td>$link</td><td>$count</td></tr>";
		}
		return "$HTML</table>";
	}

	/**
	 * Cut a string to the specified length
	 *
	 * @param string $str
	 * @param int    $len
	 *
	 * @return string
	 */
	public static function cutoff(string $str, $len):string {
		$strlen = mb_strlen($str);
		return $strlen > $len ? self::trim(mb_substr($str, 0, $len-1)).'…' : $str;
	}

	public static function socketEvent(string $event, array $data = []){
		$elephant = new \ElephantIO\Client(new SocketIOEngine('https://ws.'.WS_SERVER_DOMAIN.':8667', [
			'context' => [
				'http' => [
					'header' => 'Cookie: access='.urlencode(WS_SERVER_KEY)
				],
				'ssl' => \defined('SOCKET_SSL_CTX') ? SOCKET_SSL_CTX : [],
			]
		]));

		$elephant->initialize();
		$elephant->emit($event, $data);
		$elephant->close();
	}

	public const VECTOR_APPS = [
		'' => "(don't show)",
		'illustrator' => 'Adobe Illustrator',
		'inkscape' => 'Inkscape',
		'ponyscape' => 'Ponyscape',
	];

	/**
	 * Universal method for setting keys/properties of arrays/objects
	 * @param mixed  $on
	 * @param string $key
	 * @param mixed  $value
	 */
	public static function set(&$on, $key, $value){
		if (\is_object($on))
			$on->{$key} = $value;
		else if (\is_array($on))
			$on[$key] = $value;
		else throw new \RuntimeException('$on is of invalid type ('.\gettype($on).')');
	}

	/**
	 * Checks if an image exists on the web
	 * Specify raw HTTP codes as integers to $onlyFail to only report failure on those codes
	 *
	 * @param string $url
	 * @param array  $onlyFails
	 *
	 * @return bool
	 */
	public static function isURLAvailable(string $url, array $onlyFails = []):bool{
		$ch = curl_init();
		curl_setopt_array($ch, [
			CURLOPT_URL => $url,
			CURLOPT_NOBODY => 1,
			CURLOPT_FAILONERROR => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FOLLOWLOCATION => true,
		]);
		$available = curl_exec($ch) !== false;
		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		if ($available === false && !empty($onlyFails))
			$available = !\in_array($responseCode, $onlyFails, false);
		curl_close($ch);

		return $available;
	}

	public static function msleep(int $ms){
		usleep($ms*1000);
	}

	public static function sha256(string $data):string {
		return hash('sha256', $data);
	}

	public static function makeUrlSafe(string $string):string{
		return self::trim(preg_replace(new RegExp('-+'),'-',preg_replace(new RegExp('[^A-Za-z\d\-]'),'-', $string)),false,'-');
	}

	/**
	 * @param string $table_name
	 *
	 * @return SQLBuilder
	 */
	public static function sqlBuilder(string $table_name){
		$conn = ConnectionManager::get_connection();
		return new SQLBuilder($conn, $table_name);
	}

	public static function execSqlBuilderArgs(SQLBuilder $builder):array {
		return [ $builder->to_s(), $builder->bind_values() ];
	}

	public static function elasticClient():Client {
		/** @var $elastiClient Client */
		static $elastiClient;
		if ($elastiClient !== null)
			return $elastiClient;

		$elastiClient = ClientBuilder::create()->setHosts(['127.0.0.1:9200'])->build();
		return $elastiClient;
	}

	public static function isJSONExpected():bool {
		// "Cache" the result for this request
		static $return_value;
		if ($return_value !== null)
			return $return_value;

		if (empty($_SERVER['HTTP_ACCEPT']))
		    $return_value = false;
		else {
			$htmlpos = stripos($_SERVER['HTTP_ACCEPT'], 'text/html');
			$jsonpos = stripos($_SERVER['HTTP_ACCEPT'], 'application/json');

			$return_value = $jsonpos !== false && ($htmlpos === false ? true : $jsonpos < $htmlpos);
		}

		return $return_value;
	}

	public static function detectUnexpectedJSON(){
		if (!self::isJSONExpected()){
			HTTP::statusCode(400);
			header('Content-Type: text/plain');
			die("This endpoint only serves JSON requests which your client isn't accepting");
		}
	}

	public static function gzread(string $path):string {
		$data = '';
		$file = gzopen($path, 'rb');
		while (!gzeof($file))
		    $data .= gzread($file, 4096);
		gzclose($file);
		return $data;
	}

	public const USELESS_NODE_NAMES = [
		'#text' => true,
		'br' => true,
	];

	public static function closestMeaningfulPreviousSibling(\DOMElement $e){
		do {
			$e = $e->previousSibling;
		}
		while ($e !== null && empty(self::trim($e->textContent)));
		return $e;
	}

	private static function _downloadHeaders(string $filename){
		header('Content-Type: application/octet-stream');
		header('Content-Transfer-Encoding: Binary');
		header("Content-disposition: attachment; filename=\"$filename\"");
	}

	public static function downloadAsFile(string $data, string $name){
		self::_downloadHeaders($name);
		echo $data;
		exit;
	}

	public static function downloadFile(string $path, ?string $dl_name = null){
		self::_downloadHeaders($dl_name ?? basename($path));
		readfile($path);
		exit;
	}

	/**
	 * Set theory equivalent: $initial ∖ $remove
	 * Only works with non-associative arrays
	 * I wouldn't rely on the order of the returned elements
	 *
	 * @param array $initial
	 * @param array $remove
	 *
	 * @return array The inital array with the elements present in both arrays removed
	 */
	public static function array_subtract(array $initial, array $remove):array {
		$initial = array_flip($initial);
		if ($initial === false)
			throw new \RuntimeException(__METHOD__.': $initial could not be flipped');
		/** @var $initial array */
		$remove = array_flip($remove);
		if ($remove === false)
			throw new \RuntimeException(__METHOD__.': $remove could not be flipped');
		/** @var $remove array */
		foreach ($initial as $el => $_){
			if (isset($remove[$el]))
				unset($initial[$el]);
		}
		return array_keys($initial);
	}

	public static function array_random(array $arr){
		return empty($arr) ? null : $arr[array_rand($arr, 1)];
	}

	/**
	 * Returns the file's modification timestamp or the current timestamp if it doesn't exist
	 *
	 * @param string $path
	 *
	 * @return int
	 */
	public static function filemtime(string $path):int {
		if (!file_exists($path))
			return time();

		$mt = filemtime($path);
		return $mt === false ? time() : $mt;
	}

	/**
	 * Deletes a file if it exists, stays silent otherwise
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public static function deleteFile(string $name):bool {
		if (!file_exists($name))
			return true;

		return unlink($name);
	}

	public static function conditionalUncompress(string &$data){
		if (0 === mb_strpos($data, "\x1f\x8b\x08", 0, 'US-ASCII'))
			$data = @gzdecode($data);
	}

	public static function stringSize(string $data):int {
		return mb_strlen($data, '8bit');
	}

	public static function error_log(string $message){
		global $logger;

		if (\defined('DISABLE_MONOLOG')){
			/** @noinspection ForgottenDebugOutputInspection */
			error_log($message);
			return;
		}

		/** @var $logger Logger */
		$logger->log(Logger::ERROR, $message);
	}

	public static function responseSmiley(string $face):string {
		return "<div class='align-center'><span class='sideways-smiley-face'>$face</span></div>";
	}

	public static function isURLSafe(string $url, &$matches = null):bool {
		global $REWRITE_REGEX;

		return mb_strlen($url) <= 256 && $REWRITE_REGEX->match(strtok($url,'?'), $matches);
	}

	public static function getSidebarLoggedIn():string {
		if (Auth::$signed_in)
			$av_wrap = Auth::$user->getAvatarWrap();
		else $av_wrap = (new FailsafeUser([
			'avatar_url' => GUEST_AVATAR
		]))->getAvatarWrap();
		$avprov = UserPrefs::get('p_avatarprov');
		$name = Auth::$signed_in?Auth::$user->toAnchor():'Curious Pony';
		$role = Auth::$signed_in?Auth::$user->role_label:'Guest';
		$sessup = Auth::$signed_in?Auth::$session->getUpdateIndicatorHTML():'';

		return <<<HTML
<div class='logged-in provider-$avprov'>
	$av_wrap
	<div class="user-data">
		<span class="user-name">$name</span>
		<span class="user-role">
			<span>$role</span>
			$sessup
		</span>
	</div>
</div>
HTML;
	}
}

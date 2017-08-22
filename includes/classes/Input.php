<?php

namespace App;

use App\Exceptions\MismatchedProviderException;
use App\Models\Episode;

class Input {
	private $_type, $_source, $_key, $_initValue, $_origValue, $_value, $_respond = true, $_validator, $_range, $_silentFail;
	private static $SUPPORTED_TYPES = [
		'exists' => true,
		'bool' => true,
		'int' => true,
		'vote' => true,
		'float' => true,
		'text' => true,
		'string' => true,
		'uuid' => true,
		'username' => true,
		'url' => true,
		'int[]' => true,
		'json' => true,
		'timestamp' => true,
		'epid' => true,
	];

	const
		IS_OPTIONAL = 'optional',
		SILENT_FAILURE = 'silent',
		CUSTOM_ERROR_MESSAGES = 'errors',
		THROW_EXCEPTIONS = 'throw',
		IN_RANGE = 'range',
		METHOD_GET = 'GET',
		ERROR_NONE = 0,
		ERROR_MISSING = 1,
		ERROR_INVALID = 2,
		ERROR_RANGE = 3;

	/**
	 * Creates a class instance based on the settings provided
	 * All options are optional and have default fallbacks
	 *
	 * $o = array(
	 *     // Prevents $ERROR_MISSING from being triggered
	 *     Input::$IS_OPTIONAL => bool,
	 *     // Throw exceptions instead of calling CoreUtils::Respond
	 *     Input::$THROW_EXCEPTIONS => bool,
	 *     // Range for length/size validation (choose one)
	 *     Input::$IN_RANGE => [int],        // input >= int
	 *     Input::$IN_RANGE => [int1, int2], // input >= $mix && input <= $max
	 *     Input::$IN_RANGE => [null, int],  // input <= int
	 *     // Custom error strings
	 *     Input::$CUSTOM_ERROR_MESSAGES => array(
	 *         Input::$ERROR_MISSING => string,
	 *         Input::$ERROR_INVALID => string,
	 *         Input::$ERROR_RANGE => string,
	 *         'custom' => string,
	 *     )
	 * )
	 *
	 * @param string                 $key
	 * @param string|RegExp|callable $type
	 * @param array                  $o
	 *
	 * @return Input
	 */
	public function __construct($key, $type, $o = null){
		if (isset($o[self::THROW_EXCEPTIONS]))
			$this->_respond = $o[self::THROW_EXCEPTIONS] === false;
		if ($type instanceof RegExp)
			$this->_validator = function($value) use ($type){
				return $type->match($value) ? self::ERROR_NONE : self::ERROR_INVALID;
			};
		else if (is_callable($type))
			$this->_validator = $type;
		else {
			/** @var $type string */
			if (empty(self::$SUPPORTED_TYPES[$type]))
				$this->_outputError('Validation failed: Input type is invalid');
		}
		$this->_type = $type;

		if (!is_string($key))
			$this->_outputError('Input key missing or invalid');
		$this->_key = $key;

		$this->_silentFail = isset($o[self::SILENT_FAILURE]) && $o[self::SILENT_FAILURE] === true;

		$this->_source = $SRC = isset($o[self::METHOD_GET]) && $o[self::METHOD_GET] === true ? '_GET' : '_POST';
		$_SRC = $GLOBALS[$SRC];
		if (!isset($_SRC[$key]) || CoreUtils::length($_SRC[$key]) === 0)
			$result = empty($o[self::IS_OPTIONAL]) ? self::ERROR_MISSING : self::ERROR_NONE;
		else {
			$this->_origValue = $this->_type === 'text' ? CoreUtils::trim($_SRC[$key], true) : CoreUtils::trim($_SRC[$key]);
			$this->_range = $o[self::IN_RANGE] ?? null;

			$result = $this->_validate();
		}
		if ($result !== self::ERROR_NONE)
			$this->_outputError(
				!empty($o[self::CUSTOM_ERROR_MESSAGES][$result])
				? $o[self::CUSTOM_ERROR_MESSAGES][$result]
				: "Error wile checking \${$SRC}['{$this->_key}'] (code $result)",
				$result
			);
	}

	/**
	 * Validates the input and returns an error code
	 *
	 * @return int
	 */
	private function _validate(){
		if ($this->_validator !== null){
			$call_params = [&$this->_origValue, $this->_range];
			$vaildation_result = call_user_func_array($this->_validator, $call_params);
			if ($vaildation_result !== null)
				return $vaildation_result;

			$this->_value = $this->_origValue;
			return self::ERROR_NONE;
		}
		switch ($this->_type){
			case 'bool':
				if (!in_array($this->_origValue, ['1', '0', 'true', 'false', 'on', 'off'], false))
					return self::ERROR_INVALID;
				$this->_origValue = in_array($this->_origValue, ['1', 'true', 'on'], false);
			break;
			case 'int':
			case 'vote':
			case 'float':
				if (!is_numeric($this->_origValue))
					return self::ERROR_INVALID;
				$this->_origValue = $this->_type === 'float'
					? (float) $this->_origValue
					: (int) $this->_origValue;
				if ($this->_type === 'vote' && $this->_origValue === 0)
					return self::ERROR_INVALID;
				if (self::checkNumberRange($this->_origValue, $this->_range, $code))
					return $code;
				$this->_origValue = $this->_origValue;
			break;
			case 'text':
			case 'string':
				if (!is_string($this->_origValue))
					return self::ERROR_INVALID;
				if (self::checkStringLength($this->_origValue, $this->_range, $code))
					return $code;
			break;
			case 'uuid':
				if (!is_string($this->_origValue) || !preg_match(new RegExp('^[a-f0-9]{8}\-[a-f0-9]{4}\-4[a-f0-9]{3}\-[89ab][a-f0-9]{3}\-[a-f0-9]{12}$','i'), $this->_origValue))
					return self::ERROR_INVALID;

				$this->_origValue = strtolower($this->_origValue);
			break;
			case 'username':
				global $USERNAME_REGEX;
				if (!is_string($this->_origValue) || !$USERNAME_REGEX->match($this->_origValue))
					return self::ERROR_INVALID;
			break;
			case 'url':
				if (!is_string($this->_origValue))
					return self::ERROR_INVALID;
				global $REWRITE_REGEX;
				if (stripos($this->_origValue, ABSPATH) === 0)
					$this->_origValue = CoreUtils::substring($this->_origValue, CoreUtils::length(ABSPATH)-1);
				if (!preg_match($REWRITE_REGEX,$this->_origValue) && !preg_match(new RegExp('^#[a-z\-]+$'),$this->_origValue)){
					if (self::checkStringLength($this->_origValue, $this->_range, $code))
						return $code;
					if (!preg_match(new RegExp('^https?://[a-z\d/.-]+(?:/[ -~]+)?$','i'), $this->_origValue))
						Response::fail('Link URL does not appear to be a valid link');
				}
			break;
			case 'int[]':
				if (!is_string($this->_origValue) || !preg_match(new RegExp('^\d{1,12}(?:,\d{1,12})*$'), $this->_origValue))
					return self::ERROR_INVALID;

				$this->_origValue = array_map('intval',explode(',',$this->_origValue));
			break;
			case 'json':
				try {
					$this->_origValue = JSON::decode($this->_origValue);
					if (empty($this->_origValue))
						throw new \RuntimeException(rtrim('Could not decode JSON; '.json_last_error(),'; '));
				}
				catch (\Throwable $e){
					error_log(__METHOD__.': '.$e->getMessage()."\n".$e->getTraceAsString());
					return self::ERROR_INVALID;
				}
			break;
			case 'timestamp':
				$this->_origValue = strtotime($this->_origValue);
				if ($this->_origValue === false)
					return self::ERROR_INVALID;
				if (self::checkNumberRange($this->_origValue, $this->_range, $code))
					return $code;
			break;
			case 'epid':
				$this->_origValue = Episode::parseID($this->_origValue);
				if (empty($this->_origValue))
					return self::ERROR_INVALID;
			break;
			case 'favme':
				try {
					try {
						$Image = new ImageProvider(CoreUtils::trim($this->_origValue), ImageProvider::PROV_DEVIATION);
						$this->_value = $Image->extra;
						return self::ERROR_NONE;
					}
					catch (MismatchedProviderException $e){
						Response::fail('The cutie mark vector must be on DeviantArt, '.$e->getActualProvider().' links are not allowed');
					}
					catch (\Exception $e){ Response::fail('Error while checking deviation link: '.$e->getMessage()); }
				}
				catch (\Throwable $e){
					error_log(__METHOD__.': '.$e->getMessage()."\n".$e->getTraceAsString());
					return self::ERROR_INVALID;
				}
			break;
		}

		$this->_value = $this->_origValue;
		return self::ERROR_NONE;
	}

	public static function checkStringLength($value, $range, &$code){
		$code = self::_numberInRange(CoreUtils::length($value), $range);
		return $code;
	}
	public static function checkNumberRange($value, $range, &$code = false){
		$result = self::_numberInRange($value, $range, $code);
		return $code === false ? $result === self::ERROR_RANGE : $result;
	}

	private static function _numberInRange($n, $range){
		$has_min = isset($range[0]);
		$has_max = isset($range[1]);
		if ($has_min || $has_max){
			if ($has_min ? $n < $range[0] : $n < 1)
				return self::ERROR_RANGE;
			if ($has_max && $n > $range[1])
				return self::ERROR_RANGE;
		}
		return self::ERROR_NONE;
	}

	private function _outputError($message, $errorCode = null){
		$message = str_replace('@value', CoreUtils::escapeHTML($this->_initValue), $message);
		if ($errorCode === self::ERROR_RANGE){
			if (isset($this->_range[0]))
				$message = str_replace('@min', $this->_range[0], $message);
			if (isset($this->_range[1]))
				$message = str_replace('@max', $this->_range[1], $message);
		}
		if ($this->_silentFail)
			return error_log("Silenced Input validation error: $message\nKey: $this->_key\nOptions: _source={$this->_source}, _origValue={$this->_origValue}, _respond={$this->_respond}, request_uri={$_SERVER['REQUEST_URI']}");
		if ($this->_respond)
			Response::fail($message);
		throw new \Exception($message);
	}

	public function out(){
		return $this->_value;
	}
}

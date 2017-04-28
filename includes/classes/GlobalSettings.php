<?php

namespace App;

class GlobalSettings {
	protected static
		$_db = 'global_settings',
		$_defaults = array(
			'reservation_rules' => '',
			'about_reservations' => '',
		);

	/**
	 * Gets a global cofiguration item's value
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	static function get(string $key, $default = false){
		global $Database;

		if (isset(static::$_defaults[$key]))
			$default = static::$_defaults[$key];
		$q = $Database->where('key', $key)->getOne(static::$_db,'value');
		return isset($q['value']) ? $q['value'] : $default;
	}

	/**
	 * Sets a global cofiguration item's value
	 *
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return bool
	 */
	static function set(string $key, $value):bool {
		global $Database;

		if (!isset(static::$_defaults[$key]))
			Response::fail("Key $key is not allowed");
		$default = static::$_defaults[$key];

		if ($Database->where('key', $key)->has(static::$_db)){
			$Database->where('key', $key);
			if ($value == $default)
				$Database->delete(static::$_db);
			else return $Database->update(static::$_db, array('value' => $value));
		}
		else if ($value != $default)
			return $Database->insert(static::$_db, array('key' => $key, 'value' => $value));
		else return true;
	}

	/**
	 * Processes a configuration item's new value
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	static function process(string $key){
		$value = CoreUtils::trim($_POST['value']);

		if ($value === '')
			return null;

		switch ($key){
			case "reservation_rules":
			case "about_reservations":
				$value = CoreUtils::sanitizeHtml($value, $key === 'reservation_rules'? array('li', 'ol') : array('p'));
			break;
		}

		return $value;
	}
}

<?php

namespace App;

class File {
	/**
	 * @param string $name
	 * @param mixed $data
	 *
	 * @return int|bool Number of bytes that written or false on failure
	 */
	public static function put(string $name, $data){
		$bytes = file_put_contents($name, $data);
		if ($bytes === false)
			return false;

		self::chmod($name);
		return $bytes;
	}

	/**
	 * @param string $name
	 *
	 * @return string|bool The read data or false on failure
	 */
	public static function get(string $name){
		return file_get_contents($name);
	}

	/**
	 * @param string $name
	 *
	 * @return bool True on success, false on failure
	 */
	public static function chmod(string $name):bool {
		$result = @chmod($name, 0770);
		if ($result === false)
			error_log(__METHOD__.': Fail for file '.$name);
		return $result;
	}
}

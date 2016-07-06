<?php

/**
 * This file is part of the Nette Framework (http://nette.org)
 * Copyright (c) 2004 David Grudl (http://davidgrudl.com)
 */

namespace Nette\Utils;

use Nette;


/**
 * Array tools library.
 *
 * @author     David Grudl
 */
class Arrays
{

	/**
	 * Static class - cannot be instantiated.
	 */
	final public function __construct()
	{
		throw new Nette\StaticClassException;
	}


	/**
	 * Returns item from array or $default if item is not set.
	 * @param  array
	 * @param  string|int|array one or more keys
	 * @param  mixed
	 * @return mixed
	 * @throws Nette\InvalidArgumentException if item does not exist and default value is not provided
	 */
	public static function get(array $arr, $key, $default = NULL)
	{
		foreach (is_array($key) ? $key : array($key) as $k) {
			if (is_array($arr) && array_key_exists($k, $arr)) {
				$arr = $arr[$k];
			} else {
				if (func_num_args() < 3) {
					throw new Nette\InvalidArgumentException("Missing item '$k'.");
				}
				return $default;
			}
		}
		return $arr;
	}


	/**
	 * Returns reference to array item or $default if item is not set.
	 * @param  array
	 * @param  string|int|array one or more keys
	 * @return mixed
	 * @throws Nette\InvalidArgumentException if traversed item is not an array
	 */
	public static function & getRef(& $arr, $key)
	{
		foreach (is_array($key) ? $key : array($key) as $k) {
			if (is_array($arr) || $arr === NULL) {
				$arr = & $arr[$k];
			} else {
				throw new Nette\InvalidArgumentException('Traversed item is not an array.');
			}
		}
		return $arr;
	}


	/**
	 * Recursively appends elements of remaining keys from the second array to the first.
	 * @return array
	 */
	public static function mergeTree($arr1, $arr2)
	{
		$res = $arr1 + $arr2;
		foreach (array_intersect_key($arr1, $arr2) as $k => $v) {
			if (is_array($v) && is_array($arr2[$k])) {
				$res[$k] = self::mergeTree($v, $arr2[$k]);
			}
		}
		return $res;
	}


	/**
	 * Searches the array for a given key and returns the offset if successful.
	 * @return int|FALSE offset if it is found, FALSE otherwise
	 */
	public static function searchKey($arr, $key)
	{
		$foo = array($key => NULL);
		return array_search(key($foo), array_keys($arr), TRUE);
	}


	/**
	 * Inserts new array before item specified by key.
	 * @return void
	 */
	public static function insertBefore(array & $arr, $key, array $inserted)
	{
		$offset = self::searchKey($arr, $key);
		$arr = array_slice($arr, 0, $offset, TRUE) + $inserted + array_slice($arr, $offset, count($arr), TRUE);
	}


	/**
	 * Inserts new array after item specified by key.
	 * @return void
	 */
	public static function insertAfter(array & $arr, $key, array $inserted)
	{
		$offset = self::searchKey($arr, $key);
		$offset = $offset === FALSE ? count($arr) : $offset + 1;
		$arr = array_slice($arr, 0, $offset, TRUE) + $inserted + array_slice($arr, $offset, count($arr), TRUE);
	}


	/**
	 * Renames key in array.
	 * @return void
	 */
	public static function renameKey(array & $arr, $oldKey, $newKey)
	{
		$offset = self::searchKey($arr, $oldKey);
		if ($offset !== FALSE) {
			$keys = array_keys($arr);
			$keys[$offset] = $newKey;
			$arr = array_combine($keys, $arr);
		}
	}


	/**
	 * Returns array entries that match the pattern.
	 * @return array
	 */
	public static function grep(array $arr, $pattern, $flags = 0)
	{
		set_error_handler(function($severity, $message) use ($pattern) { // preg_last_error does not return compile errors
			restore_error_handler();
			throw new RegexpException("$message in pattern: $pattern");
		});
		$res = preg_grep($pattern, $arr, $flags);
		restore_error_handler();
		if (preg_last_error()) { // run-time error
			throw new RegexpException(NULL, preg_last_error(), $pattern);
		}
		return $res;
	}


	/**
	 * Returns flattened array.
	 * @return array
	 */
	public static function flatten(array $arr)
	{
		$res = array();
		array_walk_recursive($arr, function($a) use (& $res) { $res[] = $a; });
		return $res;
	}

}

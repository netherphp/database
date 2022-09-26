<?php

namespace Nether;
use \Nether;

class Option {

	static $Storage = array();
	/*//
	@type array
	a singleton array for holding all the options for this instance.
	//*/

	static function Get($key) {
	/*//
	@argv string Key
	@argv array(string Key, ...)
	fetch a specific option or list of options.
	//*/

		// if requesting an array of keys, then return an array of values
		// indexed by their respective keys.
		if(is_array($key)) {
			$return = [];

			foreach($key as $k)
			$return[$k] = self::Get($k);

			return $return;
		}

		// else if only asking for one key return the direct value stored
		// in that key.
		else {
			if(array_key_exists($key,self::$Storage))
			return self::$Storage[$key];

			else
			return null;
		}

	}

	static function Define() {
	/*//
	@argv string Key, mixed Value
	@argv array(string Key => mixed Value, ...)
	store a value under a key, but only if it already has not been defined
	prior by something like the application config file.
	//*/

		$argv = func_get_args();
		if(!count($argv))
		throw new \Exception('expected [string,mixed] or [array(string=>mixed,...)]');

		if(is_array($argv[0])) {
			foreach($argv[0] as $key => $value) {
				if(!array_key_exists($key,self::$Storage))
				self::$Storage[$key] = $value;
			}
		} else {
			if(!array_key_exists($argv[0],self::$Storage))
			self::$Storage[$argv[0]] = $argv[1];
		}

		return;
	}

	static function Set() {
	/*//
	@argv string Key, mixed Value
	@argv array(string Key => mixed Value, ...)
	store a value under a key, overwriting any value that may already be
	there. if you wish for a config or something else to take presidence
	over your set call, you should use the Define method instead.
	//*/

		$argv = func_get_args();
		if(!count($argv))
		throw new \Exception('expected [string,mixed] or [array(string=>mixed,...)]');

		if(is_array($argv[0])) {
			foreach($argv[0] as $key => $value) {
				self::$Storage[$key] = $value;
			}
		} else {
			self::$Storage[$argv[0]] = $argv[1];
		}

		return;
	}

}

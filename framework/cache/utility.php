<?php
namespace Framework\Cache;
use Framework\{Base, Registry};

class Utility extends Base {
	/**
	 * Set Cache in Memcache
	 * @param string  $key      Name of the Key
	 * @param mixed  $value    The value to be stored
	 * @param integer $duration No of seconds for which the value should be stored
	 */
	public static function setCache($key, $value, $duration = 300) {
		$memCache = Registry::get("cache");
		return $memCache->set($key, $value, $duration);
	}

	/**
	 * Get Cache Value from the key
	 * @param  string $key Name of the key
	 * @return mixed      Corresponding value in the key
	 */
	public static function getCache($key, $default = null) {
		$memCache = Registry::get("cache");
		return $memCache->get($key, $default);
	}

	public static function smartCache($date, $resourceUid) {
		$cacheKey = sprintf("Date:%s_ID:%s", $date, $resourceUid);
		return static::getCache($cacheKey);
	}
}

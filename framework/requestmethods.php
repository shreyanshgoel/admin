<?php

/**
 * RequestMethods class is quite simple. It has methods for returning get/post/server variables, based on a key.
 * If that key is not present, the default value will be returned. We use these methods to return our posted form data to the controller.
 *
 * @author Faizan Ayubi
 */

namespace Framework {

    class RequestMethods {

        private function __construct() {
            // do nothing
        }
        
        private function __clone() {
            // do nothing
        }

        public static function getIp() {
        	return self::server('REMOTE_ADDR', '');
        }

        protected static function _clean($arr = []) {
            $result = [];
            foreach ($arr as $key => $value) {
            	$key = self::escape($key);
                if (is_array($value)) {
                    $result[$key] = self::_clean($value);
                } else {
                    $result[$key] = self::escape($value);
                }
            }
            return $result;
        }
        
        public static function get($key, $default = null) {
            if (isset($_GET[$key])) {
                if (is_array($_GET[$key])) {
                    return self::_clean($_GET[$key]);
                }
                return self::escape($_GET[$key]);
            }
            return $default;
        }

        public static function post($key, $default = null) {
            if (isset($_POST[$key])) {
                if (is_array($_POST[$key])) {
                    return self::_clean($_POST[$key]);
                }
                return self::escape($_POST[$key]);
            } return $default;
        }

        public static function server($key, $default = "") {
            if (isset($_SERVER[$key])) {
                return self::escape($_SERVER[$key]);
            } return $default;
        }

        public static function type() {
            return self::server('REQUEST_METHOD');
        }

        public static function escape($val) {
            if (is_array($val)) {
                return self::_escape($val);
            } else {
                return htmlspecialchars($val);
            }
        }

        public static function _escape($val) {
            $result = [];
            foreach ($val as $key => $value) {
                if (is_array($value)) {
                    $result[$key] = self::_escape($value);
                } else {
                    $result[$key] = htmlspecialchars($value);   
                }
            }
            return $result;
        }

        /**
         * This PATCH request will only parse the data sent to it in application/json form
         * @param  string $key     Key to be looked for in JSON data
         * @param  mixed $default Default value to be sent in case key is not found
         */
        public static function patch($key, $default = null) {
        	try {
        		$d = file_get_contents('php://input');
        		$data = json_decode($d, true);

        		if (isset($data[$key])) {
        			if (is_array($data[$key])) {
        				return self::_clean($data[$key]);
        			}
        			return self::escape($data[$key]);
        		}

        		return $default;
        	} catch (\Exception $e) {
        		return $default;
        	}
        }

        public static function put($key, $default = null) {
        	try {
        		$d = file_get_contents('php://input');
        		$data = json_decode($d, true);

        		if (isset($data[$key])) {
        			if (is_array($data[$key])) {
        				return self::_clean($data[$key]);
        			}
        			return self::escape($data[$key]);
        		}

        		return $default;
        	} catch (\Exception $e) {
        		return $default;
        	}
        }
    }
}

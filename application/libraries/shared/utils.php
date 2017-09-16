<?php
namespace Shared;

use WebBot\Core\Bot as Bot;
use Framework\{Registry, ArrayMethods, RequestMethods};

class Utils {
	/**
	 * Check whether the User is Accessing the webapp from a fixed IP
	 * @param string $ip Current IP of the connecting User
	 * @param  mixed $var Variable to be debuged
	 * @return boolean      if IP is debugging IP
	 */
	public static function debugMode($ip, $var = null) {
		if (in_array($ip, ['14.139.251.107', '14.141.173.170', '103.201.126.209'])) {
			if (!is_null($var)) {
				var_dump($var);	
			}
			return true;
		}
		return false;
	}

	/**
	 * Get Class of an object
	 * @param  object  $obj  Any object
	 * @param  boolean $full Whether full name is required (with namespace as prefix)
	 * @return string        Name of the class of the object
	 */
	public static function getClass($obj, $full = false) {
		$cl = get_class($obj);

		if (!$full) {
			$parts = explode("\\", $cl);
			$cl = array_pop($parts);
		}
		return $cl;
	}

	/**
	 * Converts the object to string by using 'sprintf'
	 * @param  mixed $field Can be any thing which is needed in string format
	 * @return string
	 */
	public static function getMongoID($field) {
		if (is_object($field)) {
			$id = sprintf('%s', $field);
		} else {
			$id = $field;
		}
		return $id;
	}

	/**
	 * Capture the output of var_dump in a string and return it
	 * !!!!!! Use CAREFULLY !!!!!!
	 * @param  mixed $var Variable to be debuged
	 * @return string
	 */
	public static function debugVar($var) {
		ob_start();
		var_dump($var);
		$result = ob_get_clean();
		return $result;
	}

	/**
	 * Set a message to Session that will only be displayed once
	 * @param  string $msg Message to display
	 * @return null
	 */
	public static function flashMsg($msg) {
		$session = Registry::get("session");
		$session->set('$flashMessage', $msg);
	}

	/**
	 * Converts the string to a valid BSON ObjectID of 24 characters or if $id -> string
	 * else if $id -> array recursively converts each id to bson objectId
	 * 
	 * @param  string|object|array $id ID to converted to bson type
	 * @return string|object|array (objects)     Returns an BSON ObjectID if $id is valid else empty string
	 */
	public static function mongoObjectId($id) {
		$result = "";
		try {
			if (is_array($id)) {
				$result = [];
				foreach ($id as $i) {
					$result[] = self::mongoObjectId($i);
				}
			} else if (!Services\Db::isType($id, 'id')) {
				if (strlen($id) === 24) {
					$result = new \MongoDB\BSON\ObjectID($id);	
				} else if (is_null($id)) {
					$result = null;
				} else {
					$result = "";
				}
	        } else {
	        	$result = $id;
	        }
		} catch (\Exception $e) {
			$result = "";
		}
        return $result;
	}

	/**
	 * Downloads the Image From the URL by checking its Content Type and matching against
	 * the valid image content types defined by the standard. Image is stored into
	 * the uploads directory
	 * @param  string $url URL of the image
	 * @return string|boolean      FALSE on failure else uploaded image name
	 */
	public static function downloadImage($url = null, $opts = []) {
		if (!$url) { return false; }
		try {
			$bot = new Bot(['image' => $url], ['logging' => false]);
			$bot->execute();
			$documents = $bot->getDocuments();
			$doc = array_shift($documents);

			$contentType = $doc->type;
			preg_match('/image\/(.*)/i', $contentType, $matches);
			if (!isset($matches[1])) {
				return false;
			} else {
				$extension = $matches[1];
			}

		} catch (\Exception $e) {
			return false;
		}
		
		$allowedExtension = $opts['extension'] ?? 'jpe?g|gif|bmp|png|tif';
		if (!preg_match('/^'.$allowedExtension.'$/', $extension)) {
			return false;
		}

		$path = APP_PATH . '/public/assets/uploads/images/';
		if (isset($opts['name'])) {
			$img = $opts['name'];
		} else {
			$img = uniqid() . ".{$extension}";
		}

		$str = @file_get_contents($url);
		if ($str === false) {
			return false;
		}
		$status = file_put_contents($path . $img, $str);
		if ($status === false) {
			return false;
		}
		return $img;
	}

	public static function particularFields($field) {
	    switch ($field) {
	        case 'name':
	            $type = 'text';
	            break;
	        
	        case 'password':
	            $type = 'password';
	            break;

	        case 'email':
	            $type = 'email';
	            break;

	        case 'phone':
	            $type = "text";
	            break;

	        default:
	            $type = 'text';
	            break;
	    }
	    return array("type" => $type);
	}

	public static function parseValidations($validations) {
	    $html = ''; $pattern = '';
	    foreach ($validations as $key => $value) {
	        preg_match("/(\w+)(\((\d+)\))?/", $value, $matches);
	        $type = isset($matches[1]) ? $matches[1] : 'none';
	        switch ($type) {
	            case 'required':
	                $html .= ' required="" ';
	                break;
	            
	            case 'max':
	                $html .= ' maxlength="' .$matches[3] . '" ';
	                break;

	            case 'min':
	                $pattern .= ' pattern="(.){' . $matches[3] . ',}" ';
	                break;
	        }
	    }
	    return array("html" => $html, "pattern" => $pattern);
	}

	public static function fetchCampaign($url) {
		$data = [];
    	
        try {
    		$bot = new Bot(['cloud' => $url], ['logging' => false]);
    	    $bot->execute();
    	    $documents = $bot->getDocuments();	// because only variables can be passed as reference
    	    $doc = array_shift($documents);

    	    $type = $doc->type;
    	    if (preg_match("/image/i", $type)) {
    	        $data["image"] = $data["url"] = $url;
    	        $data["description"] = $data["title"] = "";
    	        return $data;
    	    }
            $data["title"] = $doc->query("/html/head/title")->item(0)->nodeValue;
            $data["url"] = $url;
            $data["description"] = "";
            $data["image"] = "";

            $metas = $doc->query("/html/head/meta");
            for ($i = 0; $i < $metas->length; $i++) {
                $meta = $metas->item($i);
                
                if ($meta->getAttribute('name') == 'description') {
                    $data["description"] = $meta->getAttribute('content');
                }

                if ($meta->getAttribute('property') == 'og:image') {
                    $data["image"] = $meta->getAttribute('content');
                }
            }
        } catch (\Exception $e) {
            $data["url"] = $url;
            $data["image"] = $data["description"] = $data["title"] = "";
        }
        return $data;
	}

	/**
	 * @deprecated should not be used
	 * @param  boolean $numbers Whether numbers are required in the string
	 * @return string           [a-zA-Z0-9]+
	 */
	public static function randomPass($numbers = true) {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

		if (!$numbers) {
			$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		}
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass); //turn the array into a string
	}

	public static function urlRegex($url) {
		$regex = "((https?|ftp)\:\/\/)"; // SCHEME
        $regex .= "([a-z0-9+!*(),;?&=\$_.-]+(\:[a-z0-9+!*(),;?&=\$_.-]+)?@)?"; // User and Pass
        $regex .= "([a-z0-9-.]*)\.([a-z]{2,4})"; // Host or IP
        $regex .= "(\:[0-9]{2,5})?"; // Port
        $regex .= "(\/([a-z0-9+\$_-]\.?)+)*\/?"; // Path
        $regex .= "(\?[a-z+&\$_.-][a-z0-9;:@&%=+\/\$_.-]*)?"; // GET Query
        $regex .= "(#[a-z_.-][a-z0-9+\$_.-]*)?"; // Anchor

        $result = preg_match('/^'.$regex.'$/', $url);
        return (boolean) $result;
	}

	/**
	 * Converts dates to be passed for mongodb query
	 * @return array       mongodb start and end date
	 */
	public static function dateQuery($dateQ, $endDate = null) {
		if (!is_array($dateQ)) {
            $opts = ['start' => $dateQ, 'end' => $endDate];
        } else {
            $opts = $dateQ;
        }
        $org = Registry::get("session")->get("org");
        $startDt = new \DateTime(); $endDt = new \DateTime();

        if (is_object($org) && is_a($org, 'Organization')) {
            $tz = new \DateTimeZone($org->getZone());
            $startDt->setTimezone($tz);
            $endDt->setTimezone($tz);
        }

        $start = strtotime("-1 day"); $end = strtotime("+1 day");
        if (isset($opts['start'])) {
            $start = (int) strtotime($opts['start'] . ' 00:00:00'); // this returns in seconds
        }

        if (isset($opts['end'])) {
            $end = (int) strtotime($opts['end'] . ' 23:59:59');
        }

        $startDt->setTimestamp($start - $startDt->getOffset());
        $endDt->setTimestamp($end - $endDt->getOffset());

        $startTimeStamp = $startDt->getTimestamp() * 1000;
        $endTimeStamp = $endDt->getTimestamp() * 1000 + 999;

        return [
            'start' => new \MongoDB\BSON\UTCDateTime($startTimeStamp),
            'end' => new \MongoDB\BSON\UTCDateTime($endTimeStamp)
        ];
	}

	/**
	 * Convert an object to array recursively
	 * @param  object $object Object derived from simple class that can be used as array in foreach
	 * @return array         Final Array
	 */
	public static function toArray($object) {
		$arr = [];
		$obj = (array) $object;
		foreach ($obj as $key => $value) {
			if (Services\Db::isType($value, 'document')) {
				$arr[$key] = self::toArray($value);
			} else {
				$arr[$key] = $value;
			}
		}
		return $arr;
	}

	public static function mongoRegex($val) {
		return new \MongoDB\BSON\Regex($val, 'i');
	}

	public static function dateArray($arr) {
		$result = [];
		foreach ($arr as $key => $value) {
			$date = \Framework\StringMethods::only_date($key);
			$result[$date] = $value;
		}
		return $result;
	}

	/**
	 * Returns a string if the URL has parameters or NULL if not
	 * @return string
	 */
	public static function addURLParams($linkUrl, $string) {
		if (parse_url($linkUrl, PHP_URL_QUERY)) {
			$append = '&'.$string;
		} else {
			$append = '?'.$string;
		}
		return $append;
	}

	public static function getConfig($name, $property = null) {
		$config = Registry::get("configuration")->parse("configuration/{$name}");

		if ($property && property_exists($config, $property)) {
			return $config->$property;
		}
		return $config;
	}

	public static function getAppConfig() {
		return static::getConfig('app')->app;
	}

	/**
	 * Uploads the image sent by the user in $_FILES array when submitting
	 * the form using file-upload. Assigns a name to the file and also checks
	 * for a valid file extension based on the type (if provided)
	 * 
	 * @return string|boolean      FALSE on failure else uploaded image name
	 */
	public static function uploadImage($name, $type = "images", $opts = []) {
		if (!isset($opts['extension'])) {
			$opts['extension'] = 'jpe?g|gif|bmp|png|ico|tif';
		}
	    return static::_uploadFile($name, $type, $opts);
	}

	public static function uploadFileObj($file, $type, $opts) {
		$path = APP_PATH . "/public/assets/uploads/{$type}/";
		$extension = pathinfo($file["name"], PATHINFO_EXTENSION);

		$extensionRegex = $opts['extension'];
		if (!preg_match("/^".$extensionRegex."$/i", $extension)) {
		    return false;
		}

		if (isset($opts['name'])) {
		    $filename = $opts['name'];
		} else {
		    $filename = uniqid() . ".{$extension}";
		}

		if (move_uploaded_file($file["tmp_name"], $path . $filename)) {
		    return $filename;
		}
		return false;
	}

	protected static function _uploadFile($name, $type, $opts) {
		if (isset($_FILES[$name])) {
	        $file = $_FILES[$name];
	        return static::uploadFileObj($file, $type, $opts);
	    }
	    return false;
	}

	/**
	 * Download Video from a URL
	 * @param  string $url  Youtube URL
	 * @param  array  $opts Array of Options, Keys-> ('extension', 'quality')
	 * @return false|string       False on failure, string -> name of the newly created file
	 */
	public static function downloadVideo($url, $opts = []) {
		$folder = self::media('', 'show', ['type' => 'video']);
		$extension = $opts['extension'] ?? 'mp4';
		try {
			$ytdl = new \YTDownloader\Service\Download($url, [
				'path' => $folder
			]);
			$file = $ytdl->convert($extension, [
				'type' => 'video',
				'quality' => $opts['quality'] ?? '240p'
			]);

			$name = uniqid() . ".{$extension}";
			copy($folder . $file, $folder . $name);
			unlink($folder . $file);
		} catch (\Exception $e) {
			$name = false;
		}
		return $name;
	}

	/**
	 * Set Cache in Memcache
	 * @param string  $key      Name of the Key
	 * @param mixed  $value    The value to be stored
	 * @param integer $duration No of seconds for which the value should be stored
	 */
	public static function setCache($key, $value, $duration = 300) {
        /** @var \Framework\Cache\Driver\Memcached $memCache */
		$memCache = Registry::get("cache");
		return $memCache->set($key, $value, $duration);
	}

	/**
	 * Get Cache Value from the key
	 * @param  string $key Name of the key
	 * @return mixed      Corresponding value in the key
	 */
	public static function getCache($key, $default = null) {
	    /** @var \Framework\Cache\Driver\Memcached $memCache */
		$memCache = Registry::get("cache");
		return $memCache->get($key, $default);
	}

	public static function getSmartCache($date, $resourceUid) {
		$cacheKey = sprintf("Date:%s_ID:%s", $date, $resourceUid);
		return static::getCache($cacheKey);
	}

	public static function setSmartCache($date, $resourceUid, $resource) {
		$cacheKey = sprintf("Date:%s_ID:%s", $date, $resourceUid);
		static::setCache($cacheKey, $resource, 86400);
	}

	/**
	 * Group an array of objects with a key
	 * @param  array $objArr  Array of objects
	 * @param  string $groupBy Group By Key
	 * @return array          Modified Array
	 */
	public static function groupBy($objArr, $groupBy) {
		$result = [];
		try {
			foreach ($objArr as $key => $value) {
				if (!is_object($value)) {
					continue;
				}

				$newKey = $value->$groupBy ?? 'Empty';
				if (!isset($result[$newKey])) {
					$result[$newKey] = [];
				}
				$result[$newKey][] = $value;
			}
		} catch (\Exception $e) {
			// log the exception
		}

		return $result;
	}

	public static function uploadFile($name, $folder = "files", $opts = []) {
		if (!isset($opts['extension'])) {
			$opts['extension'] = 'tar|zip|pdf|csv';
		}
		return static::_uploadFile($name, $folder, $opts);
	}

	/**
	 * Perform Various Media Related functions
	 * @param  string $name Name of the file - local, remote
	 * @param  string $task Name of the task to be performed on the file
	 * @param  array  $opts Keys -> ('extension', 'type', 'quality')
	 * @return mixed       Return type depending on the action performed
	 */
	public static function media($name, $task = 'show', $opts = []) {
		$type = ($opts['type']) ?? 'image';
		$folder = APP_PATH . "/public/assets/uploads/{$type}s/";
		switch ($task) {
			case 'remove':
				if ($type === 'image' && $name === \Ad::NO_IMAGE) {
					break;	// dont delete it
				}
				@unlink($folder . $name);
				$filename = sprintf("%ss/%s", $type, $name);
				$bucketObj = new \Media\Bucket\Object($filename);
				$bucketObj->delete();
				break;
			
			case 'show':
				return $folder . $name;

			case 'getType':
				return mime_content_type($folder . $name);

			case 'upload':
				$func = "upload" . ucfirst($type);
				$media = self::$func($name, "{$type}s", $opts);
				if ($media === false) {
					$media = '';
				}
				$uploadToBucket = $opts['uploadToBucket'] ?? true;
				if ($uploadToBucket) {
					static::media($media, 'uploadToBucket', $opts);
				}
				return $media;

			case 'uploadToBucket':
				$objectName = sprintf("%ss/%s", $type, $name);
				$filePath = static::media($name, 'show', $opts);
				$bucketObj = new \Media\Bucket\Object($objectName);
				$bucketObj->upload($filePath);
				break;

			case 'download':
				$func = "download" . ucfirst($type);
				$media = self::$func($name, $opts);
				if ($media === false) {
					$media = '';
				}
				$uploadToBucket = $opts['uploadToBucket'] ?? true;
				if ($uploadToBucket) {
					static::media($media, 'uploadToBucket', $opts);
				}
				return $media;

			case 'display':
				$useBucket = $opts['useBucket'] ?? true;
				if ($useBucket) {
					$path = sprintf("%simages/%s", GCDN, $name);
				} else {
					$path = sprintf("%suploads/images/%s", CDN, $name);
				}
				return $path;

			case 'dimensions':
				$type = static::media($name, 'getType');
				$info = getimagesize($folder . $name);
				if (preg_match('/image/', $type) && $info !== false) {
					return [
						'width' => $info[0],
						'height' => $info[1]
					];
				} else {
					return [];
				}

			case 'sendFile':
				$file = $folder . $name;
				$contentType = mime_content_type($file);
				header('Content-Type: ' . $contentType);
				header('Content-Length: ' . filesize($file));
				$sendFile = $opts['send'] ?? false;
				if ($sendFile) {
					header(sprintf('Content-Disposition: attachment; filename="%s"', $name));
					readfile($file);
				} else {
					echo file_get_contents($file);
				}
				break;
		}
	}
}

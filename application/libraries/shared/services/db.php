<?php
namespace Shared\Services;

use MongoDB\Driver\ReadPreference;
use Shared\Utils as Utils;
use Framework\{Registry, ArrayMethods};

class Db {
	const ID = 'MongoDB\BSON\ObjectID';
	const DATE = 'MongoDB\BSON\UTCDateTime';
	const REGEX = 'MongoDB\BSON\Regex';
	const DOCUMENT = 'MongoDB\BSON\Document';
	const REGISTRY = "MongoDB";
	
	/**
	 * Constants for Read Preference
	 */
	const READ_PRIMARY = ReadPreference::RP_PRIMARY;
	const READ_PRIMARY_PREFERRED = ReadPreference::RP_PRIMARY_PREFERRED;
	const READ_SECONDARY = ReadPreference::RP_SECONDARY;
	const READ_SECONDARY_PREFERRED = ReadPreference::RP_SECONDARY_PREFERRED;

	public static function connect() {
		$mongoDB = Registry::get(static::REGISTRY);
		if (!$mongoDB) {
		    $configuration = Registry::get("configuration");

		    try {
		        $dbconf = $configuration->parse("configuration/database")->database->mongodb;
		        $mongo = new \MongoDB\Client("mongodb://" .$dbconf->url.":27017/" . $dbconf->dbname);

		        $mongoDB = $mongo->selectDatabase($dbconf->dbname);
		    } catch (\Exception $e) {
		        throw new \Framework\Database\Exception("DB Error");   
		    }

		    Registry::set(static::REGISTRY, $mongoDB);
		}
		return $mongoDB;
	}

	/**
	 * All the read operations following this command will be done such that data is
	 * read from secondary instance whenever possible
	 */
	public static function readPreference($readPref = ReadPreference::RP_SECONDARY_PREFERRED) {
		$db = Registry::get(static::REGISTRY);
		$pref = new ReadPreference($readPref);
	    $uriOpts = ['readPreference' => $pref];
		$newDb = $db->withOptions($uriOpts);
		Registry::set(static::REGISTRY, $newDb);
	}

	public static function generateId($str = true) {
		$id = new \MongoDB\BSON\ObjectID();
		if ($str) {
			$id = self::simplifyValue($id);
		}
		return $id;
	}

	public static function getCount($cursor) {
		$count = 0; $result = [];
		foreach ($cursor as $c) {
			$result[] = $c;
			$count++;
		}

		return [
			'count' => $count, 'result' => $result
		];
	}

	public static function getCacheKey($table, $query = [], $fields = []) {
		return sprintf("%s__%s__%s", $table, ArrayMethods::arraySig($query), json_encode($fields));
	}

	public static function convertType($value, $type = 'id') {
		switch ($type) {
			case 'id':
				return Utils::mongoObjectId($value);

			case 'regex':
				return Utils::mongoRegex($value);
			
			case 'date':
			case 'datetime':
			case 'time':
				return self::time($value);
		}
		return '';
	}

	public static function updateRaw($table, $find, $set, $opts = []) {
		$collection = Registry::get("MongoDB")->$table;
		
		$many = $opts['many'] ?? false;
		if ($many) {
			$collection->updateMany($find, $set);
		} else {
			$collection->updateOne($find, $set);
		}
	}

	/**
	 * Converts the Time given to MongoDB UTC DateTime
	 * @param  string|int|null $date Date that can be passed to strtotime or time in seconds
	 * @return object       MongoDB\BSON\UTCDateTime
	 */
	public static function time($date = null) {
		if (is_string($date)) {
			$time = strtotime($date);
		} else if (is_numeric($date)) {
			$time = $date;
		} else {
			$time = round(microtime(true), 3);
		}

		return new \MongoDB\BSON\UTCDateTime($time * 1000);
	}

	/**
	 * Checks the Default MongoDb types
	 * @param  mixed  $value 
	 * @param  string  $type  Name of the basic type
	 * @return boolean
	 */
	public static function isType($value, $type = '') {
		switch ($type) {
			case 'id':
				return is_object($value) && is_a($value, Db::ID);

			case 'regex':
				return is_object($value) && is_a($value, Db::REGEX);

			case 'document':
				return (is_object($value) && (
					is_a($value, 'MongoDB\Model\BSONArray') ||
					is_a($value, 'MongoDB\Model\BSONDocument') ||
					is_a($value, 'stdClass')
				));
			
			case 'date':
			case 'datetime':
			case 'time':
				return is_object($value) && is_a($value, Db::DATE);

			default:
				return is_object($value) && is_a($value, Db::ID);
		}
	}

	public static function dateQuery($start = null, $end = null) {
		$changed = false;
		if ($start && $end) {
			if (self::isType($start, 'date') && self::isType($end, 'date')) {
				$dq = ['start' => $start, 'end' => $end];
				$changed = true;
			}
		}

		if (!$changed) {
			$dq = \Shared\Utils::dateQuery(['start' => $start, 'end' => $end]);	
		}

		$result = [];
		if ($start) {
			$result['$gte'] = $dq['start'];
		}

		if ($end) {
			$result['$lte'] = $dq['end'];
		}
		return $result;
	}

	/**
     * To format a key before storing it in DB
     * @param  string $key Key value
     * @return string
     */
    public static function formatKey($key, $format = true) {
        if (strlen($key) == 0) {
            $key = "Empty";
        }

        if ($format) {
        	$key = str_replace(".", "-", $key);	
        } else {	// decode the key
        	$key = str_replace("-", ".", $key);
        }
        return $key;
    }

	public static function opts($fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		$opts = [];

		if (!empty($fields)) {
		    $opts['projection'] = $fields;
		}
		
		if ($order && $direction) {
		    switch ($direction) {
		        case 'desc':
		        case 'DESC':
		            $direction = -1;
		            break;
		        
		        case 'asc':
		        case 'ASC':
		            $direction = 1;
		            break;

		        default:
		        	$direction = -1;
		        	break;
		    }
		    $opts['sort'] = [$order => $direction];
		}

		if ($page) {
		    $opts['skip'] = $limit * ($page - 1);
		}

		if ($limit) {
		    $opts['limit'] = (int) $limit;
		}
		return $opts;
	}

	public static function collection($model) {
		$model = "\\$model";
		$m = new $model;

		return $m->getCollection();
	}

	/**
	 * Wrapper for database find which also converts the returned records to simple objects
	 * @return array 	Array of objects
	 */
	public static function findAll($model, $query, $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		$cursor = self::query($model, $query, $fields, $order, $direction, $limit, $page);
		$results = [];
		foreach ($cursor as $c) {
			$obj = self::simplifyDoc($c);
			$id = $obj->_id ?? null;

			// to maintain backwards compatibility with SQL syntax
			if (!property_exists($obj, 'id')) {
				$obj->id = $id;
			}

			if (!is_null($id) && is_string($id)) {
				$results[$id] = $obj;
			} else {
				$results[] = $obj;
			}
		}
		return $results;
	}

	/**
	 * Wrapper for database findOne which also converts the returned records to simple objects
	 * @return array 	Array of objects
	 */
	public static function first($model, $query, $fields = [], $order = null, $direction = null) {
		$cursor = self::query($model, $query, $fields, $order, $direction, 1);
		$result = null;
		foreach ($cursor as $c) {
			$obj = self::simplifyDoc($c);
			$id = $obj->_id ?? null;

			// to maintain backwards compatibility with SQL syntax
			if (!property_exists($obj, 'id')) {
				$obj->id = $id;
			}
			$result = $obj;
		}
		return $result;
	}

	public static function cacheFirst($model, $query, $fields = [], $order = null, $direction = null) {
		$cacheKey = static::getCacheKey($model, $query, $fields);
		$foundCache = Utils::getCache($cacheKey, false);

		if ($foundCache === false) {
			$foundCache = static::first($model, $query, $fields, $order, $direction);
			Utils::setCache($cacheKey, $foundCache);
		}
		return $foundCache;
	}

	/**
	 * Simplify the database document (Equivalent Term => Mysql Row) to an object
	 * containing properties and values
	 * @param  mixed $doc Document provided
	 * @return object
	 */
	public static function simplifyDoc($doc) {
		$obj = $arr = Utils::toArray($doc);
		foreach ($obj as $k => $value) {
			$arr[$k] = self::simplifyValue($value);
		}
		$obj = (object) $arr;
		return $obj;
	}

	/**
	 * Convert the Database objects to simple objects that can be used by the language
	 * @param  mixed $value  Mixed object generally of type -> \MongoDB\BSON\*
	 * @return mixed
	 */
	public static function simplifyValue($value) {
		if (is_object($value)) {
		    if (self::isType($value, 'id')) {
		        $raw = Utils::getMongoID($value);
		    } else if (self::isType($value, 'date')) {
		        $v = $value->toDateTime();
		        $raw = $v;
		    } else if (self::isType($value, 'document')) {
		        $raw = Utils::toArray($value);
		    } else if (self::isType($value, 'regex')) {
		    	$raw = $value->getPattern();
		    } else {    // fallback case
		        $raw = (object) $value;
		    }
		} else {
		    $raw = $value;
		}
		return $raw;
	}

	/**
	 * Aggregate High Level Wrapper
	 * @param  string  $model  the name of the model to be passed
	 * @param  array  $query  the query used to search records in DB
	 * @param  array  $groupBy Array of fields by which records are to be grouped
	 * @param  array $extra   Extra Params Keys => (sort, count, limit)
	 * @return array 		Array of objects (i.e. records)
	 */
	public static function aggregate($model, $query, $groupBy, $extra = []) {
		$project = ['_id' => 0]; $group = ['_id' => []];
		foreach ($groupBy as $f) {
			$project[$f] = 1;
			$group['_id'][$f] = sprintf("$%s", $f);
		}
		if (count($groupBy) === 1) {
			$group['_id'] = array_values($group['_id'])[0];
		}

		$countByField = $extra['count'] ?? false;
		if ($countByField) {	// when we need to sum by a specific field
			$project[$countByField] = 1;
			$group['count'] = ['$sum' => sprintf("$%s", $countByField)];
		} else {	// When we need to sum all the records by grouping
			$group['count'] = ['$sum' => 1];
		}
		/***** @todo - See this grouping *******/
		$groupByDate = $extra['groupByDate'] ?? false;
		if ($groupByDate) {
			$group['_id'][$groupByDate] = ['$dateToString' => ['format' => "%Y-%m-%d", 'date' => sprintf("$%s", $groupByDate)]];
		}

		$aggQuery = [
			['$match' => $query],
			['$project' => $project],
			['$group' => $group]
		];

		// Check for sorting of records
		$sort = $extra['sort'] ?? false;
		if ($sort) {
			$aggQuery[] = ['$sort' => ['count' => -1]];
		}

		// Check if we need to limit the no of records: generally used after sorting
		$limit = $extra['limit'] ?? false;
		if ($limit) {
			$aggQuery[] = ['$limit' => (int) $limit];
		}

		if (\Framework\Registry::get("DEBUG")) {
			var_dump($aggQuery);
		}
		return self::collection($model)->aggregate($aggQuery);
	}

	// query method
	public static function query($model, $query, $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		$model = "\\$model";
		$m = new $model;
		$where = $m->_updateQuery($query);
		$fields = $m->_updateFields($fields);
		
		return self::_query($m, $where, $fields, $order, $direction, $limit, $page);
	}

	protected static function _query($model, $where, $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		$collection = $model->getCollection();

		$opts = self::opts($fields, $order, $direction, $limit, $page);
		return $collection->find($where, $opts);
	}

	public static function count($model, $query) {
		$model = "\\$model";
		$m = new $model;
		$where = $m->_updateQuery($query);

		$collection = $m->getCollection();
		return $collection->count($where);
	}

	public static function cacheAll($model, $query, $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		return static::cacheQuery($model, $query, $fields, $order, $direction, $limit, $page);
	}

	public static function cacheQuery($model, $query, $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
		$cacheKey = static::getCacheKey($model, $query, $fields);
		$foundCache = Utils::getCache($cacheKey, false);

		if ($foundCache === false) {
			$foundCache = static::findAll($model, $query, $fields, $order, $direction, $limit, $page);
			Utils::setCache($cacheKey, $foundCache);
		}
		return $foundCache;
	}
}

<?php

/**
 * Contains similar code of all models and some helpful methods
 *
 * @author Hemant Mann
 */

namespace Shared {
    use JsonSerializable;
    use Framework\{Registry, StringMethods, TimeZone};
    use \Shared\Services\Db as Db;

    class Model extends \Framework\Model implements JsonSerializable {
        const TASKS = [];
        
        /**
         * @readwrite
         * @var boolean
         */
        protected $_allowNull = false;

        /**
         * @read
         */
        protected $_types = ["autonumber", "text", "integer", "decimal", "boolean", "datetime", "date", "time", "mongoid", "array"];

        /**
         * @column
         * @readwrite
         * @primary
         * @type autonumber
         * @label ID
         */
        protected $__id = null;

        /**
         * @column
         * @readwrite
         * @type boolean
         * @index
         */
        protected $_live = null;

        /**
         * @column
         * @readwrite
         * @type datetime
         * @label Created
         */
        protected $_created = null;

        /**
         * @column
         * @readwrite
         * @type datetime
         */
        protected $_modified = null;

        /**
         * @column
         * @readwrite
         * @type array
         */
        protected $_meta = [];

        public function load() {
            
        }

        public function __toString() {
            echo json_encode($this);
        }

        public function jsonSerialize() {
            $fields = static::fields();
            $arr = [];
            foreach ($fields as $f) {
                $arr[$f] = $this->$f;
            }
            $arr['id'] = $this->getId();
            return $arr;
        }

        public function getTaskInfo($taskName = '') {
            if (!array_key_exists($taskName, static::TASKS)) {
                return '';
            }
            $task = \Task::first(['object' => get_class($this), 'object_id' => $this->_id, 'name' => $taskName]);
            if (!$task) return '';

            $dt = TimeZone::zoneConverter($task->execution_time);
            $dateOnly = $dt->format('F j\, o');
            $timeOnly = $dt->format('g\:i a');
            return sprintf("%s %s at %s", static::TASKS[$taskName]['info'], $dateOnly, $timeOnly);
        }

        public function createTask($name, $time = null, $org = null) {
            if (!array_key_exists($name, static::TASKS)) {
                throw new \Exception("Task does not exists!!");
            }

            // First check if task already exists
            $search = ['name' => $name, 'object' => get_class($this), 'object_id' => $this->_id];
            $task = \Task::first($search);
            if ($task) {
                return false;
            }
            return $search;
        }

        public function deleteTask($name = '') {
            if (!$name) {
                return false;
            }
            $task = \Task::first(['object' => get_class($this), 'object_id' => $this->_id, 'name' => $name]);
            $task->delete();
            return true;
        }

        public static function fields($exclude = []) {
            $model = get_called_class();
            $cl = "\\" . $model;
            $m = new $cl;
            $columns = $m->getColumns();
            $fields = array_keys($columns);

            $ans = [];
            foreach ($fields as $f) {
                if (!in_array($f, $exclude)) {
                    $ans[] = $f;    
                }
            }

            return $ans;
        }

        public function setLive($val) {
            $this->_live = (boolean) $val;
        }

        public static function hourly() {
            // override this method to do cron tasks
        }

        public function display() {
            $columns = $this->getColumns();
            $arr = [];
            foreach ($columns as $key => $value) {
                $field = "_{$key}";
                $arr[$key] = $this->$field;
            }
            return $arr;
        }

        public static function modifyQuery($query, $extra = []) {
            $fields = static::fields();
            foreach ($extra as $key => $value) {
                if (in_array($key, $fields)) {
                    $query[$key] = $value;  
                }
            }
            return $query;
        }

        public static function displayImg($name = '', $folder = "images") {
            return Utils::media($name, 'display');
        }

        public function &getMeta() {
            if (is_null($this->_meta)) {
                $this->_meta = [];
            }
            return $this->_meta;
        }

        /**
         * Converts array of \Shared\Model objects to array of stdClass objects
         * Also converts DateTime class to simple string, pass an extra key in
         * the last parameter to stop this datetime conversion
         * @return array         Array of Objects
         */
        public static function objectArr($arr = [], $fields = [], $opts = []) {
            if (!is_array($arr)) {
                $newArr = [];
                $newArr[] = $arr;
                $arr = $newArr;
            }
            if (count($fields) === 0) {
                $fields = static::fields();
            }
            
            $results = [];
            foreach ($arr as $key => $a) {
                $data = [];
                foreach ($fields as $f) {
                    $convert = $opts['convert'] ?? true;
                    $convertProp = $a->$f ?? null;
                    if ($convert && $convertProp && is_object($a->$f) && is_a($a->$f, 'DateTime')) {
                        $dtObject = TimeZone::zoneConverter($a->$f, $opts);
                        $dateTime = $opts['date_time'] ?? false;
                        if ($dateTime) {
                            $data[$f] = $dtObject->format('Y-m-d H:i:s');
                        } else {
                            $data[$f] = $dtObject->format('Y-m-d');
                        }
                    } else {
                        $data[$f] = $a->$f ?? null;
                    }
                }
                if (!isset($data['id'])) $data['id'] = $a->_id ?? null;

                $appConfig = Utils::getAppConfig();
                $unsetId = $opts['unset_id'] ?? $appConfig->model->object->unsetId;
                if ($unsetId) unset($data['id']);
                
                $includeKey = $opts['include_key'] ?? true;
                $obj = (object) $data;
                if (@($a->_id === $key) && $includeKey) {
                    $results[$key] = $obj;
                } else {
                    $results[] = $obj;
                }
            }
            return $results;
        }

        public function getMongoID($field = null) {
            if ($field) {
                $id = sprintf('%s', $field);
            } else {
                $id = sprintf('%s', $this->__id);
            }
            return $id;
        }

        /**
         * Every time a row is created these fields should be populated with default values.
         */
        public function save() {
            $primary = $this->getPrimaryColumn();
            $raw = $primary["raw"];
            $collection = $this->getCollection();

            $doc = []; $columns = $this->getColumns();
            foreach ($columns as $key => $value) {
                $field = $value['raw'];
                $current = $this->$field;
                
                $v = $this->_convertToType($current, $value['type']);
                $checkEmpty = $this->_preventEmpty($v, $value['type']);
                
                $allowNull = !is_null($this->__id) || $this->_allowNull;
                if (is_null($checkEmpty)) {
                    // check when to save null values
                    if ($allowNull) {
                        $doc[$key] = null;
                    }
                } else {
                    $this->$field = $v;
                    $doc[$key] = $v;
                }
            }
            unset($doc['_id']); // this step is necessary

            if (empty($this->$raw)) {
                if (isset($doc['created'])) {
                    if (Db::isType($doc['created'], 'date')) {
                        $this->_created = $doc['created'];
                    } else if (is_string($doc['created'])) {
                        $this->_created = $doc['created'] = Db::time($doc['created']);
                    }
                } else {
                    $this->_created = $doc['created'] = Db::time();
                }

                $result = $collection->insertOne($doc);
                $this->__id = $result->getInsertedId();
            } else {
                $this->_modified = $doc['modified'] = Db::time();

                $this->__id = Utils::mongoObjectId($this->__id);
                $result = $collection->updateOne(['_id' => $this->__id], ['$set' => $doc]);
            }

            // remove BSON Types from class because they prevent it from
            // being serialized and store into the session
            foreach ($columns as $key => $value) {
                $raw = "_{$key}"; $val = $this->$raw;
                $this->$raw = Db::simplifyValue($val);
            }
        }

        protected function _preventEmpty($value, $type) {
            switch ($type) {
                case 'integer':
                    if ($value === 0) {
                        $value = null;
                    }
                    break;
                
                case 'array':
                    if (count($value) === 0) {
                        $value = null;
                    }
                    break;

                case 'decimal':
                    if ($value === 0.0) {
                        $value = null;
                    }
                    break;

                case 'text':
                    if ($value === '') {
                        $value = null;
                    }
                    break;

                case 'mongoid':
                    if (is_string($value) && strlen(trim($value)) === 0) {
                        $value = null;
                    }
                    break;
            }
            return $value;
        }

        /**
         * @important | @core function
         * Specific types are needed for MongoDB for proper querying
         * @param misc $value
         * @param string $type
         */
        public function _convertToType($value, $type) {
            if (Db::isType($value, 'regex')) {
                return $value;
            }

            switch ($type) {
                case 'text':
                    if (!is_null($value)) {
                        $value = (string) $value;   
                    }
                    break;

                case 'integer':
                    $value = (int) $value;
                    break;

                case 'boolean':
                    if (is_array($value)) {
                        // don't do anything
                    } else {
                        $value = (boolean) $value;
                    }
                    break;

                case 'decimal':
                    $value = (float) $value;
                    break;

                case 'datetime':
                case 'date':
                    if (is_array($value)) {
                        break;
                    } else if (is_object($value)) {
                        if (Db::isType($value, 'date')) {
                           break;
                        } else if (is_a($value, 'DateTime')) {
                            $dt = TimeZone::zoneConverter($value, ['zone' => 'Europe/London']);
                            $value = Db::time($dt->getTimestamp());
                        } else {
                            $value = Db::time();
                        }
                    } else if (is_string($value)) {
                        $value = Db::time($value);
                    }
                    break;

                case 'autonumber':
                case 'mongoid':
                    if (Db::isType($value, 'id')) {
                        break;
                    } else if (is_array($value)) {
                        $copy = $value; $value = [];
                        foreach ($copy as $key => $val) {
                            $value[$key] = Utils::mongoObjectId($val);
                        }
                    } else {
                        $value = Utils::mongoObjectId($value);
                    }
                    break;

                case 'array':
                    if (!is_array($value)) {
                        $value = (array) $value;   
                    }
                    break;
                
                default:
                    $value = $value;
                    break;
            }
            return $value;
        }

        /**
         * @getter
         * @return \MongoDB\Collection Collection object of MongoDB
         */
        public function getCollection() {
            $table = $this->getTable();
            $collection = Registry::get("MongoDB")->$table;
            return $collection;
        }

        /**
         * @getter
         * Returns "_id" if presents else "__id"
         */
        public function getId() {
            if (property_exists($this, '_id')) {
                return $this->_id;
            }
            return $this->__id;
        }

        /**
         * Updates the MongoDB query
         */
        public function _updateQuery($where) {
            $columns = $this->getColumns();

            $query = [];
            foreach ($where as $key => $value) {
                $key = str_replace('=', '', $key);
                $key = str_replace('?', '', $key);
                $key = preg_replace("/\s+/", '', $key);

                // because $this->id equivalent to $this->_id
                if ($key == "id" && !property_exists($this, '_id')) {
                    $key = "_id";
                }

                if (isset($columns[$key])) {
                    $query[$key] = $this->_convertToType($value, $columns[$key]['type']);   
                } else {
                    $query[$key] = $value;
                }
            }
            return $query;
        }

        /**
         * Updates the fields when query mongodb
         * Checks for correct property "id" and "_id"
         * Also accounts for "*" in MySql
         */
        public function _updateFields($fields) {
            $f = [];
            foreach ($fields as $key => $value) {
                if ($value == "*" || !is_string($value)) {
                    continue;
                }

                if ($value == "id" && !property_exists($this, '_id')) {
                    $f["_id"] = 1;
                } else {
                    $f[$value] = 1;
                }
            }
            return $f;
        }

        /**
         * @param array $where ['name' => 'something'] OR ['name = ?' => 'something'] (both works)
         * @param array $fields ['name' => true, '_id' => true]
         * @param string $order Name of the field
         * @param int $direction 1 | -1 OR "asc" |  "desc"
         * @param int $limit
         * @return array
         */
        public static function all($where = [], $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
            $model = new static();
            $where = $model->_updateQuery($where);
            $fields = $model->_updateFields($fields);
            return $model->_all($where, $fields, $order, $direction, $limit, $page);
        }

        protected function _all($where = [], $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
            $collection = $this->getCollection();

            $opts = Db::opts($fields, $order, $direction, $limit, $page);

            $cursor = $collection->find($where, $opts);
            $results = [];
            foreach ($cursor as $c) {
                $converted = $this->_convert($c);
                if ($converted->_id) {
                    $key = Utils::getMongoID($converted->_id);
                    $results[$key] = $converted;
                } else {
                    $results[] = $converted;
                }
            }
            return $results;
        }

        /**
         * @param array $where ['name' => 'something'] OR ['name = ?' => 'something'] (both works)
         * @param array $fields ['name' => true, '_id' => true]
         * @param string $order Name of the field
         * @param int $direction 1 | -1 OR "asc" |  "desc"
         * @param int $limit
         * @return \Shared\Model object | null
         */
        public static function first($where = [], $fields = [], $order = null, $direction = null) {
            $model = new static();
            $where = $model->_updateQuery($where);
            $fields = $model->_updateFields($fields);
            return $model->_first($where, $fields, $order, $direction);
        }

        protected function _first($where = [], $fields = [], $order = null, $direction = null) {
            $collection = $this->getCollection();
            $record = null;

            if ($order && $direction) {
                $results = self::_all($where, $fields, $order, $direction, 1);
                
                if (count($results) === 1) {
                    $record = array_shift($results);   
                }
            } else {
                if (count($fields) === 0) {
                    $record = $collection->findOne($where);
                } else {
                    $record = $collection->findOne($where, ['projection' => $fields]);
                }

                $record = $this->_convert($record);    
            }

            return $record;
        }

        /**
         * Converts the MongoDB result to an object of class 
         * whose parent is \Shared\Model
         */
        protected function _convert($record) {
            if (!$record) return null;
            $columns = $this->getColumns();
            $record = (array) $record;

            $class = get_class($this);
            $c = new $class();

            foreach ($record as $key => $value) {
                if (!property_exists($this, "_{$key}")) {
                    continue;
                }
                $raw = "_{$key}";
                $c->$raw = Db::simplifyValue($value);
            }
            
            return $c;
        }

        /**
         * Find the records of the table and if none found then sets a
         * flash message to the session and redirects if $opts['redirect']
         * is set else returns the records
         */
        public static function isEmpty($query = [], $fields = [], $opts = []) {
            $records = self::all($query, $fields);
            $session = Registry::get("session");

            if (count($records) === 0) {
                if (isset($opts['msg'])) {
                    $session->set('$flashMessage', $opts['msg']);
                }

                if (isset($opts['redirect'])) {
                    $controller = $opts['controller'];
                    $controller->redirect($opts['redirect']);
                }
            }
            return $records;
        }

        public function delete() {
            $collection = $this->getCollection();

            $query = $this->_updateQuery(['_id' => $this->__id]);
            $return = $collection->deleteOne($query);
        }

        public static function deleteAll($query = []) {
            $instance = new static();
            $query = $instance->_updateQuery($query);
            $collection = $instance->getCollection();

            $return = $collection->deleteMany($query);
        }

        public static function count($query = []) {
            $model = new static();
            $query = $model->_updateQuery($query);
            return $model->_count($query);
        }

        protected function _count($query = []) {
            $collection = $this->getCollection();

            $count = $collection->count($query);
            return $count;
        }

        public static function cacheFirst($where = [], $fields = [], $order = null, $direction = null) {
            $cacheKey = static::getCacheKey($where, $fields);
            $foundCache = Utils::getCache($cacheKey, false);

            if ($foundCache === false) {
                $foundCache = static::first($where, $fields, $order, $direction);
                Utils::setCache($cacheKey, $foundCache);
            }
            return $foundCache;
        }

        public static function cacheAll($where = [], $fields = [], $order = null, $direction = null, $limit = null, $page = null) {
            $cacheKey = static::getCacheKey($where, $fields);
            $foundCache = Utils::getCache($cacheKey, false);

            if ($foundCache === false) {
                $foundCache = static::all($where, $fields, $order, $direction, $limit, $page);
                Utils::setCache($cacheKey, $foundCache);
            }
            return $foundCache;
        }
    }
}

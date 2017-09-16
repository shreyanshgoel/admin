<?php
namespace Framework;
use DateTime;
use DateTimeZone;

class TimeZone extends Base {
	/**
	 * TimeZone converter - Converts the Time given in one zone to other zone,
	 * By default search for Organization Key set in session to get Organization Time Zone
	 * else searches for 'zone' key provided with the extra's array
	 * @param object $dt DateTime class object
	 * @param array $extra Keys => (org, zone)
	 * @return  \DateTime Object of class DateTime
	 */
	public static function zoneConverter($dt, $extra = []) {
	    $org = $extra['org'] ?? (Registry::get("session")->get("org")) ?? (object) [];
	    if (is_object($org) && method_exists($org, 'getZone')) {
	    	$defaultZone = $org->getZone();
	    } else if (is_object($org)) {
	    	$defaultZone = $org->region['zone'] ?? 'Europe/London';
	    } else {
	    	$defaultZone = 'Europe/London';
	    }
	    $zone = $extra['zone'] ?? $defaultZone;
	    $tz = new DateTimeZone($zone);
	    $newDt = new DateTime(); $newDt->setTimezone($tz);

	    $newDt->setTimestamp($dt->getTimestamp());
	    return $newDt;
	}

	/**
	 * Generate UTC Date Time stamp based on the timezone provided to it
	 * so as to query for the whole day based on the given DateTime object
	 * @param string $date Date in string format -> Y-m-d
	 * @param object $dt object of class \DateTime
	 * @return array Array containing keys -> (start, end)
	 */
	public static function utcDateTime($date, $dt) {
	    $startDt = new DateTime(); $endDt = new DateTime();

	    $startDt->setTimezone($dt->getTimezone());
	    $endDt->setTimezone($dt->getTimezone());
	    
	    $start = (int) strtotime($date . ' 00:00:00');
	    $end = (int) strtotime($date . ' 23:59:59');
	    
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
	 * Get a date time object with zone set to particular zone
	 * @param  string $zone Valid Date Time zone
	 * @param  string|int $time Valid timeString
	 * @return object       DateTime
	 */
	public static function zoneTime($zone = 'Europe/London', $time = null) {
		if (is_null($time)) {
			$time = date('Y-m-d H:i:s');
		}
		$dt = new DateTime();
		$dt = self::zoneConverter($dt, ['zone' => $zone]);
		$dt->modify($time);
		return $dt;
	}

	/**
	 * Get the Date Query for any date i.e From Start of the day to end of the day
	 * for the organization with current time zone
	 * @param  string $date  Y-m-d H:i:s format
	 * @param  array $extra Array passed to zoneConverter function
	 * @return array        Result of utcDateTime
	 */
	public static function dateQuery($date, $extra = []) {
		$dt = new DateTime($date);
    	$dt = static::zoneConverter($dt, $extra);
    	return static::utcDateTime($date, $dt);
	}

	public static function dateRangeQuery($dq, $extra = []) {
		$start = $dq['start'];
		if ($start instanceof \MongoDB\BSON\UTCDateTime) {
			$startDq = $dq;
		} else {
			$startDq = static::dateQuery($start, $extra);
		}

    	$end = $dq['end'];
    	if ($end instanceof \MongoDB\BSON\UTCDateTime) {
			$endDq = $dq;
		} else {
			$endDq = static::dateQuery($end, $extra);
		}

    	return [
    		'start' => $startDq['start'],
    		'end' => $endDq['end']
    	];
	}

	/**
	 * Get the date for today based on the timezone for an org
	 * @param  array  $opts Array of Opts, Keys -> ('zone', 'org')
	 * @return string       Date in Y-m-d format
	 */
	public static function getToday($opts = []) {
		$dt = new \DateTime();

		$zoneDt = static::zoneConverter($dt, $opts);
		$zoneDt->modify('today');
		$formatStr = $opts['format'] ?? 'd-m-Y';
		return $zoneDt->format($formatStr);
	}

	/**
	 * Get the date for today based on the timezone for an org
	 * @param  array  $opts Array of Opts, Keys -> ('zone', 'org')
	 * @return string       Date in Y-m-d format
	 */
	public static function getYesterday($opts = []) {
		$dt = new \DateTime();

		$zoneDt = static::zoneConverter($dt, $opts);
		$zoneDt->modify('-1 day');
		$formatStr = $opts['format'] ?? 'd-m-Y';
		return $zoneDt->format($formatStr);
	}

	/**
	 * Formats the date for based on the timezone for an org
	 * @param  array  $opts Array of Opts, Keys -> ('zone', 'org')
	 * @return string       Date in asked format
	 */
	public static function format($opts = []) {
		$dt = new \DateTime();

		$zoneDt = static::zoneConverter($dt, $opts);
		$zoneDt->modify($opts['date']);
		$formatStr = $opts['format'] ?? 'd-m-Y';
		return $zoneDt->format($formatStr);
	}

	/**
	 * Find the time elapsed since the timestamp
	 * @param  string  $datetime Default arg passed to DateTime class
	 * @param  boolean $full     Whether full time is required
	 * @return string            Timem passed string
	 */
	public static function timeElapsed($datetime, $full = false) {
	    $now = new DateTime; $ago = new DateTime($datetime);
	    $diff = $ago->diff($now);

	    $diff->w = floor($diff->d / 7);
	    $diff->d -= $diff->w * 7;

	    $string = array(
	        'y' => 'year',
	        'm' => 'month',
	        'w' => 'week',
	        'd' => 'day',
	        'h' => 'hour',
	        'i' => 'minute',
	        's' => 'second',
	    );
	    foreach ($string as $k => &$v) {
	        if ($diff->$k) {
	            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
	        } else {
	            unset($string[$k]);
	        }
	    }

	    if (!$full) $string = array_slice($string, 0, 1);
	    return $string ? implode(', ', $string) . ' ago' : 'just now';
	}

	/**
	 * Print the full date + time in human readable format
	 * @param  object $dt    Class DateTime
	 * @param  array  $extra Extra Opts same as zoneConverter
	 * @return string        Formated date
	 */
	public static function printDateTime($dt, $extra = []) {
		$newDt = static::zoneConverter($dt, $extra);
		return $newDt->format('F j\, o \a\t g\:i a');
	}

	/**
	 * Print the full date in human readable format
	 * @param  object $dt    Class DateTime
	 * @param  array  $extra Extra Opts same as zoneConverter
	 * @return string        Formated date
	 */
	public static function printDate($dt, $extra = []) {
		$newDt = static::zoneConverter($dt, $extra);
		return $newDt->format('F j\, o');
	}

	public static function validateDate($date) {
	    $d = DateTime::createFromFormat('Y-m-d', $date);
	    return $d && $d->format('Y-m-d') === $date;
	}

	public static function firstDayOfMonth($opts = [], $relativeTime = null) {
		$dt = new DateTime;
		$zoneDt = static::zoneConverter($dt, $opts);
		if ($relativeTime) {
			$zoneDt->modify($relativeTime);
		}

		$dt = new DateTime('first day of ' . $zoneDt->format('Y-m'));
		return static::zoneConverter($dt, $opts);
	}

	public static function lastDayOfMonth($opts = [], $relativeTime = null) {
		$dt = new DateTime;
		$zoneDt = static::zoneConverter($dt, $opts);
		if ($relativeTime) {
			$zoneDt->modify($relativeTime);
		}

		$dt = new DateTime('last day of ' . $zoneDt->format('Y-m'));
		return static::zoneConverter($dt, $opts);
	}
}

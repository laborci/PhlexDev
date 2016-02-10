<?php namespace Phlex\Tool;

class PxDate {

	/**
	 * Creates a PHP \DateTime object
	 * @link http://php.net/manual/en/datetime.formats.php
	 * @link http://php.net/manual/en/timezones.php
	 * @param string [$dateString] PHP date string
	 * @param string [$timezone] PHP timezone string
	 * @return \DateTime
	 */
	public static function getDateTime($dateString = 'now', $timezone = null) {
		return $timezone?new DateTime($dateString, new DateTimeZone($timezone)):new DateTime($dateString);
	}

	/**
	 * @deprecated Use Date::getTimeStamp instead!
	 * Converts PHP date strings to timestamp. Uses DateTime object. That's why $timezone string should be specified!
	 * @link http://php.net/manual/en/datetime.formats.php
	 * @link http://php.net/manual/en/timezones.php
	 * @param string $dateString PHP date string
	 * @param string $timezone PHP timezone string
	 * @return int UNIX timestamp
	 */
	public static function createTimeStamp($dateString, $timezone = null){
		return static::getTimeStamp($dateString, $timezone);
	}

	/**
	 * Converts PHP date strings to timestamp. Uses DateTime object. That's why $timezone string should be specified!
	 * @link http://php.net/manual/en/datetime.formats.php
	 * @link http://php.net/manual/en/timezones.php
	 * @param string $dateString PHP date string
	 * @param string $timezone PHP timezone string
	 * @return int UNIX timestamp
	 */
	public static function getTimeStamp($dateString = 'now', $timezone = null) {
		return $dateString == 'now'?time():static::getDateTime($dateString, $timezone)->getTimestamp();
	}

	public static function sinceDate($dateString, $dateFormat = '%Y. %B %d.', $calculate = true, $timezone = null){
		if($calculate){
			$diff = time() - strtotime($dateString);
			if($diff <= (60)) return 'épp most';
			elseif($diff <= (10 * 60)) return ceil($diff / 60).' perce';
			elseif($diff <= (22 * 60)) return 'negyed órája';
			elseif($diff <= (37 * 60))	return 'fél órája';
			elseif($diff <= (52 * 60))	return 'háromnegyed órája';
			elseif($diff <= (24 * 60 * 60)) return round($diff / (60 * 60)).' órája';
			elseif($diff <= (10 * 24 * 60 * 60)) return round($diff / (24 * 60 * 60)).' napja';
		}
		return strftime($dateFormat, strtotime($dateString));
	}

	static function compare($lhs, $rhs = null){
		if(is_string($lhs)) $lhs = DateTime::createFromFormat("Y-m-d H:i:s", $lhs);
		if(is_string($rhs)) $rhs = DateTime::createFromFormat("Y-m-d H:i:s", $rhs);
		if($rhs === null) $rhs = new DateTime('now');

		if($lhs<$rhs) return -1;
		if($lhs>$rhs) return 1;
		return 0;
	}

	static function getDiffInSec($lhs, $rhs, $abs = false) {
		$diff = self::createTimeStamp($lhs) - self::createTimeStamp($rhs);
		return $abs?abs($diff):$diff;
	}

	static function dateDiff($lhs, $rhs, $resultInArray = false){
		$lhsObj = DateTime::createFromFormat("Y-m-d H:i:s", $lhs);
		$rhsObj = DateTime::createFromFormat("Y-m-d H:i:s", $rhs);
		$diff = $lhsObj->diff($rhsObj);
		$Y = $diff->format("%Y");
		$M = $diff->format("%M");
		$D = $diff->format("%D");
		$H = $diff->format("%H");
		$I = $diff->format("%I");
		$S = $diff->format("%S");
		$A = floor((static::createTimeStamp($lhs) - static::createTimeStamp($rhs)) / 86400); // 86400 := másodperc/nap
		if (is_nan($A)) $A = '00';

		if ($resultInArray) return array('y' => $Y, 'm' => $M, 'd' => $D, 'h' => $H, 'i' => $I, 's' => $S, 'a' => $A);
		else {
			$time = $diff->format("%H:%I:%S");
			$Y = $Y == '00'?'':$Y.' év, ';
			$M = $M == '00'?'':$M.' hónap, ';
			$D = $D == '00'?'':$D.' nap, ';
			return $Y.$M.$D.$time;
		}
	}

	/**
	 * H:i:s formátumok különbségéből adja vissza az eltelt időt [day nap, ]H:i:s formában
	 *
	 * @param string $lhs	A kezdeti időpont H:i:s formában
	 * @param string $rhs	A végidőpont H:i:s formában
	 * @param string $formatString	Elhelyezhetős placeholderek: %a - napok[lehet nagyobb 365-nél] %h - óra %i - perc %s - másodperc
	 * @return mixed paraméterhiba esetén false, egyébként a string
	 */
	static function getFormattedDifference($lhs, $rhs, $formatString) {
		if (!static::isValidDateTimeString($lhs) || !static::isValidDateTimeString($rhs) || $rhs < $lhs) return false;
		$diffInSec = static::createTimeStamp($rhs) - static::createTimeStamp($lhs);
		$diff = array();
		$diff['a'] = str_pad(floor($diffInSec / 86400), 2, "0", STR_PAD_LEFT);
		$diffInSec %= 86400;
		$diff['h'] = str_pad(floor($diffInSec / 3600), 2, "0", STR_PAD_LEFT);
		$diffInSec %= 3600;
		$diff['i'] = str_pad(floor($diffInSec / 60), 2, "0", STR_PAD_LEFT);
		$diffInSec %= 60;
		$diff['s'] = str_pad($diffInSec, 2, "0", STR_PAD_LEFT);

		$formatString = preg_replace("/%\?a(.*?)%!a/", trim($diff['a'],'0')?'$1':'', $formatString);
		$formatString = preg_replace("/%a/", $diff['a'], $formatString);
		$formatString = preg_replace("/%\?h(.*?)%!h/", trim($diff['h'],'0')?'$1':'', $formatString);
		$formatString = preg_replace("/%h/", $diff['h'], $formatString);
		$formatString = preg_replace("/%\?i(.*?)%!i/", trim($diff['i'],'0')?'$1':'', $formatString);
		$formatString = preg_replace("/%i/", $diff['i'], $formatString);
		$formatString = preg_replace("/%\?s(.*?)%!s/", trim($diff['s'],'0')?'$1':'', $formatString);
		$formatString = preg_replace("/%s/", $diff['s'], $formatString);

		return $formatString;
	}
	static function getFormattedInterval($interval, $formatString, $isMinInsteadOfSec = false) {
		$lhs = date('Y-m-d H:i:s');
		$rhs = date('Y-m-d H:i:s', time() + $interval * ($isMinInsteadOfSec?60:1));
		return static::getFormattedDifference($lhs, $rhs, $formatString);
	}

	/**
	 * Checks if the given string is a not null, Y-m-d H:i[:s] formatted string which is not
	 * 0000-00-00 00:00:00 and not 1970-01-01 01:00:00.
	 * @param string $strToCheck
	 */
	static function isValidDateTimeString($strToCheck) {
		if (!is_string($strToCheck) || !preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}(:\d{2})?$/', $strToCheck)) return false;
		return !preg_match('/^0000-00-00\s+00:00(:00)?$/', $strToCheck);
	}

	/**
	 * Checks if the given string is a not null, Y-m-d H:i[:s] formatted string which is not
	 * 0000-00-00 00:00:00 and not 1970-01-01 01:00:00.
	 * @param string $strToCheck
	 */
	static function isValidDateString($strToCheck) {
		if (!is_string($strToCheck) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $strToCheck)) return false;
		return !preg_match('/^0000-00-00$/', $strToCheck);
	}

	static function formatted($format, $time = 'now', $timezone = null, $formatAsStrf = false) {
		if (is_numeric($time) && intval($time) == $time) {
			$date = static::getDateTime('now', $timezone);
			$date->setTimestamp($time);
		} else if ($time instanceof DateTime) {
			$date = $time;
		} else $date = new DateTime($time);
		return $formatAsStrf?strftime($format, $date->getTimestamp()):$date->format($format);
	}

	/**
	 * @param int $week number of the week
	 * @param int [$year = null] the year to calculate with. Gets current year if null.
	 * @param strnig [$timezone = null] timezone string
	 * @return int UNIX timestamp
	 */
	static function getWeekStart($week = null, $year = null, $timezone = null) {
		if (!$week) $week = PxDate::formatted('W', 'now', $timezone);
		return strtotime(($year?$year:static::formatted('Y', 'now', $timezone)).'W'.str_pad($week, 2, '0', STR_PAD_LEFT));
	}

	/**
	 * @param int $week number of the week
	 * @param int [$year = null] the year to calculate with. Gets current year if null.
	 * @param strnig [$timezone = null] timezone string
	 * @return \DateTime
	 */
	static function getWeekStartDate($week = null, $year = null, $timezone = null) {
		$date = static::getDateTime('now', $timezone);
		$date->setTimestamp(static::getWeekStart($week, $year, $timezone));
		return $date;
	}

	/**
	 * @param string $formatString PHP date format string, e.g. Y-m-d
	 * @param int $week number of the week
	 * @param int [$year = null] the year to calculate with. Gets current year if null.
	 * @param strnig [$timezone = null] timezone string
	 * @return int UNIX timestamp
	 */
	static function getFormattedWeekStart($formatString, $week = null, $year = null, $timezone = null) {
		return static::formatted($formatString, static::getWeekStart($week, $year, $timezone));
	}

	/**
	 * @param int $week number of the week
	 * @param int [$year = null] the year to calculate with. Gets current year if null.
	 * @param strnig [$timezone = null] timezone string
	 * @return int UNIX timestamp
	 */
	static function getWeekEnd($week = null, $year = null, $timezone = null) {
		if (!$week) $week = PxDate::formatted('W', 'now', $timezone);
		$sixDays = 518400; // 6 * 24 * 3600
		return strtotime(($year?$year:PxDate::formatted('Y', 'now', $timezone)).'W'.str_pad($week, 2, '0', STR_PAD_LEFT)) + $sixDays;
	}

	/**
	 * @param int $week number of the week
	 * @param int [$year = null] the year to calculate with. Gets current year if null.
	 * @param strnig [$timezone = null] timezone string
	 * @return \DateTime
	 */
	static function getWeekEndDate($week = null, $year = null, $timezone = null) {
		$date = static::getDateTime('now', $timezone);
		$date->setTimestamp(static::getWeekEnd($week, $year, $timezone));
		return $date;
	}

	/**
	 * @param string $formatString PHP date format string, e.g. Y-m-d
	 * @param int $week number of the week
	 * @param int [$year = null] the year to calculate with. Gets current year if null.
	 * @param strnig [$timezone = null] timezone string
	 * @return int UNIX timestamp
	 */
	static function getFormattedWeekEnd($formatString, $week = null, $year = null, $timezone = null) {
		return static::formatted($formatString, static::getWeekEnd($week, $year, $timezone));
	}

}

<?php
namespace ttm\util;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * UtilDate - Utilitary class to centralize the treatement of date 
 * conversions (parsing string<->DateTime)
 *
 * @package ttm-core-php
 * @namespace ttm\util
 * @version 1.0
 */
class UtilDate {

	/**
	 * @method dateToString - parse date to string (without time)
	 *
	 * @param $dateTime - date for convertion
	 * @param $formatDate - format (default=Y-m-d) 
	 * 
	 * @return string with formatted date
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 */
	public static function dateToString(\DateTime $dateTime ,string $formatDate = "Y-m-d"):string {
		if(is_null($dateTime)) 
			return null;
			
		return $dateTime->format($formatDate);
	}

	/**
	 * @method stringToDate - parse string to date (without time)
	 *
	 * @param $stringDate - string date for convertion
	 * @param $formatDateTime - format (default=Y-m-d H:i:s.u)
	 *
	 * @return DateTime with date passed on string
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 */
	public static function stringToDate(string $stringDate, string $formatDateTime = "Y-m-d H:i:s.u"):\DateTime {

		if(is_null($stringDate))
			return null;

		$stringDate.=" 00:00:00.000000";
		
		return date_create_from_format($formatDateTime, $stringDate);
	}
}

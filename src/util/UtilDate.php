<?php
namespace ttm\util;

class UtilDate {
	
	private static $formatDate = "Y-m-d";
	private static $formatDateTime = "Y-m-d H:i:s.u";
	
	public static function dateToString(\DateTime $dateTime):string {
		if(is_null($dateTime)) 
			return null;
			
		return $dateTime->format(static::$formatDate);
	}

	public static function stringToDate(string $stringDate):\DateTime {
// 		$dateTime = new \DateTime();

// 		$year = substr($stringDateYmd, 0,4);
// 		$month = substr($stringDateYmd, 5,2);
// 		$day = substr($stringDateYmd, 8,2);
		
// 		$dateTime->setDate($year, $month, $day);

		if(is_null($stringDate))
			return null;

		$stringDate.=" 00:00:00.000000";
		
		return date_create_from_format(static::$formatDateTime, $stringDate);

	}
	
}

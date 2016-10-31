<?php
namespace ttm\util;

class UtilDate {
		
	public static function dateToString(\DateTime $dateTime ,string $formatDate = "Y-m-d"):string {
		if(is_null($dateTime)) 
			return null;
			
		return $dateTime->format($formatDate);
	}

	public static function stringToDate(string $stringDate, string $formatDateTime = "Y-m-d H:i:s.u"):\DateTime {

		if(is_null($stringDate))
			return null;

		$stringDate.=" 00:00:00.000000";
		
		return date_create_from_format($formatDateTime, $stringDate);
	}
}

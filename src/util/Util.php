<?php
namespace ttm\util;

class Util {
	
	public static function doMethodName($propertyName, $prefix):string {
		$firstLetter = substr($propertyName,0,1);
		$wordRest = substr($propertyName,1);
	
		return $prefix.strtoupper($firstLetter).$wordRest;
	}
}

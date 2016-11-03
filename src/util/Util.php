<?php
namespace ttm\util;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * Util - Utilitary class to centralize the genereal treatement on the system
 * (miscelaneus resusable methods)
 *
 * @package ttm-core-php
 * @namespace ttm\util
 * @version 1.0
 */
class Util {
	
	/**
	 * @method doMethodName - generates name methods for reflection invocating (getter/setter)
	 *
	 * @param $propertyName - attribuite name for generating method name
	 * @param $prefix - prefix get, set or is
	 *
	 * @return string method name
	 *
	 * @access public
	 * @static
	 * @since 1.0
	 */
	public static function doMethodName($propertyName, $prefix):string {
		$firstLetter = substr($propertyName,0,1);
		$wordRest = substr($propertyName,1);
	
		return $prefix.strtoupper($firstLetter).$wordRest;
	}
}

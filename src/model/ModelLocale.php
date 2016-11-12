<?php
namespace ttm\model;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * Model - Determines the entity type to the model classes of the system, encapsulating
 * the commons attributes and treatments
 * 
 * @package ttm-core-php
 * @namespace ttm\model
 * @abstract
 * @version 1.0
 */
abstract class ModelLocale extends Model {

	public abstract function getLocaleStrings();

	public abstract function setLocaleStrings($localeStrings);
	
}

?>
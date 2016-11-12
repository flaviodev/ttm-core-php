<?php
namespace ttm\model;

/**
 * @author flaviodev - Flávio de Souza TTM/ITS - fdsdev@gmail.com
 *
 * ModelLocale - Determines the entity type to the model classes of the system, encapsulating
 * the commons attributes and treatments, using a locale for internationalization, this model
 * will be used by 'ModelStrings'
 * 
 * @package ttm-core-php
 * @namespace ttm\model
 * @abstract
 * @version 1.0
 */
abstract class ModelLocale extends Model {
	/**
	 * @method getLocale - return the entity locale of your content language 
	 * (of mapped attribute on concrete class) ex: en-us
	 *
	 * @return entity locale (part of composite primary key)
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public abstract function getLocale();

	/**
	 * @method setLocale - set the entity locale (of mapped attribute on concrete class)
	 *
	 * @param $locale - entity locale (part of composite primary key)
	 *
	 * @access public
	 * @abstract
	 * @since 1.0
	 */
	public abstract function setLocale($locale);
}

?>
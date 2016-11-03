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
abstract class Model {
	
	/**
	 * @method getId - return the entity id (of mapped attribute on concrete class)
	 *
	 * @return entity id (primary key)
	 *
	 * @abstract
	 * @access public
	 * @since 1.0
	 */
	public abstract function getId();

	/**
	 * @method setId - set the entity id (of mapped attribute on concrete class)
	 *
	 * @param $id - entity id (primary key)
	 *
	 * @abstract
	 * @access public
	 * @since 1.0
	 */
	public abstract function setId($id);
}

?>
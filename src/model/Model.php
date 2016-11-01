<?php
namespace ttm\model;

abstract class Model {
	public abstract function getId();
	
	public abstract function setId($id);
}

?>
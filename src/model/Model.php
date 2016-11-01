<?php
namespace ttm\model;

abstract class ObjectBOIntId extends ObjectBO {
	
	public abstract function getId():int;
	
	public abstract function setId(int $id);
}

?>
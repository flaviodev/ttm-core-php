<?php
namespace ttm\model;

abstract class ObjectBOCompKey extends ObjectBO {
	
	public abstract function getId():CompositeKey;
	
	public abstract function setId(CompositeKey $id);
}

?>
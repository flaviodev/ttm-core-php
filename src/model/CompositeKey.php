<?php
namespace ttm\model;

class CompositeKey {
	private $keys;
	
	public function __construct(array $keys) {
		$this->keys=$keys;
	}
	
	public function getKeys():array {
		return $this->keys;
	}
	
	public function setKeys(array $keys) {
		$this->keys = $keys;
	}
	
}

?>
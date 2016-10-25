<?php
namespace ttm\dao;

use ttm\model\ObjectBO;

interface Dao {
	public function find($entityName,$key):ObjectBO;
	
	public function findAll($entityName):array;
	
	public function update(ObjectBO $entity);
	
	public function remove(ObjectBO $entity);
	
	public function create(ObjectBO $entity):ObjectBO;
	
}
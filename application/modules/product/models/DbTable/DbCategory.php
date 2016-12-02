<?php

class Product_Model_DbTable_DbCategory extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_category";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["cat_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
				'prefix'		=>	$data["prifix"],
		);
		$this->_name = "tb_category";
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["cat_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
				'prefix'		=>	$data["prifix"],
		);
		$this->_name = "tb_category";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	
	//Insert Popup=============================
	public function addNew($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["cat_name"],
				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
		);
		$this->_name = "tb_category";
		return $this->insert($arr);
	}
	
	public function getAllCategory(){
		$db = $this->getAdapter();
		$sql = "SELECT c.id,c.`name`,c.`parent_id`,
				(SELECT par_id.name FROM tb_category AS par_id WHERE par_id.id = c.parent_id  ) AS parent_name,c.`remark`,c.`status` 
				FROM `tb_category` AS c WHERE c.`status` =1";
		return $db->fetchAll($sql);
	}
	 
    function getParent($par_id){
    	$db = $this->getAdapter();
    	$sql = "SELECT  c.`name` FROM `tb_category` AS c WHERE c.`parent_id`=$par_id";
    	return $db->fetchAll($sql);
    }
	
	public function getCategory($id){
		$db = $this->getAdapter();
		$sql = "SELECT c.id,c.`name`,c.`parent_id`,c.`remark`,c.`status`,c.`prefix` FROM `tb_category` AS c WHERE c.`id`= $id";
		return $db->fetchRow($sql);
	}
}
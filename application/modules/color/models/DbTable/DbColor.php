<?php

class Color_Model_DbTable_DbColor extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_color";
	
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
	public function add($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["name"],
// 				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
				'user_id'		=>	$this->getUserId(),
		);
		$this->_name = "tb_color";
		$this->insert($arr);
	}
	public function edit($data){
		$db = $this->getAdapter();
		$arr = array(
				'name'			=>	$data["name"],
// 				'parent_id'		=>	$data["parent"],
				'date'			=>	new Zend_Date(),
				'status'		=>	$data["status"],
				'remark'		=>	$data["remark"],
				'user_id'		=>	$this->getUserId(),
		);
		$this->_name = "tb_color";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	
	public function getAllColor(){
		$db = $this->getAdapter();
		$sql = "SELECT m.id,m.`name`,m.`status`,m.`remark` FROM `tb_color` AS m ";
		return $db->fetchAll($sql);
	}
	
	public function getColor($id){
		$db = $this->getAdapter();
		$sql = "SELECT m.id,m.`name`,m.`status`,m.`remark` FROM `tb_color` AS m  WHERE m.`id`= $id";
		return $db->fetchRow($sql);
	}
}
<?php

class Size_Model_DbTable_DbSize extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_size";
	
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
		$this->_name = "tb_size";
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
		$this->_name = "tb_size";
		$where = $db->quoteInto("id=?", $data["id"]);
		$this->update($arr, $where);
	}
	
	public function getAllSize(){
		$db = $this->getAdapter();
		$sql = "SELECT m.id,m.`name`,m.`status`,m.`remark` FROM `tb_size` AS m ";
		return $db->fetchAll($sql);
	}
	
	public function getSize($id){
		$db = $this->getAdapter();
		$sql = "SELECT m.id,m.`name`,m.`status`,m.`remark` FROM `tb_size` AS m  WHERE m.`id`= $id";
		return $db->fetchRow($sql);
	}
}
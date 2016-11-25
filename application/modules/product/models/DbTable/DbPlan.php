<?php
class Product_Model_DbTable_DbPlan extends Zend_Db_Table_Abstract
{
    protected $_name = 'tb_plan';
    public function setName($name)
    {
    	$this->_name=$name;
    }
    public function add($_data){
	  $this->_name='tb_plan';
	  $_arr = array(
	    'type'=>$_data['type'],
	    'name'=>$_data['name'],
	  	'address'=>$_data['address'],
	  	'status'=>$_data['status'],
		'date'	=> date('Y-m-d'),
	    );
	  $this->insert($_arr);// insert data into database
 }//add product
 function getUser(){
 	$db = $this->getAdapter();
 	$sql ="SELECT u.id,u.`first_name` FROM `rms_users` AS u";
 	return $db->fetchAll($sql);
 }
 public function updateplanById($_data){
 	$_arr = array(
 				
 			'type'=>$_data['type'],
			'name'=>$_data['name'],
			'address'=>$_data['address'],
			'status'=>$_data['status'],
			'date'	=> date('Y-m-d'),
 	);
 	$where = "id =".$_data['id'];
 	
 	$this->update($_arr, $where);
 
 }// updateproductById
 
 public function getplanById($id)
 {
 	$db = $this->getAdapter();
 	$sql = " SELECT id,type,name,address,status FROM`tb_plan` WHERE id = $id";
 	return $db->fetchRow($sql);
 }// getproduct1ById
 
  public function getPlan($search){
  	$db = $this->getAdapter();
  	$sql = "SELECT id,
  (SELECT  name FROM `tb_plan_type` WHERE `tb_plan_type`.`id` = `tb_plan`.`type`) AS `type` ,
  name,
  address,
  status
FROM
  `tb_plan` 
WHERE 1";
  	$where ='';
  	if(!empty($search["adv_search"])){
  		$s_where=array();
  		$s_search = addslashes(trim($search['adv_search']));
  		$s_where[]= " name LIKE '%{$s_search}%'";
  		$where.=' AND ('.implode(' OR ', $s_where).')';
  		
  	}
  	if(!empty($search["status"])){
  		$where.=' AND status='.$search["status"];
  	}
  	if(!empty($search["typecate"])){
  		$where.=' AND type='.$search["typecate"];
  	}
  	
  	//echo $sql.$where;
  	return $db->fetchAll($sql.$where);
  }
   
  function getType(){
  	$db = $this->getAdapter();
  	$sql ="SELECT id,name FROM `tb_plan_type`";
  	return $db->fetchAll($sql);
  } 
  
	function getStatus(){
		$db = $this->getAdapter();
		$sql="SELECT v.`key_code`,v.`name_en` FROM `tb_view` AS v WHERE v.`type`=5";
		return $db->fetchAll($sql);
	} 
	 
	
    
}
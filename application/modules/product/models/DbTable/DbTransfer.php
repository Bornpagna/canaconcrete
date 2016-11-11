<?php

class Product_Model_DbTable_DbTransfer extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_product_transfer";
	public function setName($name)
	{
		$this->_name=$name;
	}
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	function getPrefix(){
		$id = $this->GetuserInfo();
		$db = $this->getAdapter();
		$sql = "SELECT s.prefix FROM `tb_sublocation` AS s WHERE s.`id` =".$id["branch_id"];
		//echo $sql;
		return $db->fetchOne($sql);
	}
	public function getTransferNo(){
		$db =$this->getAdapter();
		$prefix = $this->getPrefix();
		$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "TR";
		for($i = $acc_no;$i<4;$i++){
			$pre.='0';
		}
		return $prefix.$pre.$new_acc_no;
	}
	
	public function getRequestTransferNo(){
		$location = $this->GetuserInfo();
		$db =$this->getAdapter();
		$prefix = $this->getPrefix();
		$sql="SELECT id FROM `tb_request_transfer` AS t WHERE t.`cur_location`=".$location["branch_id"]." ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = "RQTR";
		for($i = $acc_no;$i<4;$i++){
			$pre.='0';
		}
		return $prefix.$pre.$new_acc_no;
	}
	function getLocation(){
		$id = $this->GetuserInfo();
		$db = $this->getAdapter();
		$sql = "SELECT s.id,s.`name` FROM `tb_sublocation` AS s WHERE s.`status`=1 AND s.`id` !=".$id["branch_id"];
		//echo $sql;
		return $db->fetchAll($sql);
	}
	function getTransfer($data){
		$tran_date = $data["tran_date"];
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.*,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=p.`cur_location`) AS cur_location,
				  (SELECT s.name FROM `tb_sublocation` AS s WHERE s.id=p.`tran_location`) AS tran_location,
  				  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=p.`user_mod`) AS user_tran
				FROM
				  `tb_product_transfer` AS p 
				WHERE 1 AND p.`date`<='$tran_date'";
		$where = '';
	  	if($data["tran_num"]!=""){
	  		$s_where=array();
	  		$s_search = addslashes(trim($data['tran_num']));
	  		$s_where[]= " p.tran_no LIKE '%{$s_search}%'";
	  		//$s_where[]=" p.user_mod LIKE '%{$s_search}%'";
	  		$s_where[]= " p.date LIKE '%{$s_search}%'";
	  		$s_where[]= " p.remark LIKE '%{$s_search}%'";
	  		//$s_where[]= " cate LIKE '%{$s_search}%'";
	  		$where.=' AND ('.implode(' OR ', $s_where).')';
	  	}
	  	if($data["type"]!=""){
	  		$where.=' AND p.`type`='.$data["type"];
	  	}
	  	if($data["status"]!=""){
	  		$where.=' AND p.status='.$data["status"];
	  	}
	  	if($data["to_loc"]!=""){
	  		$where.=' AND p.tran_location='.$data["to_loc"];
	  	}
  		//echo $sql.$where;
		return $db->fetchAll($sql.$where);
	}
	function getTransferById($id){
		$db = $this->getAdapter();
		$sql="SELECT p.* FROM `tb_product_transfer` AS p WHERE p.id=$id";
		return $db->fetchRow($sql);
	}
	function getTransferDettail($id){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$db = $this->getAdapter();
		$loc_id = $result["location_id"];
		$sql="SELECT 
				  t.* ,
				  (SELECT p.`item_name` FROM `tb_product` AS p WHERE p.id=t.`pro_id` LIMIT 1) AS item_name,
				  (SELECT p.`qty` FROM `tb_prolocation` AS p WHERE p.pro_id=t.`pro_id` AND p.`location_id`=$loc_id LIMIT 1) AS qty_loc
				FROM
				  `tb_transfer_item` AS t 
				WHERE t.`tran_id` = $id";
		return $db->fetchAll($sql);
	}
	
	function getCurrentTransfer($id){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$db = $this->getAdapter();
		$loc_id = $result["location_id"];
		$sql="SELECT 
				  t.`pro_id`,
				  t.`qty`,
				  p.`cur_location`,
				  p.`tran_location`,
				  p.`type`
				FROM
				  `tb_transfer_item` AS t ,
				  `tb_product_transfer` AS p
				WHERE t.`tran_id` = p.`id`AND p.id=$id";
		return $db->fetchAll($sql);
	}
	public function add($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			
			$arr = array(
					'tran_no'		=>	$data["tran_num"],
					'cur_location'	=>	$result["location_id"],
					'tran_location'	=>	$data["to_loc"],
					'type'			=>	$data["type"],
					'date'			=>	$data["tran_date"],
					'date_mod'		=>	new Zend_Date(),
					'remark'		=>	$data["remark"],
					'user_mod'		=>	$result["user_id"],
			);
			$id = $this->insert($arr);
			
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr_ti = array(
							'tran_id'		=>	$id,
							'pro_id'		=>	$data["pro_id_".$i],
							'qty'			=>	$data["qty_tran_".$i],
							'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_transfer_item";
					$this->insert($arr_ti);
	
					$rs_from = $this->getProductExist($data["pro_id_".$i],$result["branch_id"]);
					$rs_to = $this->getProductExist($data["pro_id_".$i],$data["to_loc"]);
	
					//update stock recieve branch
					//echo $rs_to["qty"]+$data["qty_tran_".$i];
					if(!empty($rs_to)){
						$arr_to = array(
								'qty'	=>	$rs_to["qty"]+$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["to_loc"]);
						$this->update($arr_to, $where);
					}else{
						$arr_to = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["to_loc"],
								'qty'				=>	$data["qty_tran_".$i],
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$result["user_id"],
								'last_mod_date'		=>	new Zend_Date(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_to);
					}
					
					/// Update transfer branch
					if(!empty($rs_from)){
						$arr_fo = array(
							'qty'	=>	$rs_from["qty"]-$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$result["branch_id"]);
						$this->update($arr_fo, $where);
					}
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	public function edit($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
				
			$arr = array(
					//'tran_no'		=>	$data["tran_num"],
					//'cur_location'	=>	$result["location_id"],
					'tran_location'	=>	$data["to_loc"],
					'type'			=>	$data["type"],
					'date'			=>	$data["tran_date"],
					'date_mod'		=>	new Zend_Date(),
					'remark'		=>	$data["remark"],
					'user_mod'		=>	$result["user_id"],
			);
			$where = $db->quoteInto("id=?", $data["id"]);
			$this->update($arr, $where);
			
			
			// Update Tb_prolocation has Transfered to old qty  to old Qty
			$rs_detail = $this->getCurrentTransfer($data["id"]);
			if(!empty($rs_detail)){
				foreach ($rs_detail as $rs){
					//Update Prolocation has transfer to
					$arr_up_to = array(
						//'' 		=>		$
					);
					
					//Update Prolocation has transfer to
					$arr_up_fr = array(
					
					);
				}
			}
				
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr_ti = array(
							'tran_id'		=>	$id,
							'pro_id'		=>	$data["pro_id_".$i],
							'qty'			=>	$data["qty_tran_".$i],
							'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_transfer_item";
					$this->insert($arr_ti);
	
					$rs_from = $this->getProductExist($data["pro_id_".$i],$result["branch_id"]);
					$rs_to = $this->getProductExist($data["pro_id_".$i],$data["to_loc"]);
	
					//update stock recieve branch
					//echo $rs_to["qty"]+$data["qty_tran_".$i];
					if(!empty($rs_to)){
						$arr_to = array(
								'qty'	=>	$rs_to["qty"]+$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$data["to_loc"]);
						$this->update($arr_to, $where);
					}else{
						$arr_to = array(
								'pro_id'			=>	$data["pro_id_".$i],
								'location_id'		=>	$data["to_loc"],
								'qty'				=>	$data["qty_tran_".$i],
								'qty_warning'		=>	0,
								'last_mod_userid'	=>	$result["user_id"],
								'last_mod_date'		=>	new Zend_Date(),
						);
						$this->_name="tb_prolocation";
						$this->insert($arr_to);
					}
						
					/// Update transfer branch
					if(!empty($rs_from)){
						$arr_fo = array(
								'qty'	=>	$rs_from["qty"]-$data["qty_tran_".$i],
						);
						$this->_name="tb_prolocation";
						$where = array('pro_id=?'=>$data["pro_id_".$i],"location_id=?"=>$result["branch_id"]);
						$this->update($arr_fo, $where);
					}
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	function getProductName(){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		
		$db = $this->getAdapter();
		$sql = "SELECT 
				  p.`id`,
				  p.`item_name` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl 
				WHERE p.`id` = pl.`pro_id` ";
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		return $db->fetchAll($sql.$location);
	}
	function getProductById($id){
		$db_globle = new Application_Model_DbTable_DbGlobal();
		$db = $this->getAdapter();
		$sql = "SELECT
				  p.`id`,
				  p.`item_name` ,
				  p.`item_code`,
				  (SELECT b.name FROM `tb_brand` AS b WHERE b.id=p.`brand_id`) AS brand,
				  (SELECT c.name FROM `tb_category` AS c WHERE c.id = p.`cate_id`) AS category,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`model_id`) AS model,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`color_id`) AS color,
				  (SELECT v.name_kh FROM `tb_view` AS v WHERE v.id=p.`size_id`) AS size,
				  pl.`qty`
				FROM
				  `tb_product` AS p,
				  `tb_prolocation` AS pl
				WHERE p.`id` = pl.`pro_id` AND p.`id`=$id ";
		$location = $db_globle->getAccessPermission('pl.`location_id`');
		//echo $sql.$location;
		return $db->fetchRow($sql.$location);
	}
	
	public function addRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		$date =new Zend_Date();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			
			$arr = array(
					'tran_no'		=>	$data["tran_num"],
					'cur_location'	=>	$result["branch_id"],
					'tran_location'	=>	$data["to_loc"],
					//'type'			=>	$data["type"],
					'date'			=>	$data["tran_date"],
					'date_tran'		=>	$date->get('MM/d/Y'),
					'remark'		=>	$data["remark"],
					'user_id'		=>	$result["user_id"],
			);
			$this->_name="tb_request_transfer";
			$id = $this->insert($arr);
			
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr_ti = array(
							'tran_id'		=>	$id,
							'pro_id'		=>	$data["pro_id_".$i],
							'qty'			=>	$data["qty_tran_".$i],
							'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_request_transfer_item";
					$this->insert($arr_ti);
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	public function editRequest($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$user_info = new Application_Model_DbTable_DbGetUserInfo();
			$result = $user_info->getUserInfo();
			$arr = array(
					//'tran_no'		=>	$data["tran_num"],
					'cur_location'	=>	$result["branch_id"],
					'tran_location'	=>	$data["to_loc"],
					//'type'			=>	$data["type"],
					'date'			=>	$data["tran_date"],
					'date_tran'		=>	new Zend_Date(),
					'remark'		=>	$data["remark"],
					'user_id'		=>	$result["user_id"],
			);
			$this->_name="tb_request_transfer";
			$id = $this->insert($arr);
			
			if(!empty($data['identity'])){
				$identitys = explode(',',$data['identity']);
				foreach($identitys as $i)
				{
					$arr_ti = array(
							'tran_id'		=>	$id,
							'pro_id'		=>	$data["pro_id_".$i],
							'qty'			=>	$data["qty_tran_".$i],
							'remark'		=>	$data["remark_".$i],
					);
					$this->_name="tb_request_transfer_item";
					$this->insert($arr_ti);
				}
			}
			$db->commit();
		}catch (Exception $e){
			$db->rollBack();
			Application_Model_DbTable_DbUserLog::writeMessageError($e);
			echo $e->getMessage();exit();
		}
	}
	
	function getRequestTransfer($data){
		$tran_date = $data["tran_date"];
		$db = $this->getAdapter();
		$sql = "SELECT 
				  rt.`tran_no`,
				  rt.`date`,
				  rt.`date_tran`,
				  (SELECT s.`name` FROM `tb_sublocation` AS s WHERE s.`id`=rt.`tran_location`) AS re_tran ,
				  rt.`remark`,
				  rt.`is_approved`,
				  rt.`approved_by`,
				  (SELECT u.`fullname` FROM  `tb_acl_user` AS u WHERE u.`user_id`=rt.`user_id`) AS `user`
				FROM
				  `tb_request_transfer` AS rt ";
		$where = '';
	  	if($data["tran_num"]!=""){
	  		$s_where=array();
	  		$s_search = addslashes(trim($data['tran_num']));
	  		$s_where[]= " p.tran_no LIKE '%{$s_search}%'";
	  		//$s_where[]=" p.user_mod LIKE '%{$s_search}%'";
	  		$s_where[]= " p.date LIKE '%{$s_search}%'";
	  		$s_where[]= " p.remark LIKE '%{$s_search}%'";
	  		//$s_where[]= " cate LIKE '%{$s_search}%'";
	  		$where.=' AND ('.implode(' OR ', $s_where).')';
	  	}
	  	if($data["type"]!=""){
	  		$where.=' AND p.`type`='.$data["type"];
	  	}
	  	if($data["status"]!=""){
	  		$where.=' AND p.status='.$data["status"];
	  	}
	  	if($data["to_loc"]!=""){
	  		$where.=' AND p.tran_location='.$data["to_loc"];
	  	}
  		//echo $sql.$where;
		return $db->fetchAll($sql.$where);
	}
	
}
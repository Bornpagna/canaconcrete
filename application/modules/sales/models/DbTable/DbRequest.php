<?php

class Sales_Model_DbTable_DbRequest extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_sales_order";
	
	function getPlan(){
		$db= $this->getAdapter();
			$sql="SELECT p.`id`,p.`name` FROM `tb_plan` AS p WHERE p.`status`=1";
			return $db->fetchAll($sql);
	}
	function getPlanAddr($id){
		$db= $this->getAdapter();
		$sql="SELECT p.`address` FROM `tb_plan` AS p WHERE p.id=$id";
		return $db->fetchOne($sql);
	}
	
	function getRequestCode($id){
		$db= $this->getAdapter();
		$sql = "SELECT s.`prefix` FROM `tb_sublocation` AS s WHERE s.id=$id";
		$prefix = $db->fetchOne($sql);
		
		$sql="SELECT s.id FROM `tb_sales_order` AS s WHERE s.`type`=2 ORDER BY s.`id` DESC LIMIT 1";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = $prefix."DIBP";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	function getAllRequestOrder($search){
			$db= $this->getAdapter();
			$sql="SELECT 
					  s.id,
					  (SELECT sl.`name` FROM `tb_sublocation` AS sl WHERE sl.`id`=s.`branch_id`) AS location,
					  s.`sale_no`,
					  s.`request_name`,
					  s.`position`,
					  (SELECT `name` FROM `tb_plan` AS p WHERE p.id=s.`plan_id`) AS plan,
					  s.`date_sold`,
					  s.`all_total` ,
					  (SELECT u.`fullname` FROM `tb_acl_user` AS u WHERE u.`user_id`=s.`user_mod`) AS `user`,
					  (SELECT v.name_en FROM `tb_view` AS v WHERE v.type=5 AND v.key_code=s.`status`) AS `status`
					FROM
					  `tb_sales_order` AS s 
					WHERE s.`type` = 2 ";
			
			$from_date =(empty($search['start_date']))? '1': " date_sold >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " date_sold <= '".$search['end_date']." 23:59:59'";
			$where = " AND ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " sale_no LIKE '%{$s_search}%'";
				$s_where[] = " net_total LIKE '%{$s_search}%'";
				$s_where[] = " paid LIKE '%{$s_search}%'";
				$s_where[] = " balance LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch_id']>0){
				$where .= " AND branch_id = ".$search['branch_id'];
			}
			if($search['customer_id']>0){
				$where .= " AND customer_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	public function addRequestOrder($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
			$so = $dbc->getSalesNumber($data["branch_id"]);

			$info_purchase_order=array(
					"plan_id"  		 => 	$data['plan'],
					"request_name"	=>		$data["request_by"],
					"position"		=>		$data["requstman_pos"],
					"branch_id"     => 		$data["branch_id"],
					"sale_no"       => 		$data["apno"],//$data['txt_order'],
					"date_sold"     => 		date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"  => 		$data['saleagent_id'],
					//"payment_method" => 	$data['payment_name'],
					//"currency_id"    => 	$data['currency'],
					"remark"         => 	$data['remark'],
					"all_total"      => 	$data['totalAmoun'],
					"discount_value" => 	$data['dis_value'],
					"discount_real"  => 	$data['global_disc'],
					"net_total"      => 	$data['all_total'],
					//"paid"        	 => 	$data['paid'],
					//"balance"      	 => 	$data['remain'],
					//"tax"			 =>     $data["total_tax"],
					"user_mod"       => 	$GetUserId,
					//'pending_status' =>		2,
					"date"      	 => 	date("Y-m-d"),
					"type"			=>		2
			);
			$this->_name="tb_sales_order";
			$sale_id = $this->insert($info_purchase_order); 
			unset($info_purchase_order);

			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'saleorder_id'	=> 	$sale_id,
						'pro_id'	  	=> 	$data['item_id_'.$i],
						'qty_unit'		=>	$data['qty_unit_'.$i],
						'qty_detail'  	=> 	$data['qty_per_unit_'.$i],
						'qty_order'	  	=> 	$data['qty'.$i],
						'price'		  	=> 	$data['price'.$i],
						'old_price'   	=>  $data['current_qty'.$i],
						//'disc_value'  	=> 	$data['real-value'.$i],//check it
						'sub_total'	  	=> 	$data['total'.$i],
				);
				$this->_name='tb_salesorder_item';
				$this->insert($data_item);
				
				$rows=$this->productLocationInventory($data['item_id_'.$i], $locationid);//check stock product location
					if($rows)
					{
						//if($data["status"]==4 OR $data["status"]==5){
							$datatostock   = array(
									'qty'   			=> 		$rows["qty"]-$data['qty'.$i],
									'last_mod_date'		=>		date("Y-m-d"),
									'last_mod_userid'	=>		$GetUserId
							);
							$this->_name="tb_prolocation";
							$where=" id = ".$rows['id'];
							$this->update($datatostock, $where);
						//}
					}
			 }
			//exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function updateRequestOrder($data)
	{
		$id=$data["id"];
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			$dbc=new Application_Model_DbTable_DbGlobal();
// 			$so = $dbc->getSalesNumber($data["branch_id"]);
			$arr=array(
					"plan_id"  		 => 	$data['plan'],
					"request_name"	=>		$data["request_by"],
					"position"		=>		$data["requstman_pos"],
					"branch_id"     => 		$data["branch_id"],
					"sale_no"       => 		$data["apno"],//$data['txt_order'],
					"date_sold"     => 		date("Y-m-d",strtotime($data['order_date'])),
					"saleagent_id"  => 		$data['saleagent_id'],
					//"payment_method" => 	$data['payment_name'],
					//"currency_id"    => 	$data['currency'],
					"remark"         => 	$data['remark'],
					"all_total"      => 	$data['totalAmoun'],
					"discount_value" => 	$data['dis_value'],
					"discount_real"  => 	$data['global_disc'],
					"net_total"      => 	$data['all_total'],
					//"paid"        	 => 	$data['paid'],
					//"balance"      	 => 	$data['remain'],
					//"tax"			 =>     $data["total_tax"],
					"user_mod"       => 	$GetUserId,
					//'pending_status' =>		2,
					"date"      	 => 	date("Y-m-d"),
					"type"			=>		2
			);

			$this->_name="tb_sales_order";
			$where="id = ".$id;
			$this->update($arr, $where);
			unset($arr);
			
			$row_old_item = $this->getSaleorderItemDetailid($data["id"]);
			if(!empty($row_old_item)){
				foreach($row_old_item AS $rs){
					$sql = "SELECT pl.id,pl.`qty` FROM `tb_prolocation` AS pl WHERE pl.`pro_id`=".$rs["pro_id"]. " AND pl.`location_id` =".$data["old_location"];
					$row = $db->fetchRow($sql);
					
						$arr_old = array(
							'qty'	=> $row["qty"]+$rs["qty_order"],
						);
						$where = $db->quoteInto("id=?",$row["id"]);
						$this->_name = "tb_prolocation";
						$this->update($arr_old,$where);
				}
				
				$this->_name='tb_salesorder_item';
				$where = " saleorder_id =".$id;
				$this->delete($where);
			}
			
			
			
			$ids=explode(',',$data['identity']);
			$locationid=$data['branch_id'];
			foreach ($ids as $i)
			{
				$data_item= array(
						'saleorder_id'=> $data["id"],
						'pro_id'	  => 	$data['item_id_'.$i],
						'qty_unit'=>$data['qty_unit_'.$i],
						'qty_detail'  => 	$data['qty_per_unit_'.$i],
						'qty_order'	  => 	$data['qty'.$i],
						'price'		  => 	$data['price'.$i],
						'old_price'   =>    $data['current_qty'.$i],
						//'disc_value'  => $data['real-value'.$i],//check it
						'sub_total'	  => $data['total'.$i],
				);
				$this->_name='tb_salesorder_item';
				$this->insert($data_item);
				
				$rows=$this->productLocationInventory($data['item_id_'.$i], $locationid);//check stock product location
					if($rows)
					{
						//if($data["status"]==4 OR $data["status"]==5){
							//echo$rows["qty"];
							$datatostock   = array(
									'qty'   			=> 		$rows["qty"]-$data['qty'.$i],
									'last_mod_date'		=>		date("Y-m-d"),
									'last_mod_userid'	=>		$GetUserId
							);
							$this->_name="tb_prolocation";
							$where=" id = ".$rows['id'];
							$this->update($datatostock, $where);
						//}
					}
// 				print_r($data_item);exit();
			}
			/*$this->_name='tb_quoatation_termcondition';
			$where = " term_type=2 AND quoation_id = ".$id;
			$this->delete($where);
			
			$ids=explode(',',$data['identity_term']);
			if(!empty($data['identity_term'])){
				foreach ($ids as $i)
				{
					$data_item= array(
							'quoation_id'=> $sale_id,
							'condition_id'=> $data['termid_'.$i],
							"user_id"   => 	$GetUserId,
							"date"      => 	date("Y-m-d"),
							'term_type'=>2
	
					);
					$this->_name='tb_quoatation_termcondition';
					$this->insert($data_item);
				}
			}*/
			//exit();
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			//Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			//echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function productLocationInventory($pro_id, $location_id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,pro_id,location_id,qty,qty_warning,last_mod_date,last_mod_userid
    	 FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 "; 
    	$row = $db->fetchRow($sql);
    	
    	if(empty($row)){
    		$session_user=new Zend_Session_Namespace('auth');
    		$userName=$session_user->user_name;
    		$GetUserId= $session_user->user_id;
    		
    		$array = array(
    				"pro_id"			=>	$pro_id,
    				"location_id"		=>	$location_id,
    				"qty"				=>	0,
    				"qty_warning"		=>	0,
    				"last_mod_userid"	=>	$GetUserId,
    				"user_id"			=>	$GetUserId,
    				"last_mod_date"		=>	date("Y-m-d")
    				);
    		$this->_name="tb_prolocation";
    		$this->insert($array);
    		
    		$sql="SELECT id,pro_id,location_id,qty,qty_warning,user_id,last_mod_date,last_mod_userid
    		FROM tb_prolocation WHERE pro_id =".$pro_id." AND location_id=".$location_id." LIMIT 1 ";
    		return $row = $db->fetchRow($sql);
    	}else{
    		
    	return $row; 
    	}  	
    }
	
	function getSaleorderItemById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getSaleorderItemDetailid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_salesorder_item` WHERE saleorder_id=$id ";
		return $db->fetchAll($sql);
	}
	function getTermconditionByid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_termcondition` WHERE quoation_id=$id AND term_type=2 ";
		return $db->fetchAll($sql);
	} 
	
	function getProductPriceBytype($product_id){//BY Customer Level and Product id
   	$db = $this->getAdapter();
   	$sql=" SELECT price,pro_id FROM `tb_product_price` WHERE type_id = 
   		(SELECT customer_level FROM `tb_customer` WHERE id=$customer_id limit 1) AND pro_id=$product_id LIMIT 1 ";
   	return $db->fetchRow($sql);
   }
}

<?php

class Sales_Model_DbTable_DbCustomer extends Zend_Db_Table_Abstract
{
	protected $_name = "tb_customer";
	public function setName($name)
	{
		$this->_name=$name;
	}
	
	public function getCustomerCode($id){
		$db = $this->getAdapter();
		$sql = "SELECT s.`prefix` FROM `tb_sublocation` AS s WHERE s.id=$id";
		$prefix = $db->fetchOne($sql);
		
		$sql=" SELECT id FROM $this->_name AS s WHERE s.`branch_id`=$id ORDER BY id DESC LIMIT 1 ";
		$acc_no = $db->fetchOne($sql);
		$new_acc_no= (int)$acc_no+1;
		$acc_no= strlen((int)$acc_no+1);
		$pre = $prefix."CID";
		for($i = $acc_no;$i<5;$i++){
			$pre.='0';
		}
		return $pre.$new_acc_no;
	}
	function getAllCustomer($search){
		$db = $this->getAdapter();
		
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE id=branch_id LIMIT 1) AS branch_name,
		 cust_name,phone,
		(SELECT NAME FROM `tb_price_type` WHERE id=customer_level LIMIT 1) As level,
		 contact_name,contact_phone,address,
		( SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=status LIMIT 1) status,
		( SELECT fullname FROM `tb_acl_user` WHERE tb_acl_user.user_id=user_id LIMIT 1) AS user_name
		 FROM `tb_customer` WHERE cust_name!=''  ";
		
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " cust_name LIKE '%{$s_search}%'";
			$s_where[] = " phone LIKE '%{$s_search}%'";
			$s_where[] = " contact_name LIKE '%{$s_search}%'";
			$s_where[] = " contact_phone LIKE '%{$s_search}%'";
			$s_where[] = " address LIKE '%{$s_search}%'";
			
			$s_where[] = " email LIKE '%{$s_search}%'";
			$s_where[] = " website LIKE '%{$s_search}%'";
			$s_where[] = " remark LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		
		if($search['branch_id']>0){
			$where .= " AND branch_id = ".$search['branch_id'];
		}
		if($search['customer_id']>0){
			$where .= " AND id = ".$search['customer_id'];
		}
		if($search['level']>0){
			$where .= " AND customer_level = ".$search['level'];
		}
		$order=" ORDER BY id DESC ";
// 		echo $sql.$where.$order;
		return $db->fetchAll($sql.$where.$order);
	}
	public function addCustomer($post)
	{
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$db=$this->getAdapter();
		$data=array(
 				'cu_code'		=> $post['cu_code'],
				'cust_name'		=> $post['txt_name'],
				'phone'			=> $post['txt_phone'],
				'contact_name'	=> $post['txt_contact_name'],//test
				'contact_phone'	=> $post['contact_phone'],//test
				'address'		=> $post['txt_address'],
				'province_id'=> $post['province'],
				'fax'			=> $post['txt_fax'],
				'email'			=> $post['txt_mail'],
				'website'		=> $post['txt_website'],//test
				'add_remark'	=>	$post['remark'],
				'user_id'		=> $GetUserId,
				'date'			=> date("Y-m-d"),
				'branch_id'		=> $post['branch_id'],
				'customer_level'=> $post['customer_level'],
				'cu_type'		=>	$post["customer_type"],
				'credit_limit'	=>	$post["credit_limit"],
				'credit_team'	=>	$post["credit_tearm"],
		);
		
		$this->insert($data);
	}
	public function updateCustomer($post){
		$session_user=new Zend_Session_Namespace('auth');
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
		$db = $this->getAdapter();
		$data=array(
				//'cu_code'		=> $post['cu_code'],
				'cust_name'		=> $post['txt_name'],
				'phone'			=> $post['txt_phone'],
				'contact_name'	=> $post['txt_contact_name'],//test
				'contact_phone'	=> $post['contact_phone'],//test
				'address'		=> $post['txt_address'],
				'province_id'=> $post['province'],
				'fax'			=> $post['txt_fax'],
				'email'			=> $post['txt_mail'],
				'website'		=> $post['txt_website'],//test
				'add_remark'	=>	$post['remark'],
				'user_id'		=> $GetUserId,
				'date'			=> date("Y-m-d"),
				'branch_id'		=> $post['branch_id'],
				'customer_level'=> $post['customer_level'],
				'cu_type'		=>	$post["customer_type"],
				'credit_limit'	=>	$post["credit_limit"],
				'credit_team'	=>	$post["credit_tearm"],
		);
		$where=$this->getAdapter()->quoteInto('id=?',$post["id"]);
		$this->_name="tb_customer";
		$this->update($data,$where);
	}
	//for add new customer from sales
	final function addNewCustomer($post){
		$session_user=new Zend_Session_Namespace('auth');
		$db = new Application_Model_DbTable_DbGlobal();
		$userName=$session_user->user_name;
		$GetUserId= $session_user->user_id;
			$data=array(
 				//'cu_code'		=> $post['cu_code'],
				'cust_name'		=> $post['txt_name'],
				'phone'			=> $post['txt_phone'],
				'contact_name'	=> $post['txt_contact_name'],//test
				'contact_phone'	=> $post['contact_phone'],//test
				'address'		=> $post['txt_address'],
// 				'fax'			=> $post['txt_fax'],
				'email'			=> $post['txt_mail'],
// 				'website'		=> $post['txt_website'],//test
// 				'add_remark'	=>	$post['remark'],
				'user_id'		=> $GetUserId,
				'date'			=> date("Y-m-d"),
				'branch_id'		=> $post['branch_id'],
				'customer_level'=> $post['customer_level'],
				'cu_type'		=>	$post["customer_type"],
				'credit_limit'	=>	$post["credit_limit"],
				'credit_team'	=>	$post["credit_tearm"],
		);
// 		$result=$db->addRecord($data, "tb_customer");

		return $this->insert($data);;	
	}
}
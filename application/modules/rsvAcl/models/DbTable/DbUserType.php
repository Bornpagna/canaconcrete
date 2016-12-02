<?php

class Rsvacl_Model_DbTable_DbUserType extends Zend_Db_Table_Abstract
{

    protected $_name = 'tb_acl_user_type';
	//function for getting record user_type by user_type_id
	public function getUserType($user_id)
	{
		$select=$this->select();		
		$select->where('user_type_id=?',$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row->toArray();
	}
	//get user name
	public function getUserTypeName($user_id)
	{
		$select=$this->select();
		$select->from($this,'user_type')
			->where("user_type_id=?",$user_id);
		$row=$this->fetchRow($select);
		if(!$row) return null; 
		return $row['user_type'];
	}
	//get infomation of user
	public function getUserTypeInfo($sql)
	{
		$db=$this->getAdapter();
  		$stm=$db->query($sql);
  		$row=$stm->fetchAll();
  		if(!$row) return NULL;
  		return $row;
	}
	//function get user id from database
	public function getUserTypeID($username)
	{
		$select=$this->select();
			$select->from($this,'user_type_id')
			->where('user_type=?',$username);
		$row=$this->fetchRow($select);
		if(!$row) return NULL;
		return $row['user_type_id'];
	}
	//function retrieve record users by column 
	public function getUsers($column)
	{		
		$sql='user_id not in(select user_id from pdbs_acl) AND status=1 ';	
		$select=$this->select();
		$select->from($this,$column)
			   ->where($sql);
		$row=$this->fetchAll($select);
		if(!$row) return NULL;		
		return $row->toArray();
	}
	//function check user have exist
	public function isUserTypeExist($username)
	{
		$select=$this->select();
		$select->from($this,'user_type')
			->where("user_type=?",$username);
		$row=$this->fetchRow($select);
		if(!$row) return false;
		return true;
	}
	//function check id number have exist
	public function isIdNubmerExist($id_number)
	{
		$select=$this->select();
		$select->from($this,'id_number')
			->where("id_number=?",$id_number);
		$row=$this->fetchRow($select);
		if(!$row) return false;
		return true;
	}
	//add user
	public function insertUserType($arr)
	{
		$data=array(); 
		/*foreach($arr as $key=>$value){
     		if(!($key=='confirm_password' ||
     			 $key=='password' ||
     		     $key=='user_level_id' ||
     		     substr($key, 0,11)=='division_id' ||
     		     $key=='save'
     		  ) 	 
     		){     		
     			$data[$key]=$value;
     		}     	
     	}	*/
		$data['user_type']=$arr['user_type'];  
		$data['parent_id']=$arr['parent_id'];  	
     	$data['status']='1';
     	//print_r($data);exit;
    	return $this->insert($data); 
	}	
	//update user
	public function updateUserType($arr,$user_type_id)
	{
		//print_r($arr); exit;
		$data=array(); 	
		//Sophen commented on 17 May 2012   	
    /* 	foreach($arr as $key=>$value){
     		if(!($key=='confirm_password' ||
     			 $key=='password' ||
     		     $key=='user_level_id' ||
     		     substr($key, 0,11)=='division_id' ||
     		     $key=='save'
     		  ) 	 
     		){     		
     			$data[$key]=$value;
     			
     		}     
     		
     	}*/	
		//Sophen add here
		$data['user_type']=$arr['user_type'];   
		$data['parent_id']=$arr['parent_id']; 	
    	$where=$this->getAdapter()->quoteInto('user_type_id=?',$user_type_id);
		$this->update($data,$where); 
	}
	
	public function updateUserTypeAccess($arr,$user_type_id)
	{
		//print_r($arr); exit;
		$data=array(); 	  		
        $data['user_type']=$arr;	
        //echo $data['user_type'].$user_type_id; exit;
    	$where=$this->getAdapter()->quoteInto('user_type_id=?',$user_type_id);
		$this->update($data,$where); 
	}
	//function dupdate field status user to force use become inaction
	public function inactiveUser($user_id)
	{
		$data=array('status'=>0);
		$where=$this->getAdapter()->quoteInto('user_id=?',$user_id);
		$this->update($data,$where);
	}
	function  getUserProfileById($id){
		$db=$this->getAdapter();
		$sql="SELECT  u.user_id,u.fullname,u.username,u.email,u.photo,
				        ud.pob,ud.dob,ud.phone,ud.address,ud.position,ud.decription
				FROM tb_acl_user AS u,tb_acl_user_detail AS ud 
				WHERE u.user_id=ud.user_id AND u.user_id=$id";
		return $db->fetchRow($sql);
	}
	
	//update user profile 
	public function updateUser($arr)
	{
		//check update password
		$id_user=$arr['id'];
		if(!empty($arr['old_password']) && !empty($arr['password']) && !empty($arr['confirm_password'])){
			$arr['password']=md5($arr['password']);
			$arr['confirm_password']=md5($arr['confirm_password']);
			
			$arr=array(
					"password"			=>	$arr["password"],
					"confirm_pass"		=>	$arr["confirm_password"],
			);
			$this->_name="tb_acl_user";
			$where=$this->getAdapter()->quoteInto('user_id=?',$id_user);
			$this->update($arr, $where);
		}else if(!empty($arr['olde_user_name'])){
			
			$photoname = str_replace(" ", "_", $arr['olde_user_name']).rand(). '.jpg';
			$upload = new Zend_File_Transfer();
			$upload->addFilter('Rename',
					array('target' => PUBLIC_PATH . '/images/'. $photoname, 'overwrite' => true) ,'pic');
			$receive = $upload->receive();
			if($receive)
			{
				$arr['photo'] = $photoname;
			}
			else{
				$arr['photo']=$arr['old_pic'];
			}
			$data = array(
					"photo"		=>	$arr['photo'],
			);
			$where=$this->getAdapter()->quoteInto('user_id=?',$arr['id']);
			$this->_name="tb_acl_user";
			$id=$this->update($data, $where);
			
		}else{
			try{
				$db=$this->getAdapter();
				$db->beginTransaction();
				$data = array(
						"fullname"		=>	$arr["full_name"],
						"username"		=>	$arr["user_name"],
						"email"			=>	$arr["email"],
						"created_date"	=>	date("Y-m-d H:i:s"),
						"modified_date" =>  date("Y-m-d H:i:s")
				);
				$where=$this->getAdapter()->quoteInto('user_id=?',$arr['id']);
				$this->_name="tb_acl_user";
				$id=$this->update($data, $where);
				///inset to tb_acl_user_detail
				$data = array(
						"user_id"		=>  $arr['id'],
						"phone"			=>	$arr["phone"],
						"pob"			=>	$arr['pob'],
						"dob"			=>	$arr["dob"],
						"email"			=>	$arr["email"],
						"decription"			=>	$arr["descript"],
				);
				$this->_name="tb_acl_user_detail";
				$where="user_id=".$arr['id'];
				$this->_name="tb_acl_user_detail";
				$this->update($data,$where);
				$db->commit();
				//exit();
			}
			catch (Exception $e){
				$db->rollBack();
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
	}
}


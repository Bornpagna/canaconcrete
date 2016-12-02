<?php 
class Product_Form_FrmCategory extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
	public function cat($data=null){
		$db = new Category_Model_DbTable_DbCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$name = new Zend_Form_Element_Text('cat_name');
		$name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$db = new Category_Model_DbTable_DbCategory();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$prifix = new Zend_Form_Element_Text('prifix');
		$prifix->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		 
		$parent = new Zend_Form_Element_Select("parent");
		$parent->setAttribs(array(
				'class'=>'form-control',
		));
		$opt = array(''=>$tr->translate("SEELECT_CATEGORY"));
		$row_cate = $db->getAllCategory();
		if(!empty($row_cate)){
			foreach ($row_cate as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$parent->setMultiOptions($opt);
		
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		
		$remark = new Zend_Form_Element_Text('remark');
		$remark->setAttribs(array(
				'class'=>'form-control',
		));
		
		if($data != null){
			$row_cate = $db->getAllCategory();
			if(!empty($row_cate)){
				foreach ($row_cate as $rs){
						if($rs["id"]==$data["id"]){
						}else{
							$opt[$rs["id"]] = $rs["name"];
						}
				}
				$parent->setMultiOptions($opt);
			}
			$name->setValue($data["name"]);
			$parent->setValue($data["parent_id"]);
			$remark->setValue($data["remark"]);
			$status->setValue($data["status"]);
			$prifix->setValue($data["prefix"]);
		}
			
		$this->addElements(array($parent,$name,$status,$remark,$prifix));
		return $this;
	}
	
	public function categoryFilter(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Category_Model_DbTable_DbCategory();
		$name = new Zend_Form_Element_Text('name');
		$name->setAttribs(array(
				'class'=>'form-control',
		));
		
		$parent = new Zend_Form_Element_Select("parent");
		$parent->setAttribs(array(
				'class'=>'form-control',
		));
		$opt = array(''=>$tr->translate("SEELECT_CATEGORY"));
		$row_cate = $db->getAllCategory();
		if(!empty($row_cate)){
			foreach ($row_cate as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$parent->setMultiOptions($opt);
		$status = new Zend_Form_Element_Select("status");
		$status->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		$opt = array('1'=>$tr->translate("ACTIVE"),'0'=>$tr->translate("DEACTIVE"));
		$status->setMultiOptions($opt);
		
		$this->addElements(array($parent,$name,$status));
		return $this;
	}
}
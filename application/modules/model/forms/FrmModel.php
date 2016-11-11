<?php 
class Model_Form_FrmModel extends Zend_Form
{
	public function init()
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
	}
	/////////////	Form Product		/////////////////
	public function model($data=null){
		$db = new Model_Model_DbTable_DbModel();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$name = new Zend_Form_Element_Text('name');
		$name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		 
// 		$parent = new Zend_Form_Element_Select("parent");
// 		$parent->setAttribs(array(
// 				'class'=>'form-control',
// 		));
// 		$opt = array(''=>$tr->translate("SEELECT_Model"));
// 		if(!empty($db->getAllModel())){
// 			foreach ($db->getAllModel() as $rs){
// 				$opt[$rs["id"]] = $rs["name"];
// 			}
// 		}
// 		$parent->setMultiOptions($opt);
		
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
			$name->setValue($data["name"]);
			//$parent->setValue($data["parent_id"]);
			$remark->setValue($data["remark"]);
			$status->setValue($data["status"]);
		}
			
		$this->addElements(array($name,$status,$remark));
		return $this;
	}
	
	public function ModelFilter(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$db = new Model_Model_DbTable_DbModel();
		$name = new Zend_Form_Element_Text('name');
		$name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		$parent = new Zend_Form_Element_Select("parent");
		$parent->setAttribs(array(
				'class'=>'form-control',
		));
		$opt = array(''=>$tr->translate("SEELECT_Model"));
		if(!empty($db->getAllModel())){
			foreach ($db->getAllModel() as $rs){
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
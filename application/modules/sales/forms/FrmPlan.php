<?php 
class Sales_Form_FrmPlan extends Zend_Form
{
	protected $tr;
	public function init()
    {
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
	}
	///From plan 
	function frmPlanType($data=null){
		$db = new Sales_Model_DbTable_DbPlan();
		$row = $db->getStatus();
		$name_type = new Zend_Form_Element_Text("nametype");
		$name_type->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$status = new Zend_Form_Element_Select("status");
		$opt = array();
		if(!empty($row)){
			foreach($row as $rs){
				$opt[$rs["key_code"]] = $rs["name_en"];
			}
		}
		$status->setAttribs(array(
				'class'=>'form-control select2me',
				'required'=>'required',
		));
		$status->setMultiOptions($opt);
		
		if($data!=null){
			$name_type->setValue($data["name"]);
			$status->setValue($data["status"]);
		}
		$this->addElements(array($name_type,$status));
		return $this;
	}
	/////////////	Form Sales		/////////////////
	public function add($data=null){
		$db = new Sales_Model_DbTable_DbPlan();
		$row_type = $db->getType();
		//$row_cate = $db->getPlanType();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Sales_Model_DbTable_DbSales();
		$p_code = $db->getSalesCode();
		$name = new Zend_Form_Element_Text("name");
		$name->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		
		$opt_u = array(''=>"select type");
		if(!empty($row_type)){
			foreach ($row_type  as $rs){
				$opt_u[$rs["id"]] = $rs["name"];
			}
		}
		
		
		$_type = new Zend_Form_Element_Select("type");
		$_type->setMultiOptions($opt_u);
		$_type->setAttribs(array(
			'class'=>'form-control select2me',
		));
		
		
		$opt_u = array(''=>"select typecategory");
		if(!empty($row_cate)){
			foreach ($row_cate  as $rs){
				$opt_u[$rs["id"]] = $rs["type"];
			}
		}
		
		
		$_typecate = new Zend_Form_Element_Select("typecate");
		$_typecate->setMultiOptions($opt_u);
		$_typecate->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$_typecate->setMultiOptions($opt_u);
		
		$name_type = new Zend_Form_Element_Text("nametype");
		$name_type->setAttribs(array(
				'class'=>'form-control',
				'required'=>'required'
		));
		
		
		$opt = array(''=>$tr->translate("SELECT_CATEGORY"),-1=>$tr->translate("ADD_NEW_CATEGORY"));
		$category = new Zend_Form_Element_Select("category");
		$category->setAttribs(array(
				'class'=>'form-control select2me',
				'onChange'=>'getPopupCategory();getSalesPrefix();',
				//'required'=>'required'
		));
		$address = new Zend_Form_Element_Text("address");
		$address->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		$type_name = new Zend_Form_Element_Text("typename");
		$type_name->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		$status = new Zend_Form_Element_Select("status");
		$opt = array();
		if(!empty($row)){
			foreach($row as $rs){
				$opt[$rs["key_code"]] = $rs["name_en"];
			}
		}
		$status->setAttribs(array(
				'class'=>'form-control select2me',
				//'Onchange'	=>	'getMeasureLabel()'
		));
		$status->setMultiOptions($opt);
		$status->setValue($request->getParam("status"));
		if($data!=null){
			//print_r($data); exit();
			$address->setValue($data["address"]);
			$_type->setValue($data["type"]);
			//$_typecate->setValue($data["typecate"]);
			$type_name->setValue($data["name"]);
			//$status->setValue($data["status"]);
			$status->setValue($data["status"]);
		}
		
		$this->addElements(array($status,$_typecate,$type_name,$_type,$address,$name_type,$name));
		return $this;
	}
	function SalesFilter(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db_p = new Sales_Model_DbTable_DbPlan();
		$row_cate = $db_p->getType();
		$row = $db_p->getStatus();
		$ad_search = new Zend_Form_Element_Text("ad_search");
		$ad_search->setAttribs(array(
				'class'=>'form-control',
		));
		$ad_search->setValue($request->getParam("ad_search"));
		
		$opt_u = array(''=>"select type");
		if(!empty($row_cate)){
			foreach ($row_cate  as $rs){
				$opt_u[$rs["id"]] = $rs["name"];
			}
		}
		
		$_typecate = new Zend_Form_Element_Select("typecate");
		$_typecate->setMultiOptions($opt_u);
		$_typecate->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$_typecate->setMultiOptions($opt_u);
	
	
		$status = new Zend_Form_Element_Select("status");
		$opt = array('1'=>$tr->translate("ACTIVE"),'2'=>$tr->translate("DEACTIVE"));
		$status->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$status->setMultiOptions($opt);
		$status->setValue($request->getParam("status"));
	
		$this->addElements(array($_typecate,$ad_search,$status));
		return $this;
	}
	
}
	
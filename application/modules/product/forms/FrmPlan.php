<?php 
class Product_Form_FrmPlan extends Zend_Form
{
	protected $tr;
	public function init()
    {
    	$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
	}
	///From plan 
	function frmPlanType($data=null){
		$db = new Product_Model_DbTable_DbPlan();
		getStatus();
		 $name_type= new Zend_Form_Element_Text("nametype");
		$name_type->setAttribs(array(
				'class'=>'form-control',
				//'required'=>'required'
		));
		
		$status = new Zend_Form_Element_Select("status");
		$opt = array('1'=>$this->tr->translate("ACTIVE"),'2'=>$this->tr->translate("DEACTIVE"));
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
	/////////////	Form Product		/////////////////
	public function add($data=null){
		$db = new Product_Model_DbTable_DbPlan();
		$row_type = $db->getType();
		//$row_cate = $db->getPlanType();
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Product_Model_DbTable_DbProduct();
		$p_code = $db->getProductCode();
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
				'onChange'=>'getPopupCategory();getProductPrefix();',
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
		$opt = array('1'=>$this->tr->translate("ACTIVE"),'0'=>$this->tr->translate("DEACTIVE"));
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
	function productFilter(){
		$tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db = new Product_Model_DbTable_DbProduct();
		
		$db_p = new Product_Model_DbTable_DbPlan();
		$row_cate = $db_p->getType();
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
	
		$branch = new Zend_Form_Element_Select("branch");
		$opt = array(''=>$tr->translate("SELECT_BRANCH"));
		$row_branch = $db->getBranch();
		if(!empty($row_branch)){
			foreach ($row_branch as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$branch->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$branch->setMultiOptions($opt);
		$branch->setValue($request->getParam("branch"));
	
		$status = new Zend_Form_Element_Select("status");
		$opt = array('1'=>$tr->translate("ACTIVE"),'2'=>$tr->translate("DEACTIVE"));
		$status->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$status->setMultiOptions($opt);
		$status->setValue($request->getParam("status"));
		$opt = array(''=>$tr->translate("SELECT_BRAND"));
		$brand = new Zend_Form_Element_Select("brand");
		$brand->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_brand = $db->getBrand();
		if(!empty($row_brand)){
			foreach ($row_brand as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$brand->setMultiOptions($opt);
		$brand->setValue($request->getParam("brand"));
			
		$opt = array(''=>$tr->translate("SELECT_MODEL"));
		$model = new Zend_Form_Element_Select("model");
		$model->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_model = $db->getModel();
		if(!empty($row_model)){
			foreach ($row_model as $rss){
				$opt[$rss["key_code"]] = $rss["name"];
			}
		}
		$model->setMultiOptions($opt);
		$model->setValue($request->getParam("model"));
			
		$opt = array(''=>$tr->translate("SELECT_CATEGORY"));
		$category = new Zend_Form_Element_Select("category");
		$category->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_cat = $db->getCategory();
		if(!empty($row_cat)){
			foreach ($row_cat as $rs){
				$opt[$rs["id"]] = $rs["name"];
			}
		}
		$category->setMultiOptions($opt);
		$category->setValue($request->getParam("category"));
	
		$opt = array(''=>$tr->translate("SELECT_COLOR"));
		$color = new Zend_Form_Element_Select("color");
		$color->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_color = $db->getColor();
		if(!empty($row_color)){
			foreach ($row_color as $rs){
				$opt[$rs["key_code"]] = $rs["name"];
			}
		}
		$color->setMultiOptions($opt);
		$color->setValue($request->getParam("color"));
			
		$opt = array(''=>$tr->translate("SELECT_SIZE"));
		$size = new Zend_Form_Element_Select("size");
		$size->setAttribs(array(
				'class'=>'form-control select2me',
		));
		$row_size = $db->getSize();
		if(!empty($row_size)){
			foreach ($row_size as $rs){
				$opt[$rs["key_code"]] = $rs["name"];
			}
		}
		$size->setMultiOptions($opt);
		$size->setValue($request->getParam("size"));
	
		$this->addElements(array($_typecate,$ad_search,$branch,$brand,$model,$category,$color,$size,$status));
		return $this;
	}
	
}
	
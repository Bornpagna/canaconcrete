<?php
class Product_plantypeController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
	function updatecodeAction(){
		$db = new Product_Model_DbTable_DbProduct();
		$db->getProductCoded();
	}
    public function indexAction()
    {
    	$db = new Product_Model_DbTable_DbPlantype();
    	if($this->getRequest()->isPost()){
    		$search = $this->getRequest()->getPost();
    	}else{
    		$search = array(
    			'ad_search'	=>	'',
    			'status'	=>	1,
    			'name'	=>	'',
    		);
    	}
    	$this->view->product = $db->getPlanType($search);
    	$formFilter = new Product_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->productFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
        
	}
	public function addAction()
	{
		$db = new Product_Model_DbTable_DbPlantype();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->add($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/product/plantype/index');
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			//$rs_plantype = $db->getPlanType();
			//$this->view->plantype = $rs_plantype;
			$formProduct = new Product_Form_FrmPlan();
			$formStockAdd = $formProduct->frmPlanType(null);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->form = $formStockAdd;
			
	}
	public function editAction()
	{
		$id=$this->getRequest()->getParam('id');
		$db = new Product_Model_DbTable_DbPlantype();
		$row=$db->getplantypeById($id);
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post['id']=$id;
					$db->updateplantypeById($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/product/plantype/index');
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/product/plantype/add');
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$formProduct = new Product_Form_FrmPlan();
			$formStockAdd = $formProduct->frmPlanType($row);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->form = $formStockAdd;
			
	}
	
}


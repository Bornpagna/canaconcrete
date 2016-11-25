<?php
class Sales_plantypeController extends Zend_Controller_Action
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
		$db = new Sales_Model_DbTable_DbSales();
		$db->getSalesCoded();
	}
    public function indexAction()
    {
    	$db = new Sales_Model_DbTable_DbPlantype();
    	if($this->getRequest()->isPost()){
    		$search = $this->getRequest()->getPost();
    	}else{
    		$search = array(
    			'ad_search'	=>	'',
    			'status'	=>	1,
    			'name'	=>	'',
    		);
    	}
    	$this->view->Sales = $db->getPlanType($search);
    	$formFilter = new Sales_Form_FrmPlan();
    	$this->view->formFilter = $formFilter->SalesFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
        
	}
	public function addAction()
	{
		$db = new Sales_Model_DbTable_DbPlantype();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->add($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/Sales/plantype/index');
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			//$rs_plantype = $db->getPlanType();
			//$this->view->plantype = $rs_plantype;
			$formSales = new Sales_Form_FrmPlan();
			$formStockAdd = $formSales->frmPlanType(null);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->form = $formStockAdd;
			
	}
	public function editAction()
	{
		$id=$this->getRequest()->getParam('id');
		$db = new Sales_Model_DbTable_DbPlantype();
		$row=$db->getplantypeById($id);
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post['id']=$id;
					$db->updateplantypeById($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/Sales/plantype/index');
					}else{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/Sales/plantype/add');
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$formSales = new Sales_Form_FrmPlan();
			$formStockAdd = $formSales->frmPlanType($row);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->form = $formStockAdd;
			
	}
	
}


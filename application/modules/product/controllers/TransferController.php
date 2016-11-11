<?php
class Product_transferController extends Zend_Controller_Action
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
    public function indexAction()
    {
    	$db = new Product_Model_DbTable_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'tran_num'	=>	'',
    			'tran_date'	=>	1,
    			'type'		=>	'',
    			'status'	=>	1,
    			'to_loc'	=>	'',
    		);
    	}
    	$this->view->product = $db->getTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
        
	}
	public function addAction()
	{
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->add($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$formProduct = new Product_Form_FrmTransfer();
			$formStockAdd = $formProduct->add(null);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->formFilter = $formStockAdd;
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id");
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->add($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			
			$rs = $db->getTransferById($id);
			$rs_detail = $db->getTransferDettail($id);
			$this->view->rs_detail = $rs_detail;
			$formProduct = new Product_Form_FrmTransfer();
			$formStockAdd = $formProduct->add($rs);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->formFilter = $formStockAdd;
	}
	//view category 27-8-2013
	
	function requestlitAction(){
		$db = new Product_Model_DbTable_DbTransfer();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'tran_num'	=>	'',
    			'tran_date'	=>	1,
    			'type'		=>	'',
    			'status'	=>	1,
    			'to_loc'	=>	'',
    		);
    	}
    	$this->view->product = $db->getTransfer($data);
    	$formFilter = new Product_Form_FrmTransfer();
    	$this->view->formFilter = $formFilter->frmFilter();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	function requesttransferAction(){
		$db = new Product_Model_DbTable_DbTransfer();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->addRequest($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
						Application_Form_FrmMessage::redirectUrl('/product/transfer');
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$formProduct = new Product_Form_FrmTransfer();
			$formStockAdd = $formProduct->addRequest(null);
			Application_Model_Decorator::removeAllDecorator($formStockAdd);
			$this->view->formFilter = $formStockAdd;
	}
	
	function editrequestAction(){
		
	}
	
	function recieverequest(){
		
	}
	function editrecieve(){
		
	}
	
		
	
}


<?php
class Report_ReportController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    
    public function indexAction()
    {
    	
        
	}
	public function addAction()
	{
		if ($this->getRequest()->isPost()){
			try {
				$data = $this->getRequest()->getPost();
				$contact = new Dailywork_Model_DbTable_DbContact();
				$contact->add($data);
				if(isset($data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("Complte", "/dailywork/contact/index");
				}else{
					Application_Form_FrmMessage::Sucessfull("Complete", "/dailywork/contact/add");
				}
			}
			catch (Exception $e){
			Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
			}
		}
		$fm=new Dailywork_Form_FrmContact();
		$frm_dailywork=$fm->add();
		Application_Model_Decorator::removeAllDecorator($frm_dailywork);
		$this->view->frm_dailywork= $frm_dailywork;
			//$this->_redirect("/product/product/select");
		
	}// Add Product 
	
	

	
	function reportAction(){
		
	}
	function exportAction(){
		
	}
	function balanceAction(){
		
	}
	function requestAction(){
		
	}
	function readyconcreteAction(){
		
	}
	function invoiceAction(){
		
	}
	function codeAction(){
		
	}
	function projectnameAction(){
		
	}
	function deliveryAction(){
		
	}
	function ebhAction(){
		
	}
		
	
}


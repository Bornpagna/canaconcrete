<?php
class Size_indexController extends Zend_Controller_Action
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
		$db = new Size_Model_DbTable_DbSize();
		$formFilter = new Size_Form_FrmSize();
		$frmsearch = $formFilter->SizeFilter();
		$this->view->formFilter = $frmsearch;
		$list = new Application_Form_Frmlist();
		$result = $db->getAllSize();
		$this->view->resulr = $result;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$session_stock = new Zend_Session_Namespace('stock');
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Size_Model_DbTable_DbSize();
			$db->add($data);
			if($data['save_close']){
				$this->_redirect('/size/index');
			}
			else{
				$this->_redirect('/size/index/add');
			}
		}
		$formFilter = new Size_Form_FrmSize();
		$formAdd = $formFilter->size();
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Size_Model_DbTable_DbSize();
		
		if($id==0){
			$this->_redirect('/size/index/add');
		}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$db = new Size_Model_DbTable_DbSize();
			$db->edit($data);
			if($data['save_close']){
				$this->_redirect('/size/index');
			}
			else{
				$this->_redirect('/size/index/add');
			}
		}
		$rs = $db->getSize($id);
		$formFilter = new Size_Form_FrmSize();
		$formAdd = $formFilter->size($rs);
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	//view Size 27-8-2013
	
	public function addNewLocationAction(){
		$post=$this->getRequest()->getPost();
		$add_new_location = new Product_Model_DbTable_DbAddProduct();
		$location_id = $add_new_location->addStockLocation($post);
		$result = array("LocationId"=>$location_id);
		if(!$result){
			$result = array('LocationId'=>1);
		}
		echo Zend_Json::encode($result);
		exit();
	}
	
}


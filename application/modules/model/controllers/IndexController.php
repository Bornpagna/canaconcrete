<?php
class Model_indexController extends Zend_Controller_Action
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
		$db = new Model_Model_DbTable_DbModel();
		$formFilter = new Model_Form_FrmModel();
		$frmsearch = $formFilter->ModelFilter();
		$this->view->formFilter = $frmsearch;
		$list = new Application_Form_Frmlist();
		$result = $db->getAllModel();
		$this->view->resulr = $result;
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$session_stock = new Zend_Session_Namespace('stock');
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$db = new Model_Model_DbTable_DbModel();
			$db->add($data);
			if($data['save_close']){
				$this->_redirect('/model/index');
			}
			else{
				$this->_redirect('/model/index/add');
			}
		}
		$formFilter = new Model_Form_FrmModel();
		$formAdd = $formFilter->model();
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Model_Model_DbTable_DbModel();
		
		if($id==0){
			$this->_redirect('/model/index/add');
		}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$db = new Model_Model_DbTable_DbModel();
			$db->edit($data);
			if($data['save_close']){
				$this->_redirect('/model/index');
			}
			else{
				$this->_redirect('/model/index/add');
			}
		}
		$rs = $db->getModel($id);
		$formFilter = new Model_Form_FrmModel();
		$formAdd = $formFilter->model($rs);
		$this->view->frmAdd = $formAdd;
		Application_Model_Decorator::removeAllDecorator($formAdd);
	}
	//view Model 27-8-2013
	
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


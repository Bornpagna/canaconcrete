<?php
class Purchase_ReceiveController extends Zend_Controller_Action
{	
    public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
	public function indexAction()
		{
			if($this->getRequest()->isPost()){
					$search = $this->getRequest()->getPost();
			}else{
				$search= array();
			}
			$db = new Purchase_Model_DbTable_DbRecieve();
			$rows = $db->getAllReceivedOrder($search);
			$glClass = new Application_Model_GlobalClass();
			$columns=array("PURCHASE_ORDER_CAP","ORDER_DATE_CAP", "VENDOR_NAME_CAP","TOTAL_CAP_DOLLAR","BY_USER_CAP");
			$link=array(
					'module'=>'purchase','controller'=>'receive','action'=>'detail-purchase-order',
			);
			// url link to update purchase order
			
			$urlEdit = BASE_URL . "/purchase/index/update-purchase-order-test";
			$list = new Application_Form_Frmlist();
			$this->view->list=$list->getCheckList(1, $columns, $rows, array('order'=>$link),$urlEdit);
			
			$formFilter = new Application_Form_Frmsearch();
			$this->view->formFilter = $formFilter;
			Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction(){
		$id = $this->getRequest()->getParam('id');
		$db = new Purchase_Model_DbTable_DbRecieve();
		$db_p = new Purchase_Model_DbTable_DbPurchaseVendor();
		$row = $db_p->getPurchaseById($id);
		if($this->getRequest()->isPost()){
			try{
				$data = $this->getRequest()->getPost();
				
				$db->add($data);
				if(isset($data["save_close"])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", "/purchase/receive");
				}else{
					Application_Form_FrmMessage::message('INSERT_SUCCESS');
				}
			}catch (Exception $e){
				Application_Form_FrmMessage::message('INSERT_FAIL');
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$this->view->item = $db->getItemByPuId($id);
		$frm_purchase = new Purchase_Form_FrmRecieve(null);
		$form_add_purchase = $frm_purchase->add($row);
		Application_Model_Decorator::removeAllDecorator($form_add_purchase);
		$this->view->form_purchase = $form_add_purchase;
	}
	public function getPurchaseVendorAction(){		
		if($this->getRequest()->isPost()){
		    $db= new Purchase_Model_DbTable_DbRecieve();
			$post=$this->getRequest()->getPost();
			$result= $db->getVendorByPuId($post["id"]);
			echo Zend_Json::encode($result);
			exit();
		}
		
	}
	public  function getPurchaseItemAction(){
		if($this->getRequest()->isPost()){
		    $db= new Purchase_Model_DbTable_DbRecieve();
			$post=$this->getRequest()->getPost();
			$result= $db->getItemByPuId($post["id"]);
			echo Zend_Json::encode($result);
			exit();
		}
	}
	public function getPurchaseidAction(){		
		if($this->getRequest()->isPost()){
		    $db= new Application_Model_DbTable_DbGlobal();
			$post=$this->getRequest()->getPost();
			$invoice = $post['invoice_id'];
			$sqlinfo ="SELECT * FROM `tb_purchase_order` WHERE order_id = $invoice LIMIT 1";
			$rowinfo=$db->getGlobalDbRow($sqlinfo);
			$sql = "SELECT pui.qty_order,pui.pro_id,pui.price,pui.sub_total
					,(SELECT pur.order FROM tb_purchase_order as pur WHERE pur.order_id = pui.order_id ) as order_no
					,(SELECT pur.all_total FROM tb_purchase_order as pur WHERE pur.order_id = pui.order_id ) as all_total
					,(SELECT pr.qty_perunit FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS qty_perunit
      				,(SELECT pr.item_name FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS item_name
					,(SELECT pr.pro_id FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS pro_id
      				,(SELECT `label` FROM tb_product AS pr WHERE pr.pro_id = pui.pro_id LIMIT 1) AS label
     				 ,(SELECT `measure_name` FROM `tb_measure` AS ms WHERE ms.id=(SELECT measure_id FROM tb_product WHERE pro_id=pui.`pro_id`)) AS measure_name
      			FROM `tb_purchase_order_item` AS pui WHERE pui.order_id = ".$invoice;
			$rows=$db->getGlobalDb($sql);
		    $result = array('poinfo'=>$rowinfo,'item'=>$rows);
			echo Zend_Json::encode($result);
			exit();
		}
		
	}
}
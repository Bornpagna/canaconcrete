<?php
class Application_Form_Frmsearch extends Zend_Form
{
	public function init()
	{
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$db=new Application_Model_DbTable_DbGlobal();
		
		$tr=Application_Form_FrmLanguages::getCurrentlanguage();
		$nameValue = $request->getParam('text_search');
		$nameElement = new Zend_Form_Element_Text('text_search');
		$nameElement->setAttribs(array(
				'class'=>'form-control'
				));
		$nameElement->setValue($nameValue);
		$this->addElement($nameElement);
		
		$rs=$db->getGlobalDb('SELECT vendor_id, v_name FROM tb_vendor WHERE v_name!="" AND status=1 ');
		$options=array($tr->translate('Choose Suppliyer'));
		$vendorValue = $request->getParam('suppliyer_id');
		if(!empty($rs)) foreach($rs as $read) $options[$read['vendor_id']]=$read['v_name'];
		$vendor_element=new Zend_Form_Element_Select('suppliyer_id');
		$vendor_element->setMultiOptions($options);
		$vendor_element->setAttribs(array(
				'id'=>'suppliyer_id',
				'class'=>'form-control select2me'
		));
		$vendor_element->setValue($vendorValue);
		$this->addElement($vendor_element);

		
		/////////////Date of lost item		/////////////////
		$startDateValue = $request->getParam('start_date');
		$endDateValue = $request->getParam('end_date');
		
		if($endDateValue==""){
			$endDateValue=date("m/d/Y");
			//$startDateValue=date("m/d/Y");
		}
		
		$startDateElement = new Zend_Form_Element_Text('start_date');
		$startDateElement->setValue($startDateValue);
		$startDateElement->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker',
				'placeholder'=>'Start Date'
		));
		
		$this->addElement($startDateElement);
		$endDateElement = new Zend_Form_Element_Text('end_date');
		
		$endDateElement->setValue($endDateValue);
		$this->addElement($endDateElement);
		$endDateElement->setAttribs(array(
				'class'=>'form-control form-control-inline date-picker'
		));
		
// 		$rs=$db->getGlobalDb('SELECT DISTINCT name,id FROM tb_sublocation WHERE Name!="" AND status=1 ');
// 		$options=array($tr->translate('Please_Select'));
// 		$locationValue = $request->getParam('LocationId');
// 		foreach($rs as $read) $options[$read['id']]=$read['name'];
// 		$location_id=new Zend_Form_Element_Select('id');
// 		$location_id->setMultiOptions($options);
// 		$location_id->setAttribs(array(
// 				'id'=>'LocationId',
// 				'onchange'=>'this.form.submit()',
// 				'class'=>'form-control'
				
// 		));
// 		$location_id->setValue($locationValue);
// 		$this->addElement($location_id);
	  
		$statusCOValue=4;
		$statusCOValue = $request->getParam('purchase_status');
		$optionsCOStatus=array(0=>$tr->translate('CHOOSE_STATUS'),2=>$tr->translate('OPEN'),3=>$tr->translate('IN_PROGRESS'),4=>$tr->translate('PAID'),5=>$tr->translate('RECEIVED'),6=>$tr->translate('MENU_CANCEL'));
		$statusCO=new Zend_Form_Element_Select('purchase_status');
		$statusCO->setMultiOptions($optionsCOStatus);
		$statusCO->setattribs(array(
				'id'=>'status',
				'class'=>'form-control'
		));
		
		$statusCO->setValue($statusCOValue);
		$this->addElement($statusCO);
		
	}
	
}


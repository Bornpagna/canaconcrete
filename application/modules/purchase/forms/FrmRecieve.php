<?php 
class Purchase_Form_FrmVendor extends Zend_Form
{
	public function init()
    {	
	}
	/////////////	Form vendor		/////////////////
public function add($data=null) {
		$db = new Purchase_Model_DbTable_DbRecieve();
		$rs_code = $db->getAllPuCode();
		$opt_code = array();
		if(!empty($rs_code)){
			foreach($rs_code as $rs){
				$opt_code[$rs["id"]] = $rs["order_number"];
			}
		}
		$pu_code = new Zend_Form_Element_Select('pu_code');
		$pu_code->setAttribs(array('class'=>'validate[required] form-control select2me','placeholder'=>'Select Purchase No'));
    	$pu_code->setMultiValue($opt_code);
		$this->addElement($pu_code);
		
		$vendor = new Zend_Form_Element_Select('vendor');
		$pu_code->setAttribs(array('class'=>'validate[required] form-control select2me','placeholder'=>'Select Purchase No'));
    	$this->addElement($pu_code);
		
			
    	
    	if($data != null) {
	       
    	}
    	return $this;
	}
}
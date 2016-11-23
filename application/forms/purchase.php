<?php

class Application_Form_purchase extends Zend_Form
{
    public function init()
    {
   
    }
    protected function GetuserInfo(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function productOrder($data=null)
    {
    	//Application_Form_FrmLanguages::getCurrentlanguage();
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
    	
    	$paymentElement = new Zend_Form_Element_Submit('payment');
    	$paymentElement->setAttribs(array('Phone'=>'Phone'));
    	$this->addElement($paymentElement);
    	
    	$rs=$db->getGlobalDb('SELECT vendor_id, v_name FROM tb_vendor WHERE v_name!="" ORDER BY vendor_id DESC');
    	$customerValue = $request->getParam('vendor_id');
    	$options=array(''=>$tr->translate('Please_Select'),'-1'=>$tr->translate('Add_New_Vendor'));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['vendor_id']]=$read['v_name'];
    	$vendor_id=new Zend_Form_Element_Select('v_name');
    	$vendor_id ->setAttribs(array(
    			'class' => 'validate[required] form-control select2me',
    			'Onchange'=>'getCustomerInfo()'
    			));
    	$vendor_id->setMultiOptions($options);
    	$vendor_id->setValue($customerValue);
    	$this->addElement($vendor_id);
    	
    	$roder_element= new Zend_Form_Element_Text("txt_order");
    	$roder_element->setAttribs(array('placeholder' => 'Optional','class'=>'validate[required] form-control',
    			"onblur"=>"CheckPOInvoice();"));
    	$this->addElement($roder_element);
    	
    	$contactElement = new Zend_Form_Element_Text('contact');
    	$contactElement->setAttribs(array('placeholder' => 'Enter Contact Name'));
    	$this->addElement($contactElement);
    	
    	$orderElement = new Zend_Form_Element_Text('order');
    	$orderElement ->setAttribs(array('placeholder' => 'Enter Order'));
    	$this->addElement($orderElement);
    	
    	$phoneElement = new Zend_Form_Element_Text('txt_phone');
    	$phoneElement->setAttribs(array('placeholder' => 'Enter Phone Number'));
    	$this->addElement($phoneElement);
    	
    	$user= $this->GetuserInfo();
    	$options="";
    	$sql = "SELECT id, name FROM tb_sublocation WHERE Name!='' ";
    	if($user["level"]==1 OR $user["level"]== 2){
    		$options=array("1"=>$tr->translate("CHOOSE_BRANCH"),"-1"=>$tr->translate("ADD_NEW"));
    	}
    	else{
    		$sql.=" AND id = ".$user["location_id"];
    	}
    	$sql.=" ORDER BY id DESC";
    	$rs=$db->getGlobalDb($sql);
    	$productValue = $request->getParam('LocationId');
    	if(!empty($rs)) foreach($rs as $read) $options[$read['id']]=$read['name'];
    	$locationID = new Zend_Form_Element_Select('LocationId');
    	$locationID ->setAttribs(array('class'=>'validate[required] form-control select2me'));
    	$locationID->setMultiOptions($options);
    	$locationID->setattribs(array(
    			'Onchange'=>'AddLocation()',));
    	$locationID->setValue($productValue);
    	$this->addElement($locationID);
    	    	
    	$rowspayment= $db->getGlobalDb('SELECT * FROM tb_paymentmethod');
    	if($rowspayment) {
    		foreach($rowspayment as $readCategory) $options_cg[$readCategory['payment_typeId']]=$readCategory['payment_name'];
    	}
    	$paymentmethodElement = new Zend_Form_Element_Select('payment_name');
    	$paymentmethodElement->setMultiOptions($options_cg);
    	$this->addElement($paymentmethodElement);
    	$paymentmethodElement->setAttribs(array("class"=>"form-control select2me"));
    	$rowsPayment = $db->getGlobalDb('SELECT id, description,symbal FROM tb_currency WHERE status = 1 ');
    	if($rowsPayment) {
    		foreach($rowsPayment as $readPayment) $options_cur[$readPayment['id']]=$readPayment['description'].$readPayment['symbal'];
    	}	 
    	$currencyElement = new Zend_Form_Element_Select('currency');
    	$currencyElement->setAttribs(array('class'=>'demo-code-language form-control select2me'));
    	$currencyElement->setMultiOptions($options_cur);
    	$this->addElement($currencyElement);
    	
    	$descriptionElement = new Zend_Form_Element_Textarea('remark');
    	$descriptionElement->setAttribs(array("class"=>'form-control',"rows"=>3));
    	$this->addElement($descriptionElement);
    	
    	$remarkaddElement = new Zend_Form_Element_Textarea('remark_add'); 
    	$this->addElement($remarkaddElement);
    	
    	$vendoraddElement = new Zend_Form_Element_Textarea('vendor_address'); 
    	$vendoraddElement->setAttribs(array('placeholder' => 'Enter Vendor Address'));
    	$this->addElement($vendoraddElement);
    	
    	$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array("class"=>"form-control",'readonly'=>'readonly','style'=>'text-align:right'));
    	$this->addElement($allTotalElement);
    	
    	$discountTypeElement = new Zend_Form_Element_Radio('discount_type');
    	$discountTypeElement->setMultiOptions(array(1=>'%',2=>'Fix Value'));
    	$discountTypeElement->setAttribs(array('checked'=>'checked',));
    	$discountTypeElement->setAttribs(array('onChange'=>'doTotal()',"class"=>"form-control"));
    	$this->addElement($discountTypeElement);    

    	$netTotalElement = new Zend_Form_Element_Text('net_total');
    	$netTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($netTotalElement);
    	
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px form-control','onblur'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	
    	$discountRealElement = new Zend_Form_Element_Text('discount_real');
    	$discountRealElement->setAttribs(array('readonly'=>'readonly','class'=>'input100px form-control',));
    	$this->addElement($discountRealElement);
    	
    	$globalRealElement = new Zend_Form_Element_Hidden('global_disc');
    	$globalRealElement->setAttribs(array("class"=>"form-control"));
    	$this->addElement($globalRealElement);
    	
    	
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onblur'=>'doTotal();','style'=>'text-align:right'));
    	$this->addElement($discountValueElement);
    	
    	$dis_valueElement = new Zend_Form_Element_Text('dis_value');
    	$dis_valueElement->setAttribs(array("required"=>1,'placeholder' => 'Discount Value','style'=>'text-align:right'));
    	$dis_valueElement->setValue(0);
    	$dis_valueElement->setAttribs(array("onkeyup"=>"calculateDiscount();","class"=>"form-control"));
    	$this->addElement($dis_valueElement);
    	
    	$totalAmountElement = new Zend_Form_Element_Text('totalAmoun');
    	$totalAmountElement->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"form-control"
    	));
    	$this->addElement($totalAmountElement);
    	
    	$remainlElement = new Zend_Form_Element_Text('remain');
    	$remainlElement->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"red form-control"));
    	$this->addElement($remainlElement);
    	
    	$balancelElement = new Zend_Form_Element_Text('balance');
    	$balancelElement->setAttribs(array('readonly'=>'readonly','style'=>'text-align:right',"class"=>"form-control"));
    	$this->addElement($balancelElement);
    	
    	$statusElement = new Zend_Form_Element_Select('status');
    	$opt_status = array(5=>"Recieved",2=>'Open');
    	$statusElement ->setAttribs(array('class'=>'validate[required] form-control select2me','onchange'=>'calculatePrice();','placeholder' => 'Please Select status'));
    	$statusElement->setMultiOptions($opt_status);
    	$this->addElement($statusElement);    	

    	$date_inElement = new Zend_Form_Element_Text('date_in');
    	$date =new Zend_Date();
    	$date_inElement ->setAttribs(array('class'=>'validate[required] form-control form-control-inline date-picker'));
    	$date_inElement ->setValue($date->get('MM/d/Y'));
    	$this->addElement($date_inElement);
    	
    	$dateOrderElement = new Zend_Form_Element_Text('order_date');
    	$dateOrderElement ->setAttribs(array('class'=>'col-md-3 validate[required] form-control form-control-inline date-picker','placeholder' => 'Click to Choose Date'));
    	$dateOrderElement ->setValue($date->get('M/d/Y'));
    	$this->addElement($dateOrderElement);
    	 
    	$addressElement = new Zend_Form_Element_Text('address');
    	$this->addElement($addressElement);
    	 
    	$termElement = new Zend_Form_Element_Text('term');
    	$termElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($termElement);    	
    	
    	 
    	$orderidElement = new Zend_Form_Element_Text('orderid');
    	$this->addElement($orderidElement);
    	
    	$dateElement = new Zend_Form_Element_Text('date');
    	$this->addElement($dateElement);
    	$paid_date = new Zend_Form_Element_Text("paid_date_payment");
    	$paid_date ->setAttribs(array('class'=>'validate[required]'));
    	$paid_date->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($paid_date);
    	 
    	$totalElement = new Zend_Form_Element_Text('total');
    	$this->addElement($totalElement);
    	
    	$totaTaxElement = new Zend_Form_Element_Text('total_tax');
    	$totaTaxElement->setAttribs(array('class'=>'custom[number] form-control','style'=>'text-align:right'));
    	$this->addElement($totaTaxElement);
    	
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$paidElement->setAttribs(array('class'=>'custom[number] form-control','onkeyup'=>'doRemain();','style'=>'text-align:right'));
    	$this->addElement($paidElement);
    	
    	$history_idElement = new Zend_Form_Element_Hidden('history_id');
    	$this->addElement($history_idElement);
    	$history_date_idElement = new Zend_Form_Element_Hidden('old_history_date');
    	$this->addElement($history_date_idElement);
    	
    	Application_Form_DateTimePicker::addDateField(array('order_date','date_in','offer_date','receiv_date','paid_date_payment'));
    		if($data != null) {
    			$idElement = new Zend_Form_Element_Text('id');
    			$this->addElement($idElement);
    			
    			$oldlocationIdElement = new Zend_Form_Element_Hidden('old_location');
    			$this->addElement($oldlocationIdElement);
    			
    			$date_inElement->setValue($data["date_in"]);
    			$oldStatusElement = new Zend_Form_Element_Hidden('oldStatus');
    			$this->addElement($oldStatusElement);
    			$vendor_id->setValue($data["vendor_id"]);
    			
    			 if($data["status"]==1){
    			 	$statusElement->setValue("Quote");
    			 }
    			 elseif($data["status"]==2){
    			 	$statusElement->setValue("Open");
    			 }
    			 elseif($data["status"]==3){
    			 	$statusElement->setValue("In Progress");
    			 }
    			 elseif($data["status"]==4){
    			 	$statusElement->setValue("Paid");
    			 }elseif ($data["status"]==5)
    			 	$statusElement->setValue("Recieved");
    			 else{
    			 	$statusElement->setValue("Cancel");
    			 }
    			//$idElement->setValue($data['id']);
    			$statusElement->setValue($data["status"]);
    			$oldStatusElement->setValue($data['status']);
    			$locationID->setvalue($data['branch_id']);
    			$oldlocationIdElement->setvalue($data['branch_id']);
    			$dateOrderElement->setValue($data["date_order"]);
    			$roder_element->setAttribs(array('readonly'=>'readonly'));
    			$paymentmethodElement->setValue($data['payment_method']);
    			$currencyElement->setValue($data['currency_id']);
    			$paidElement->setValue($data['paid']);
				$roder_element->setvalue($data["order_number"]);
				$descriptionElement->setvalue($data["remark"]);
    			//$remainlElement->setvalue($data['balance']);
				$totalAmountElement->setvalue($data["net_total"]);
    			$allTotalElement->setValue($data['all_total']);
    			$discountValueElement->setValue($data['discount_value']);
    			$netTotalElement->setValue($data['net_total']);   
    			$balancelElement->setValue($data["balance"]);
    			$globalRealElement->setValue($data["discount_real"]);
    		
    		} else {$discountTypeElement->setValue(1);
    	}
     	return $this;
    
    
    }
    
    public function SalesOrder($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
    	
    	$paymentElement = new Zend_Form_Element_Submit('payment');
    	$paymentElement->setAttribs(array('Phone'=>'Phone'));
    	$this->addElement($paymentElement);
    	
    	$rs=$db->getGlobalDb('SELECT customer_id, cust_name FROM tb_customer WHERE cust_name!="" AND is_active=1 ');
    	$customerValue = $request->getParam('users');
    	$options="";
    	$options=array(''=>$tr->translate('Please_Select'),'-1'=>$tr->translate('ADD_CUSTOMER_ORDER'));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['customer_id']]=$read['cust_name'];
    	$customerID=new Zend_Form_Element_Select('customer_id');
    	$customerID ->setAttribs(array( 'id'=>'customer_id',
    			'Onchange'=>'getCustomerInfo()',
    			'class'=>'validate[required]'
    		//	validate[required]
    			));
    	$customerID ->setMultiOptions($options);
    	//$customerID->setattribs(array(id'=>'customer_id','onchange'=>'this.form.submit()',));
    	$customerID->setValue($customerValue);
    	$this->addElement($customerID);
    	
    	$orderElement = new Zend_Form_Element_Text('order');
    	$orderElement ->setAttribs(array('placeholder' => 'Optional'));
    	$this->addElement($orderElement);
    	
    	$phoneElement = new Zend_Form_Element_Text('txt_phone');
    	$phoneElement->setAttribs(array('placeholder' => 'Enter Contact Number'));
    	$this->addElement($phoneElement);
    	
    	$user= $this->GetuserInfo();
    	$options="";
    	$sql = "SELECT LocationId, Name FROM tb_sublocation WHERE Name!='' AND status = 1 ";
    	if($user["level"]==1 OR $user["level"]== 2){
    		$options=array("1"=>$tr->translate("Please_Select"),"-1"=>$tr->translate("ADD_NEW_LOCATION"));
    	}
    	else{
    		$sql.=" ANd LocationId = ".$user["location_id"];
    	}
    	$sql.=" ORDER BY LocationId DESC";
    	$rs=$db->getGlobalDb($sql);
    	$productValue = $request->getParam('LocationId');
    	if(!empty($rs)) foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$locationID = new Zend_Form_Element_Select('LocationId');
    	$locationID->setAttribs(array(
    			'id'=>'LocationId',
    			'Onchange'=>'AddLocation()',
    			'class'=>'validate[required]',
    			//validate[required]
    	 ));
    	$locationID->setMultiOptions($options);
    	
    	$locationID->setValue($productValue);
    	$this->addElement($locationID);  
    	    	
    	$rowsmethodpay= $db->getGlobalDb('SELECT * FROM tb_paymentmethod');
    	if($rowsmethodpay) {
    		foreach($rowsmethodpay as $readCategory) $option_method[$readCategory['payment_typeId']]=$readCategory['payment_name'];
    	}
    	$paymentmethodElement = new Zend_Form_Element_Select('payment_name');
    	$paymentmethodElement->setAttribs(array(
    			'class'=>'form-control'
    			));
    	$paymentmethodElement->setMultiOptions($option_method);
    	$this->addElement($paymentmethodElement);
    	
//     	$rowsPayment = $db->getGlobalDb('SELECT id, Description,Symbol FROM tb_currency');
    	$rowsPayment='';
    	$options_curr=array();
    	if(!empty($rowsPayment)) {
    		foreach($rowsPayment as $readPayment) $options_curr[$readPayment['CurrencyId']]=$readPayment['Description'].$readPayment['Symbol'];
    	}	 
    	$currencyElement = new Zend_Form_Element_Select('currency');
    	$currencyElement->setMultiOptions($options_curr);
    	$this->addElement($currencyElement);
    	
    	$descriptionElement = new Zend_Form_Element_Textarea('remark');
    	$this->addElement($descriptionElement);
    	
    	$remarkaddElement = new Zend_Form_Element_Textarea('remark_add');
    	$this->addElement($remarkaddElement);
    	
    	$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($allTotalElement);
    	
    	$discountTypeElement = new Zend_Form_Element_Radio('discount_type');
    	$discountTypeElement->setMultiOptions(array(1=>'%',2=>'Fix Value'));
    	$discountTypeElement->setAttribs(array('checked'=>'checked',));
    	$discountTypeElement->setAttribs(array('onChange'=>'doTotal()',));
    	$this->addElement($discountTypeElement);    	
    	
    	$netTotalElement = new Zend_Form_Element_Text('net_total');
    	$netTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($netTotalElement);
    	
    	$dis_valueElement = new Zend_Form_Element_Text('dis_value');
    	$dis_valueElement->setAttribs(array('placeholder' => 'Discount Value'));
    	$dis_valueElement->setAttribs(array('onChange'=>'doTotal()',"class"=>"form-control"));
    	$this->addElement($dis_valueElement);
    	
    	$discount_type = new Zend_Form_Element_Select("discount_types");
    	$opt_discount = array(1=>"Percent(%)",2=>"Fix value($)");
    	$discount_type->setMultiOptions($opt_discount);
    	$discount_type->setAttribs(array('selected'=>'selected',));
    	$discount_type->setAttribs(array('onChange'=>'doTotal()',));
    	$this->addElement($discount_type);
    	
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onChange'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	
    	$discountRealElement = new Zend_Form_Element_Text('discount_real');
    	$discountRealElement->setAttribs(array('readonly'=>'readonly','class'=>'input100px',));
    	$this->addElement($discountRealElement);
    	
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onChange'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	
    	$paidRealElement = new Zend_Form_Element_Text('paid');
    	$paidRealElement->setAttribs(array('class'=>'input100px','onChange'=>'Total1()',));
    	$this->addElement($paidRealElement);
    	
    	$remainlElement = new Zend_Form_Element_Text('remain');
    	$remainlElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($remainlElement);
    	
    	$statusElement = new Zend_Form_Element_Select('status');
    	//$statusElement->setAttribs(array('readonly'=>'readonly',));
    	//$statusElement->setValue("Open");
    	$opt = array(""=>$tr->translate('Please_Select'),1=>$tr->translate("Qoute"),2=>$tr->translate("Open"),3=>$tr->translate("In Progress"),4=>$tr->translate("Paid"),5=>$tr->translate("Full Delivery"),6=>$tr->translate("Cancell"));
    	$statusElement->setMultiOptions($opt);
    	$statusElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($statusElement);
    	
    	$statusquoteElement = new Zend_Form_Element_Text('quote');
    	$statusquoteElement->setAttribs(array('readonly'=>'readonly',));
    	$statusquoteElement->setValue("Quote");
    	$this->addElement($statusquoteElement);
    	
    	$date = new Zend_Date();
    	$dateOrderElement = new Zend_Form_Element_Text('order_date');
    	$dateOrderElement->setAttribs(array('placeholder' => 'Click To Choose Date','class'=>'validate[required]'));
    	$dateOrderElement ->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($dateOrderElement);
    	 
    	$contactElement = new Zend_Form_Element_Text('contact');
    	$contactElement->setAttribs(array('placeholder' => 'Enter Contace Name'));
    	$this->addElement($contactElement);
    	
    	$addressElement = new Zend_Form_Element_Text('address');
    	$this->addElement($addressElement);
    	 
    	$termElement = new Zend_Form_Element_Text('term');
    	$termElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($termElement);
    	
    	$branchElement = new Zend_Form_Element_Text('branch');
    	$branchElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($branchElement);
    	 
    	$orderidElement = new Zend_Form_Element_Text('orderid');
    	$this->addElement($orderidElement);
    
    	$dateElement = new Zend_Form_Element_Text('date');
    	$this->addElement($dateElement);
    	 
//     	$salesElement = new Zend_Form_Element_Text('sales_ref');
//     	$this->addElement($salesElement);
    	
//     	$rowsagent= $db->getGlobalDb('SELECT agent_id,name FROM tb_sale_agent ORDER BY agent_id DESC');
//     	$option_agent=array(''=>'Please Select','-1'=>'Add New Sale Agent');
//     	if($rowsagent) {
//     		foreach($rowsagent as $read_agent) $option_agent[$read_agent['agent_id']]=$read_agent['name'];
//     	}
//     	$sales_agentElement = new Zend_Form_Element_Select('sales_ref');
//     	$sales_agentElement->setMultiOptions($option_agent);
//     	$sales_agentElement->setAttribs(array('Onchange'=>'showAgentPopup()'));
//     	$this->addElement($sales_agentElement);  

    	$option="";
    	$sql = "SELECT id,name FROM tb_sale_agent WHERE name!='' ";
    	$option=array(""=>$tr->translate("Please_Select"),"-1"=>$tr->translate("Add_New_Sale_Agent"));
    	if($user["level"]==1 OR $user["level"]== 2){
    		//$option=array(""=>"Please Select","-1"=>"Add New Sale Agent");
    	}
    	else{
    		//$option=array(""=>"Please Select");
    		//$sql.=" AND agent_id =".$user["location_id"];
    	}
    	$sql.=" ORDER BY id DESC";
    	$rs=$db->getGlobalDb($sql);
    	$agent_value = $request->getParam('sales_ref');
    	if(!empty($rs)) foreach($rs as $read) $option[$read['id']]=$read['name'];
    	$sales_agentId = new Zend_Form_Element_Select('sales_ref');
    	$sales_agentId->setAttribs(array(
    			'class'=>'validate[required]',
    			'id'=>'sales_ref' ,
    			'Onchange'=>'showAgentPopup()'));
        $sales_agentId->setMultiOptions($option);
    	//$sales_agentId->setValue($agent_value);
    	$this->addElement($sales_agentId);
    	
    	$reqDateElement = new Zend_Form_Element_Text('rs-date');
    	$this->addElement($reqDateElement);
    	 
    	$remarkElement = new Zend_Form_Element_Textarea('remark');
    	$this->addElement($remarkElement);
    	 
    	 
    	$type_tax1Element = new Zend_Form_Element_Text('type-tax1');
    	$this->addElement($type_tax1Element);
    	 
    	$type_tax2Element = new Zend_Form_Element_Text('type-tax2');
    	$this->addElement($type_tax2Element);
    	 
    	$totalElement = new Zend_Form_Element_Text('total');
    	$this->addElement($totalElement);
    	
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$paidElement -> setAttribs(array('Onblur'=>'doRemain()'));
    	$this->addElement($paidElement);
    	
    	$idElement = new Zend_Form_Element_Text('id');
    	$this->addElement($idElement);
    	$idElement ->setValue($data["order_id"]);
    	
    	$is_delivery = new Zend_Form_Element_Select("is_delivery");
    	$opt_delivery = array(1=>"Yes",0=>"No");
    	$is_delivery->setMultiOptions($opt_delivery);
    	$this->addElement($is_delivery);
    	
    	$print_option = new Zend_Form_Element_Select("print_type");
    	$opt_print = array(''=>"Please select Print type",1=>"Invoice",2=>"All Delivery",3=>"Invoice And Delivery");
    	$print_option->setMultiOptions($opt_print);
    	$print_option->setAttribs(array(
    			'class'=>'validate[required]' ,
    			'Onchange'=>'printType()'));
    	$this->addElement($print_option);
    	
    	
    	Application_Form_DateTimePicker::addDateField(array('order_date','offer_date','paid_date'));
    		if($data != null) {
    			$idElement = new Zend_Form_Element_Hidden('id');
    			$this->addElement($idElement);
    			$idElement ->setValue($data["order_id"]);
    			
    			$oldlocationIdElement = new Zend_Form_Element_Hidden('old_location');
    			$this->addElement($oldlocationIdElement);
    		
    			$oldStatusElement = new Zend_Form_Element_Hidden('oldStatus');
    			$this->addElement($oldStatusElement);
    			$customerID->setValue($data["customer_id"]);
    			$contactElement->setValue($data['contact_name']);
    			$phoneElement->setValue($data['phone']);
    			    			
    			$remarkaddElement->setValue($data['add_name']);
    			
    			 if($data['status']==1){
    			 	$statusElement->setValue("Quote");
    			 }
    			 elseif($data['status']==2){
    			 	$statusElement->setValue("Open");
    			 }
    			 elseif($data['status']==3){
    			 	$statusElement->setValue("In Progress");
    			 }
    			 elseif($data['status']==4){
    			 	$statusElement->setValue("Paid");
    			 }elseif($data['status']==5){
    			 	$statusElement->setValue("Full Delivery");
    			 }
    			 else{
    			 	$statusElement->setValue("Cancel");
    			 }
    			//$idElement->setValue($data['id']);
    			$statusElement->setValue($data['status']);
    			$oldStatusElement->setValue($data['status']);
    			$sales_agentId->setValue($data['sales_ref']);
    			$locationID->setvalue($data['LocationId']);
    			$oldlocationIdElement->setvalue($data['LocationId']);
    			$dateOrderElement->setValue($data["date_order"]);
    			$orderElement->setAttribs(array("readonly"=>"readonly"));
    			$orderElement->setValue($data['order']);
    			$paymentmethodElement->setValue($data['payment_method']);
    			$currencyElement->setValue($data['currency_id']);
    			$remarkElement->setValue($data["remark"]);
    			$paidElement->setValue($data['paid']);
    			$remainlElement->setvalue($data['balance']);
    			$allTotalElement->setValue($data['all_total']);
    			$discountValueElement->setValue($data['discount_value']);
    			$netTotalElement->setValue($data['net_total']);  
    		
    		} else {$discountTypeElement->setValue(1);
    		}
     	return $this;
    
    }
    // ***********************************  Purchase Form *****************************************
    
    public function PurchaseOrder($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	$db=new Application_Model_DbTable_DbGlobal();
    	 
    	$paymentElement = new Zend_Form_Element_Submit('payment');
    	$paymentElement->setAttribs(array('Phone'=>'Phone'));
    	$this->addElement($paymentElement);
    	 
    	$rs=$db->getGlobalDb('SELECT customer_id, cust_name FROM tb_customer WHERE cust_name!="" AND is_active=1 ');
    	$customerValue = $request->getParam('users');
    	$options="";
    	$options=array(''=>$tr->translate('Please_Select'),'-1'=>$tr->translate('ADD_CUSTOMER_ORDER'));
    	if(!empty($rs)) foreach($rs as $read) $options[$read['customer_id']]=$read['cust_name'];
    	$customerID=new Zend_Form_Element_Select('customer_id');
    	$customerID ->setAttribs(array( 'id'=>'customer_id',
    			'Onchange'=>'getCustomerInfo()',
    			'class'=>'validate[required]'
    			//	validate[required]
    	));
    	$customerID ->setMultiOptions($options);
    	//$customerID->setattribs(array(id'=>'customer_id','onchange'=>'this.form.submit()',));
    	$customerID->setValue($customerValue);
    	$this->addElement($customerID);
    	 
    	$orderElement = new Zend_Form_Element_Text('order');
    	$orderElement ->setAttribs(array('placeholder' => 'Optional'));
    	$this->addElement($orderElement);
    	 
    	$phoneElement = new Zend_Form_Element_Text('txt_phone');
    	$phoneElement->setAttribs(array('placeholder' => 'Enter Contact Number'));
    	$this->addElement($phoneElement);
    	 
    	$user= $this->GetuserInfo();
    	$options="";
    	$sql = "SELECT LocationId, Name FROM tb_sublocation WHERE Name!='' AND status = 1 ";
    	if($user["level"]==1 OR $user["level"]== 2){
    		$options=array("1"=>$tr->translate("Please_Select"),"-1"=>$tr->translate("ADD_NEW_LOCATION"));
    	}
    	else{
    		$sql.=" ANd LocationId = ".$user["location_id"];
    	}
    	$sql.=" ORDER BY LocationId DESC";
    	$rs=$db->getGlobalDb($sql);
    	$productValue = $request->getParam('LocationId');
    	if(!empty($rs)) foreach($rs as $read) $options[$read['LocationId']]=$read['Name'];
    	$locationID = new Zend_Form_Element_Select('LocationId');
    	$locationID->setAttribs(array(
    			'id'=>'LocationId',
    			'Onchange'=>'AddLocation()',
    			'class'=>'validate[required]',
    			//validate[required]
    	));
    	$locationID->setMultiOptions($options);
    	 
    	$locationID->setValue($productValue);
    	$this->addElement($locationID);
    
    	$rowsmethodpay= $db->getGlobalDb('SELECT * FROM tb_paymentmethod');
    	if($rowsmethodpay) {
    		foreach($rowsmethodpay as $readCategory) $option_method[$readCategory['payment_typeId']]=$readCategory['payment_name'];
    	}
    	$paymentmethodElement = new Zend_Form_Element_Select('payment_name');
    	$paymentmethodElement->setMultiOptions($option_method);
    	$this->addElement($paymentmethodElement);
    	 
    	$rowsPayment = $db->getGlobalDb('SELECT CurrencyId, Description,Symbol FROM tb_currency');
    	if($rowsPayment) {
    		foreach($rowsPayment as $readPayment) $options_curr[$readPayment['CurrencyId']]=$readPayment['Description'].$readPayment['Symbol'];
    	}
    	$currencyElement = new Zend_Form_Element_Select('currency');
    	$currencyElement->setMultiOptions($options_curr);
    	$this->addElement($currencyElement);
    	 
    	$descriptionElement = new Zend_Form_Element_Textarea('remark');
    	$this->addElement($descriptionElement);
    	 
    	$remarkaddElement = new Zend_Form_Element_Textarea('remark_add');
    	$this->addElement($remarkaddElement);
    	 
    	$allTotalElement = new Zend_Form_Element_Text('all_total');
    	$allTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($allTotalElement);
    	 
    	$discountTypeElement = new Zend_Form_Element_Radio('discount_type');
    	$discountTypeElement->setMultiOptions(array(1=>'%',2=>'Fix Value'));
    	$discountTypeElement->setAttribs(array('checked'=>'checked',));
    	$discountTypeElement->setAttribs(array('onChange'=>'doTotal()',));
    	$this->addElement($discountTypeElement);
    	 
    	$netTotalElement = new Zend_Form_Element_Text('net_total');
    	$netTotalElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($netTotalElement);
    	 
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onChange'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	 
    	$discountRealElement = new Zend_Form_Element_Text('discount_real');
    	$discountRealElement->setAttribs(array('readonly'=>'readonly','class'=>'input100px',));
    	$this->addElement($discountRealElement);
    	 
    	$discountValueElement = new Zend_Form_Element_Text('discount_value');
    	$discountValueElement->setAttribs(array('class'=>'input100px','onChange'=>'doTotal()',));
    	$this->addElement($discountValueElement);
    	 
    	$paidRealElement = new Zend_Form_Element_Text('paid');
    	$paidRealElement->setAttribs(array('class'=>'input100px','onChange'=>'Total1()',));
    	$this->addElement($paidRealElement);
    	 
    	$remainlElement = new Zend_Form_Element_Text('remain');
    	$remainlElement->setAttribs(array('readonly'=>'readonly',));
    	$this->addElement($remainlElement);
    	 
    	$statusElement = new Zend_Form_Element_Select('status');
    	//$statusElement->setAttribs(array('readonly'=>'readonly',));
    	//$statusElement->setValue("Open");
    	$opt = array(""=>$tr->translate('Please_Select'),1=>$tr->translate("Qoute"),2=>$tr->translate("Open"),3=>$tr->translate("In Progress"),5=>$tr->translate("Paid"),6=>$tr->translate("Cancell"));
    	$statusElement->setMultiOptions($opt);
    	$this->addElement($statusElement);
    	 
    	$statusquoteElement = new Zend_Form_Element_Text('quote');
    	$statusquoteElement->setAttribs(array('readonly'=>'readonly',));
    	$statusquoteElement->setValue("Quote");
    	$this->addElement($statusquoteElement);
    	 
    	$date = new Zend_Date();
    	$dateOrderElement = new Zend_Form_Element_Text('order_date');
    	$dateOrderElement->setAttribs(array('placeholder' => 'Click To Choose Date','class'=>'validate[required]'));
    	$dateOrderElement ->setValue($date->get('YYYY-MM-dd'));
    	$this->addElement($dateOrderElement);
    
    	$contactElement = new Zend_Form_Element_Text('contact');
    	$contactElement->setAttribs(array('placeholder' => 'Enter Contace Name'));
    	$this->addElement($contactElement);
    	 
    	$addressElement = new Zend_Form_Element_Text('address');
    	$this->addElement($addressElement);
    
    	$termElement = new Zend_Form_Element_Text('term');
    	$termElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($termElement);
    	 
    	$branchElement = new Zend_Form_Element_Text('branch');
    	$branchElement->setAttribs(array('class'=>'validate[required]',));
    	$this->addElement($branchElement);
    
    	$orderidElement = new Zend_Form_Element_Text('orderid');
    	$this->addElement($orderidElement);
    
    	$dateElement = new Zend_Form_Element_Text('date');
    	$this->addElement($dateElement);
    
    	//     	$salesElement = new Zend_Form_Element_Text('sales_ref');
    	//     	$this->addElement($salesElement);
    	 
    	//     	$rowsagent= $db->getGlobalDb('SELECT agent_id,name FROM tb_sale_agent ORDER BY agent_id DESC');
    	//     	$option_agent=array(''=>'Please Select','-1'=>'Add New Sale Agent');
    	//     	if($rowsagent) {
    	//     		foreach($rowsagent as $read_agent) $option_agent[$read_agent['agent_id']]=$read_agent['name'];
    	//     	}
    	//     	$sales_agentElement = new Zend_Form_Element_Select('sales_ref');
    	//     	$sales_agentElement->setMultiOptions($option_agent);
    	//     	$sales_agentElement->setAttribs(array('Onchange'=>'showAgentPopup()'));
    	//     	$this->addElement($sales_agentElement);
    
    	$option="";
    	$sql = "SELECT agent_id,name FROM tb_sale_agent WHERE name!='' ";
    	$option=array(""=>$tr->translate("Please_Select"),"-1"=>$tr->translate("Add_New_Sale_Agent"));
    	if($user["level"]==1 OR $user["level"]== 2){
    		//$option=array(""=>"Please Select","-1"=>"Add New Sale Agent");
    	}
    	else{
    		//$option=array(""=>"Please Select");
    		//$sql.=" AND agent_id =".$user["location_id"];
    	}
    	$sql.=" ORDER BY agent_id DESC";
    	$rs=$db->getGlobalDb($sql);
    	$agent_value = $request->getParam('sales_ref');
    	if(!empty($rs)) foreach($rs as $read) $option[$read['agent_id']]=$read['name'];
    	$sales_agentId = new Zend_Form_Element_Select('sales_ref');
    	$sales_agentId->setAttribs(array(
    			'class'=>'validate[required]',
    			'id'=>'sales_ref' ,
    			'Onchange'=>'showAgentPopup()'));
    	$sales_agentId->setMultiOptions($option);
    	$sales_agentId->setValue($agent_value);
    	$this->addElement($sales_agentId);
    	 
    	$reqDateElement = new Zend_Form_Element_Text('rs-date');
    	$this->addElement($reqDateElement);
    
    	$remarkElement = new Zend_Form_Element_Textarea('remark');
    	$this->addElement($remarkElement);
    
    
    	$type_tax1Element = new Zend_Form_Element_Text('type-tax1');
    	$this->addElement($type_tax1Element);
    
    	$type_tax2Element = new Zend_Form_Element_Text('type-tax2');
    	$this->addElement($type_tax2Element);
    
    	$totalElement = new Zend_Form_Element_Text('total');
    	$this->addElement($totalElement);
    	 
    	$paidElement = new Zend_Form_Element_Text('paid');
    	$paidElement -> setAttribs(array('onChange'=>'doRemain()'));
    	$this->addElement($paidElement);
    	
    	$historyElement = new Zend_Form_Element_Text('history_id');
    	$this->addElement($historyElement);
    	 
    	Application_Form_DateTimePicker::addDateField(array('order_date','offer_date','paid_date'));
    	if($data != null) {
    		$idElement = new Zend_Form_Element_Hidden('id');
    		$this->addElement($idElement);
    		$idElement ->setValue($data["order_id"]);
    		 
    		$oldlocationIdElement = new Zend_Form_Element_Hidden('old_location');
    		$this->addElement($oldlocationIdElement);
    
    		$oldStatusElement = new Zend_Form_Element_Hidden('oldStatus');
    		$this->addElement($oldStatusElement);
    		
    		$customerID->setValue($data["customer_id"]);
    		$contactElement->setValue($data['contact_name']);
    		$phoneElement->setValue($data['phone']);
    
    		$remarkaddElement->setValue($data['add_name']);
    		if($data["status"]==1){
    			$statusElement->setValue("Quote");
    		}
    		elseif($data["status"]==2){
    			$statusElement->setValue("Open");
    		}
    		elseif($data["status"]==3){
    			$statusElement->setValue("In Progress");
    		}
    		elseif($data["status"]==4){
    			$statusElement->setValue("Paid");
    		}
    		else{
    			$statusElement->setValue("Cancel");
    		}
    		//$idElement->setValue($data['id']);
    		 
    		$oldStatusElement->setValue($data['status']);
    		$sales_agentId->setValue($data['sales_ref']);
    		$locationID->setvalue($data['LocationId']);
    		$oldlocationIdElement->setvalue($data['LocationId']);
    		$dateOrderElement->setValue($data["date_order"]);
    		$orderElement->setAttribs(array("readonly"=>"readonly"));
    		$orderElement->setValue($data['order']);
    		$paymentmethodElement->setValue($data['payment_method']);
    		$currencyElement->setValue($data['currency_id']);
    		$remarkElement->setValue($data["remark"]);
    		$paidElement->setValue($data['paid']);
    		$remainlElement->setvalue($data['balance']);
    		$allTotalElement->setValue($data['all_total']);
    		$discountValueElement->setValue($data['discount_value']);
    		$netTotalElement->setValue($data['net_total']);
    
    	} else {$discountTypeElement->setValue(1);
    	}
    	return $this;
    
    }
	
}


<?php

class Sales_Model_DbTable_Dbpayment extends Zend_Db_Table_Abstract
{
	//use for add purchase order 29-13
	protected $_name="tb_receipt";
	function getAllReciept($search){
			$db= $this->getAdapter();
			$sql=" SELECT r.id,r.`receipt_no`,
			(SELECT s.name FROM `tb_sublocation` AS s WHERE s.id = r.`branch_id` AND STATUS=1 AND NAME!='' LIMIT 1) AS branch_name,
			(SELECT c.cust_name FROM `tb_customer` AS c WHERE c.id=r.customer_id LIMIT 1 ) AS customer_name,
			r.`date_input`,
			r.`total`,r.`paid`,r.`balance`,r.`payment_id`,r.`payment_type`,
			(SELECT u.fullname FROM `tb_acl_user` AS u WHERE u.user_id = r.`user_id`) AS user_name 
			FROM `tb_receipt` AS r ";
			
			$from_date =(empty($search['start_date']))? '1': " r.`date_input` >= '".$search['start_date']." 00:00:00'";
			$to_date = (empty($search['end_date']))? '1': " r.`date_input` <= '".$search['end_date']." 23:59:59'";
			$where = " WHERE ".$from_date." AND ".$to_date;
			if(!empty($search['text_search'])){
				$s_where = array();
				$s_search = trim(addslashes($search['text_search']));
				$s_where[] = " r.`receipt_no` LIKE '%{$s_search}%'";
				$s_where[] = " r.`total` LIKE '%{$s_search}%'";
				$s_where[] = " r.`paid` LIKE '%{$s_search}%'";
				$s_where[] = " r.`balance` LIKE '%{$s_search}%'";
				$where .=' AND ('.implode(' OR ',$s_where).')';
			}
			if($search['branch_id']>0){
				$where .= " AND r.`branch_id` = ".$search['branch_id'];
			}
			if($search['customer_id']>0){
				$where .= " AND r.customer_id =".$search['customer_id'];
			}
			$dbg = new Application_Model_DbTable_DbGlobal();
			$where.=$dbg->getAccessPermission();
			$order=" ORDER BY id DESC ";
			return $db->fetchAll($sql.$where.$order);
	}
	public function addReceiptPayment($data)
	{
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
			
			$ids=explode(',',$data['identity']);
			$branch_id = '';
			foreach ($ids as $row){
				$branch_id = $this->getBranchByInvoice($data['invoice_no'.$row]);
			}
			
			$info_purchase_order=array(
					"branch_id"   => 	$branch_id['branch_id'],
					"customer_id"     => 	$data["customer_id"],
					"payment_type"       => 	$data["payment_method"],//payment by customer/invoice
					"payment_id"     => 	$data["payment_name"],	//payment by cash/paypal/cheque
					"receipt_no"  => 	$data['receipt'],
					"receipt_date"    => $data['date_in'],
					"total"         => 	$data['all_total'],
					"paid"      => 	$data['paid'],
					"balance" => 	$data['balance'],
					"remark"      => 	$data['remark'],
					"user_id"       => 	$GetUserId,
					'status' =>1,
					"date_input"      => 	date("Y-m-d"),
			);
			$this->_name="tb_receipt";
			$reciept_id = $this->insert($info_purchase_order); 
			unset($info_purchase_order);

			$ids=explode(',',$data['identity']);
			$count = count($ids);
			$paid = $data['paid'];
			foreach ($ids as $key => $i)
			{
				$paid = $paid -$data['paid_amount'.$i];
				$recipt_paid = 0;
				if ($paid>=0){
					$paided = 0;
					$recipt_paid = $data['paid_amount'.$i];
					$balance=$data['balance_after'.$i];
				}else{
					$paided = ($data['paid_amount'.$i]- abs($paid));
					$recipt_paid = ($data['paid_amount'.$i]- abs($paid));
					$balance= $data['balance_after'.$i]+abs($paid);
					$paid  = 0;
				}
				$data_item= array(
						'receipt_id'=> $reciept_id,
						'invoice_id'	  => 	$data['invoice_no'.$i],
						'total'=>$data['subtotal'.$i],
						'discount'  => 	$data['discount'.$i],
						'paid'	  => 	$recipt_paid,
						'balance'		  => 	$balance,
						'is_completed'   =>    1,
						'status'  => 1,
						'date_input'	  => date("Y-m-d"),
				);
				$this->_name='tb_receipt_detail';
				$this->insert($data_item);
				
					$data_invoice = array(
							'discount_after'	  => 	0,
							'paid_after'	  => 	$paided,
							'balance_after'	  => 	$balance,
							'is_fullpaid'	  => 	1,
							);
				$this->_name='tb_invoice';
				$where = 'id = '.$data['invoice_no'.$i];
				$this->update($data_invoice, $where);
				
				if ($key== ($count-1)){
						if ($paid>0){
							$idss= explode(',',$data['identity']);
							foreach ($idss as $k)
							{
								$paid = $paid - $data['balance_after'.$k];
								if ($paid>=0){
									$paided = 0;
									$recipt_paid =$data['balance_after'.$k]+$data['paid_amount'.$k];
								}else{
									$paided = abs($paid);
									$recipt_paid = $data['paid_amount'.$k]+($data['balance_after'.$k] - $paided);
									$paid=0;
								}
								$data_item= array(
										'paid'	  => 	$recipt_paid,
										'balance'		  => 	$paided,
										'is_completed'   =>    1,
										'status'  => 1,
								);
								$this->_name='tb_receipt_detail';
								$wheres = 'invoice_id = '.$data['invoice_no'.$k];
								$this->update($data_item, $wheres);
								
								$data_invoice = array(
										'balance_after'	  => 	$paided,
										'is_fullpaid'	  => 	1,
								);
								$this->_name='tb_invoice';
								$where = 'id = '.$data['invoice_no'.$k];
								$this->update($data_invoice, $where);
							}
						}
				}
				
			 }
			
			 
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	public function updatePayment($data){
		$db = $this->getAdapter();
		$db->beginTransaction();
		try{
			$id = $data["id"];
			$db_global = new Application_Model_DbTable_DbGlobal();
			$session_user=new Zend_Session_Namespace('auth');
			$userName=$session_user->user_name;
			$GetUserId= $session_user->user_id;
				
			$ids=explode(',',$data['identity']);
			$branch_id = '';
			foreach ($ids as $row){
				$branch_id = $this->getBranchByInvoice($data['invoice_no'.$row]);
				$data_invoice = array(
						'discount_after'	  => 	$branch_id['discount'],
						'paid_after'	  => 	$branch_id['paid_amount'],
						'balance_after'	  => 	$branch_id['balance'],
						'is_fullpaid'	  => 	0,
				);
				$this->_name='tb_invoice';
				$where = 'id = '.$data['invoice_no'.$row];
				$this->update($data_invoice, $where); // Reset Invoice like As The First
				unset($data_invoice);
			}
				
			$info_purchase_order=array(
					"branch_id"   => 	$branch_id['branch_id'],
					"customer_id"     => 	$data["customer_id"],
					"payment_type"       => 	$data["payment_method"],//payment by customer/invoice
					"payment_id"     => 	$data["payment_name"],	//payment by cash/paypal/cheque
					"receipt_no"  => 	$data['receipt'],
					"receipt_date"    => $data['date_in'],
					"total"         => 	$data['all_total'],
					"paid"      => 	$data['paid'],
					"balance" => 	$data['balance'],
					"remark"      => 	$data['remark'],
					"user_id"       => 	$GetUserId,
					'status' =>1,
					"date_input"      => 	date("Y-m-d"),
			);
			$this->_name="tb_receipt";
			$where_reciept="id = ".$id;
			$this->update($info_purchase_order, $where_reciept);
			unset($info_purchase_order);
		
			$this->_name='tb_receipt_detail';
			$where_detail = " receipt_id =".$id;
			$this->delete($where_detail);
			
			$ids=explode(',',$data['identity']);
			$count = count($ids);
			$paid = $data['paid'];
			foreach ($ids as $key => $i)
			{
				$invoice = $this->getBranchByInvoice($data['invoice_no'.$i]);
				//print_r($invoice);exit();
// 				$paid = $paid -$invoice['paid_amount'];
// 				$recipt_paid = 0;
// 				if ($paid>=0){
// 					$paided = 0;
// 					$recipt_paid = $invoice['paid_amount'];
// 					$balance= $invoice['balance'];
// 				}else{
// 					$paided = ($invoice['paid_amount']- abs($paid));
// 					$recipt_paid = ($invoice['paid_amount']- abs($paid));
// 					$balance= $invoice['balance']+abs($paid);
// 					$paid  = 0;
// 				}
// 				$data_item= array(
// 						'receipt_id'=> $id,
// 						'invoice_id'	  => 	$data['invoice_no'.$i],
// 						'total'=>$invoice['sub_total'],
// 						'discount'  => 	$invoice['discount'],
// 						'paid'	  => 	$recipt_paid,
// 						'balance'		  => 	$balance,
// 						'is_completed'   =>    1,
// 						'status'  => 1,
// 						'date_input'	  => date("Y-m-d"),
// 				);
// 				$this->_name='tb_receipt_detail';
// 				$this->insert($data_item);
				
// 				$data_invoice = array(
// 						'discount_after'	  => 	0,
// 						'paid_after'	  => 	$paided,
// 						'balance_after'	  => 	$balance,
// 						'is_fullpaid'	  => 	1,
// 				);
// 				$this->_name='tb_invoice';
// 				$where = 'id = '.$data['invoice_no'.$i];
// 				$this->update($data_invoice, $where);
				
// 				if ($key== ($count-1)){
// 					if ($paid>0){
// 						$idss= explode(',',$data['identity']);
// 						foreach ($idss as $k)
// 						{
// 							$paid = $paid - $invoice['balance'];
// 							if ($paid>=0){
// 								$paided = 0;
// 								$recipt_paid =$invoice['balance']+$invoice['paid_amount'];
// 							}else{
// 								$paided = abs($paid);
// 								$recipt_paid = $invoice['paid_amount']+($invoice['balance'] - $paided);
// 								$paid=0;
// 							}
// 							$data_item= array(
// 									'paid'	  => 	$recipt_paid,
// 									'balance'		  => 	$paided,
// 									'is_completed'   =>    1,
// 									'status'  => 1,
// 							);
// 							$this->_name='tb_receipt_detail';
// 							$wheres = 'invoice_id = '.$data['invoice_no'.$k];
// 							$this->update($data_item, $wheres);
				
// 							$data_invoice = array(
// 									'balance_after'	  => 	$paided,
// 									'is_fullpaid'	  => 	1,
// 							);
// 							$this->_name='tb_invoice';
// 							$where = 'id = '.$data['invoice_no'.$k];
// 							$this->update($data_invoice, $where);
// 						}
// 					}
// 				}

			}
				
		
			$db->commit();
		}catch(Exception $e){
			$db->rollBack();
			Application_Form_FrmMessage::message('INSERT_FAIL');
			$err =$e->getMessage();
			echo $err;exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		}
	}
	function getBranchByInvoice($invoice_id){
		$db =$this->getAdapter();
		$sql="SELECT * FROM `tb_invoice` AS i WHERE i.`id` = $invoice_id LIMIT 1";
		return $db->fetchRow($sql);
	}
	function getRecieptById($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM $this->_name WHERE id = $id LIMIT 1 ";
		return $db->fetchRow($sql);
	}
	function getRecieptDetail($reciept_id){
		$db= $this->getAdapter();
		$sql="SELECT d.`id`,d.`receipt_id`,d.`invoice_id`,
		( SELECT i.invoice_no FROM `tb_invoice` AS i  WHERE i.id = d.`invoice_id` ) AS invoice_no,
		d.`total`,d.`paid`,d.`balance`,d.`discount`,d.`date_input`
		FROM `tb_receipt_detail` AS d WHERE d.`receipt_id` =".$reciept_id;
		return $db->fetchAll($sql);
	}
	function getSaleorderItemDetailid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_salesorder_item` WHERE saleorder_id=$id ";
		return $db->fetchAll($sql);
	}
	function getTermconditionByid($id){
		$db = $this->getAdapter();
		$sql=" SELECT * FROM `tb_quoatation_termcondition` WHERE quoation_id=$id AND term_type=2 ";
		return $db->fetchAll($sql);
	} 
}
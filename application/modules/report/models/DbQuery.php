<?php 
Class report_Model_DbQuery extends Zend_Db_Table_Abstract{
	
	protected function GetuserInfo(){
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		return $result;
	}
	public function getAllPurchaseReport($search){//1
		$db= $this->getAdapter();
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=tb_purchase_order.vendor_id LIMIT 1 ) AS vendor_name,
		order_number,date_order,date_in,
		(SELECT symbal FROM `tb_currency` WHERE id= currency_id limit 1) As curr_name,
		currency_id,
		net_total,paid,balance,
		(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1) As purchase_status,
		(SELECT name_en FROM `tb_view` WHERE key_code =tb_purchase_order.status AND type=2 LIMIT 1),
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = user_mod LIMIT 1 ) AS user_name
		FROM `tb_purchase_order` ";
		$from_date =(empty($search['start_date']))? '1': " date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date_order <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " order_number LIKE '%{$s_search}%'";
			$s_where[] = " net_total LIKE '%{$s_search}%'";
			$s_where[] = " paid LIKE '%{$s_search}%'";
			$s_where[] = " balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['suppliyer_id']>0){
			$where .= " AND vendor_id = ".$search['suppliyer_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductPruchaseById($id){//2
		$db = $this->getAdapter();
		$sql=" SELECT 
				 (SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
				 p.order_number,p.date_order,p.date_in,p.remark,
				 (SELECT item_name FROM `tb_product` WHERE id= po.pro_id LIMIT 1) AS item_name,
				 (SELECT item_code FROM `tb_product` WHERE id=po.pro_id LIMIT 1 ) AS item_code,
				 (SELECT symbal FROM `tb_currency` WHERE id=p.currency_id limit 1) As curr_name,
				 (SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
				 (SELECT v_phone FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS v_phone,
				 (SELECT contact_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS contact_name,
				 (SELECT add_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS add_name,
				 (SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1 LIMIT 1) As purchase_status,
				 (SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = p.user_mod LIMIT 1 ) AS user_name,
				 po.qty_order,po.price,po.sub_total,p.net_total,
				 p.paid,p.discount_real,p.tax,
				 p.balance
				 FROM `tb_purchase_order` AS p,
				`tb_purchase_order_item` AS po WHERE p.id=po.purchase_id
				 AND po.status=1 AND p.id = $id ";
		return $db->fetchAll($sql);
	}
	function getPruchaseProductDetail($search){//3
		$db = $this->getAdapter();
		$sql=" SELECT
			(SELECT name FROM `tb_sublocation` WHERE id=p.branch_id) AS branch_name,
			it.item_name,
		    it.item_code,
			(SELECT name FROM `tb_category` WHERE id=it.cate_id LIMIT 1) AS cate_name,
		    (SELECT name FROM `tb_brand` WHERE id=it.brand_id LIMIT 1) AS brand_name,
			(SELECT symbal FROM `tb_currency` WHERE id=p.currency_id limit 1) As curr_name,
			(SELECT v_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS vendor_name,
			(SELECT v_phone FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS v_phone,
			(SELECT contact_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS contact_name,
			(SELECT add_name FROM `tb_vendor` WHERE tb_vendor.vendor_id=p.vendor_id LIMIT 1 ) AS add_name,
			(SELECT name_en FROM `tb_view` WHERE key_code = purchase_status AND `type`=1 LIMIT 1) As purchase_status,
			(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = p.user_mod LIMIT 1 ) AS user_name,
			po.qty_order,po.price,po.sub_total,p.currency_id,p.net_total,
			p.id,p.order_number,p.date_order,p.date_in,p.remark,
			p.paid,p.discount_real,p.tax,
			p.balance
			FROM `tb_purchase_order` AS p,
			`tb_purchase_order_item` AS po,
             tb_product AS it
			 WHERE p.id=po.purchase_id AND it.id=po.pro_id 
			AND po.status=1  AND p.status=1 ";
		$from_date =(empty($search['start_date']))? '1': " p.date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " p.date_order <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['txt_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['txt_search']));
			$s_where[] = " it.item_name LIKE '%{$s_search}%'";
			$s_where[] = " it.item_code LIKE '%{$s_search}%'";
			$s_where[] = " it.barcode LIKE '%{$s_search}%'";
			$s_where[] = " p.order_number LIKE '%{$s_search}%'";
			$s_where[] = " p.net_total LIKE '%{$s_search}%'";
			$s_where[] = " p.paid LIKE '%{$s_search}%'";
			$s_where[] = " p.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['item']>0){
			$where .= " AND it.id =".$search['item'];
		}
		if($search['category_id']>0){
			$where .= " AND it.cate_id =".$search['category_id'];
		}
		if($search['brand_id']>0){
			$where .= " AND it.brand_id =".$search['brand_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND p.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY p.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	public function getAllSaleOrderReport($search){//4
		$db= $this->getAdapter();
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT name FROM `tb_sale_agent` WHERE id=s.saleagent_id LIMIT 1) AS agent_name,
		s.sale_no,s.date_sold,s.all_total,s.tax,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id limit 1) As curr_name,
		s.currency_id,
		s.net_total,s.paid,s.balance,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name
		FROM `tb_sales_order` AS s ";
		$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " s.order_number LIKE '%{$s_search}%'";
			$s_where[] = " s.net_total LIKE '%{$s_search}%'";
			$s_where[] = " s.paid LIKE '%{$s_search}%'";
			$s_where[] = " s.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>0){
			$where .= " AND s.customer_id = ".$search['customer_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductSaleById($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		(SELECT NAME FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		s.sale_no,s.date_sold,s.remark,
		(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		so.qty_order,so.price,so.old_price,so.sub_total,s.net_total,
		s.paid,s.discount_real,s.tax,
		s.balance
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so WHERE s.id=so.saleorder_id
		AND s.status=1 AND s.id = $id ";
		return $db->fetchAll($sql);
	}
	function getSaleProductDetail($search){//6
		$db = $this->getAdapter();
		$sql=" SELECT
		(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		it.item_name,
		it.item_code,
		(SELECT name FROM `tb_category` WHERE id=it.cate_id LIMIT 1) AS cate_name,
		(SELECT name FROM `tb_brand` WHERE id=it.brand_id LIMIT 1) AS brand_name,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id limit 1) As curr_name,
		
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		so.qty_order,so.price,so.sub_total,s.currency_id,s.net_total,
		s.id,s.sale_no,s.date_sold,s.remark,
		s.paid,s.discount_real,s.tax,
		s.balance
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so,
		tb_product AS it
		WHERE s.id=so.saleorder_id AND it.id=so.pro_id
		AND s.status=1 ";
		$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['txt_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['txt_search']));
			$s_where[] = " it.item_name LIKE '%{$s_search}%'";
			$s_where[] = " it.item_code LIKE '%{$s_search}%'";
			$s_where[] = " it.barcode LIKE '%{$s_search}%'";
			$s_where[] = " s.order_number LIKE '%{$s_search}%'";
			$s_where[] = " s.net_total LIKE '%{$s_search}%'";
			$s_where[] = " s.paid LIKE '%{$s_search}%'";
			$s_where[] = " s.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['item']>0){
			$where .= " AND it.id =".$search['item'];
		}
		if($search['category_id']>0){
			$where .= " AND it.cate_id =".$search['category_id'];
		}
		if($search['brand_id']>0){
			$where .= " AND it.brand_id =".$search['brand_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND s.branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY s.id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllCustomer($search){//7
		$db = $this->getAdapter();
	
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE id=branch_id LIMIT 1) AS branch_name,
		cust_name,phone,
		(SELECT NAME FROM `tb_price_type` WHERE id=customer_level LIMIT 1) As level,
		contact_name,contact_phone,address,email,fax,website,remark,
		( SELECT name_en FROM `tb_view` WHERE type=5 AND key_code=status LIMIT 1) status,
		( SELECT fullname FROM `tb_acl_user` WHERE tb_acl_user.user_id=user_id LIMIT 1) AS user_name
		FROM `tb_customer` WHERE cust_name!=''  ";
	
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " cust_name LIKE '%{$s_search}%'";
			$s_where[] = " phone LIKE '%{$s_search}%'";
			
			$s_where[] = " contact_name LIKE '%{$s_search}%'";
			$s_where[] = " contact_phone LIKE '%{$s_search}%'";
			$s_where[] = " address LIKE '%{$s_search}%'";
				
			$s_where[] = " email LIKE '%{$s_search}%'";
			$s_where[] = " fax LIKE '%{$s_search}%'";
			$s_where[] = " website LIKE '%{$s_search}%'";
			$s_where[] = " remark LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
	
		if($search['branch_id']>0){
			$where .= " AND branch_id = ".$search['branch_id'];
		}
		if($search['customer_id']>0){
			$where .= " AND id = ".$search['customer_id'];
		}
		if($search['level']>0){
			$where .= " AND customer_level = ".$search['level'];
		}
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getAllSaleAgent($search){
		$sql = "SELECT sg.id,l.name AS branch_name,
			   sg.name, sg.phone, sg.email, sg.address, sg.job_title, sg.description
		FROM tb_sale_agent AS sg
		INNER JOIN tb_sublocation As l ON sg.branch_id = l.id WHERE 1 ";
		$order=" ORDER BY sg.id DESC ";
	
		$from_date =(empty($search['start_date']))? '1': " date >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " date <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " l.name LIKE '%{$s_search}%'";
			$s_where[] = " sg.name LIKE '%{$s_search}%'";
			$s_where[] = " sg.phone LIKE '%{$s_search}%'";
			$s_where[] = " sg.email LIKE '%{$s_search}%'";
			$s_where[] = " sg.address LIKE '%{$s_search}%'";
			$s_where[] = " sg.job_title LIKE '%{$s_search}%'";
			$s_where[] = " sg.description LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id = ".$search['branch_id'];
		}
		$order=" ORDER BY id DESC ";
		$db =$this->getAdapter();
		return $db->fetchAll($sql.$where.$order);
	}
	
	function getProductPurchaseDetail($search){
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
		$sql= " SELECT
		pur.id,
		pur.`order_number`,
		pur.`date_in`,
		p.item_name,
		p.item_code,
		SUM(pio.qty_order) AS qty_order,
		br.Name AS Brand,
		cg.Name AS cate_name,
		(SELECT
		NAME
		FROM
		tb_sublocation
		WHERE `id` = pur.`branch_id`) AS branch ,
		pl.qty AS qty_location
		FROM
		tb_product AS p,
		tb_category AS cg,
		tb_brand AS br,
		tb_purchase_order_item AS pio,
		tb_purchase_order AS pur,
		tb_prolocation AS pl
		WHERE p.id = pio.pro_id
		AND cg.id = p.cate_id
		AND br.id = p.brand_id
		AND pl.`id`=pur.`id`
		AND pl.`pro_id`=pio.`pro_id`
		AND pio.purchase_id = pur.id
		AND pur.`date_in` BETWEEN '$start_date' AND '$end_date'";
	}
	
	public function getItem($data){
		$db = $this->getAdapter();
    	$sql = "SELECT p.pro_id, p.item_name,p.item_code FROM tb_product AS p ";
			
    		if($data["LocationId"]!="" OR $data["LocationId"]!=0){
    			$sql.= " INNER JOIN tb_prolocation AS pl ON pl.pro_id = p.pro_id AND pl.`LocationId`= ".trim($data["LocationId"]);
    		}
    		$sql.="WHERE p.item_name!=''";
    		
			if($data["branch_id"]!="" OR $data["branch_id"]!=0){
				$sql.= " AND p.`brand_id`=".trim($data["branch_id"]);
			}
			if($data["category_id"]!="" OR $data["category_id"]!=0){
				$sql.=" AND p.`cate_id`=".trim($data["category_id"]);
			}
			$sql.=" ORDER BY p.item_name";
			echo $sql;
			return $db->fetchAll($sql);
	}
	
	public function getSalesItem($data){
		$db=$this->getAdapter();
		
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
		$sql= "SELECT 
					p.item_name,
					p.item_code,
					SUM(si.qty_order) AS qty_order,
					br.Name AS Brand,
					cg.Name AS cate_name,
					s.`sale_no`,
					s.`date_sold` , 
					(SELECT NAME FROM tb_sublocation WHERE `id` = s.`branch_id`) AS branch,
					pl.qty 
					FROM tb_product AS p,
					tb_category AS cg,
					tb_brand AS br,
					`tb_salesorder_item` AS si,
					tb_sales_order AS s ,
					tb_prolocation AS pl 
					WHERE p.id = si.pro_id AND cg.id = p.cate_id 
					AND br.id = p.brand_id 
					AND si.saleorder_id = s.id 
					AND pl.`pro_id`=si.`pro_id` 
					AND si.saleorder_id = s.id ";
				  

// 				  AND '$end_date' ";
			if(($data["item"]!="" AND $data["item"]!=0 )){
				//$sql.=" AND "."(p.item_name LIKE '%".trim($data["item"])."%' OR p.item_code LIKE '%".trim($data["item"]."%')");
				$sql.=" AND p.pro_id = ".trim($data["item"]);
			}
			if($data["category_id"]!="" AND $data["category_id"]!=0){
				$sql.=" AND cg.CategoryId = ".trim($data["category_id"]);
			}
			if($data["branch_id"]!="" AND $data["branch_id"]!=0){
				$sql.=" AND br.branch_id = ".trim($data["branch_id"]);
			}
			if($data["LocationId"]!="" AND $data["LocationId"]!=0){
				$sql.=" AND s.LocationId = ".trim($data["LocationId"]);
			}
			$result =  $this->GetuserInfo();
			if ($result["level"]!=1 AND $result["level"]!=2 ){
				$sql.=" AND s.LocationId = ".trim($result["location_id"]);
			}
// 			echo $sql;exit();
			
// 		$sql.=" GROUP BY si.id ORDER BY s.date_order DESC";
		return $db->fetchAll($sql);
	}
	public function getProductSummary($data){
		$db=$this->getAdapter();
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
		
		$sql_po=" SELECT po.LocationId, po.date_in, po.status, po.is_active, pi.pro_id, SUM( pi.qty_order ) AS qty_order
							FROM tb_purchase_order po, tb_purchase_order_item pi
							WHERE po.order_id = pi.order_id
							AND po.date_in
							BETWEEN  '$start_date'
							AND  '$end_date' ";
		$sql_so = " SELECT so.LocationId, so.date_order, so.status, so.is_active, si.pro_id, SUM( si.qty_order ) AS qty_sales
							FROM tb_sales_order so, tb_sales_order_item si
							WHERE so.order_id = si.order_id
							AND so.date_order
							BETWEEN  '$start_date'
							AND  '$end_date'  ";
		
		if($data["LocationId"]!="" AND $data["LocationId"]!=0){
			$sql_po.=" AND po.LocationId = ".trim($data["LocationId"]);
			$sql_so.=" AND so.LocationId = ".trim($data["LocationId"]);
		}
		
		$sql_po.=" GROUP BY pi.pro_id ";
		$sql_so.=" GROUP BY si.pro_id";
		
		$sql_vpo = " CREATE OR REPLACE VIEW v_po AS ".$sql_po;
		$sql_vso = " CREATE OR REPLACE VIEW v_so AS ".$sql_so;
						
		$db->query($sql_vpo);
		$db->query($sql_vso);
		
		$sql=" SELECT p.`pro_id` , p.item_name, p.item_code, po.qty_order AS qty_po, so.qty_sales AS qty_so,p.`qty_onhand`,p.`qty_onorder`,p.`qty_onsold`
					FROM tb_product AS p
					LEFT JOIN v_so AS so ON p.pro_id = so.pro_id ";
		if(($data["item"]!="" AND $data["item"]!=0 )){
			$sql.=" INNER JOIN v_po AS po ON p.pro_id = po.pro_id AND p.pro_id = ".trim($data["item"]);
		}
		else{
			$sql.=" LEFT JOIN v_po AS po ON p.pro_id = po.pro_id ";
		}
		if($data["category_id"]!="" AND $data["category_id"]!=0){
			$sql.=" AND p.cate_id = ".trim($data["category_id"]);
		}
		if($data["branch_id"]!="" AND $data["branch_id"]!=0){
			$sql.=" AND p.brand_id = ".trim($data["branch_id"]);
		}
		
			
		$sql.=" GROUP BY p.pro_id ORDER BY p.item_name  ";
		return $db->fetchAll($sql);
	}
	//get location name for report at other location
	public function getLocationName($location_id){
		$db=$this->getAdapter();
		$sql="SELECT name FROM tb_sublocation WHERE id = ".$location_id;
		$row=$db->fetchRow($sql);
		return $row;
	}
	public function getBrandByUser(){
		$db=$this->getAdapter();
		$user = $this->GetuserInfo();
		if($user["level"]==3 OR $user["level"]==4 ){
			$sql="SELECT Name FROM tb_sublocation WHERE LocationId = ".$user["location_id"];
			$row=$db->fetchRow($sql);
			return $row;
		}
		else{
			return false;
		}
		
	}
	
	public function getQtyTransfer($data){
		$db=$this->getAdapter();
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
	
// 		$sql = "SELECT p.item_name, p.item_code, SUM( ti.qty) AS qty,br.Name AS Brand,cg.Name AS cate_name
// 		FROM tb_product AS p,tb_category As cg,tb_branch AS br, tb_transfer_item AS ti, tb_stocktransfer AS t
// 			WHERE 
// 	             ti.transfer_id= t.transfer_id
// 	             AND p.pro_id = ti.pro_id
// 				 AND cg.CategoryId=p.cate_id
// 				 AND br.branch_id=p.brand_id
// 		AND t.transfer_date  BETWEEN '$start_date' AND '$end_date'";
		$sql = " SELECT 
					  p.item_name,
					  p.item_code,
					  SUM(ti.qty) AS qty,
					  t.`invoice_num`,
					  br.Name AS Brand,
					  cg.Name AS cate_name,
					  t.`transfer_date`,
					  (SELECT 
					    sl.`name` 
					  FROM
					    `tb_sublocation` AS sl 
					  WHERE sl.`id` = t.`from_location`) AS From_location,
					  (SELECT 
					    sl.`name` 
					  FROM
					    `tb_sublocation` AS sl 
					  WHERE sl.`id` = t.`to_location`) AS to_location ,
					  (SELECT 
					    u.title 
					  FROM
					    `tb_acl_user` AS u 
					  WHERE u.user_id = t.`user_id`) AS title,
					  (SELECT 
					    u.fullname 
					  FROM
					    `tb_acl_user` AS u 
					  WHERE u.user_id = t.`user_id`) AS user_name 
					FROM
					  tb_product AS p,
					  tb_category AS cg,
					  tb_brand AS br,
					  tb_transfer_item AS ti,
					  tb_stocktransfer AS t,
					  tb_sublocation AS sl 
					WHERE ti.transfer_id = t.transfer_id 
					  AND p.id = ti.pro_id 
					  AND cg.id = p.cate_id 
					  AND br.id = p.brand_id ";
					  
// 					  AND t.transfer_date BETWEEN '$start_date' 
// 					  AND '$end_date' ";
		
// 		if(($data["item"]!="" AND $data["item"]!=0 )){
// 			$sql.=" AND p.pro_id = ".trim($data["item"]);
// 		}
// 		if($data["category_id"]!="" AND $data["category_id"]!=0){
// 			$sql.=" AND cg.CategoryId = ".trim($data["category_id"]);
// 		}
// 		if($data["branch_id"]!="" AND $data["branch_id"]!=0){
// 			$sql.=" AND br.branch_id = ".trim($data["branch_id"]);
// 		}
// 		if($data["LocationId"]!="" AND $data["LocationId"]!=0){
// 			$sql.=" AND t.From_location = ".trim($data["LocationId"]);
// 		}
		
// 		if($data["to_LocationId"]!="" AND $data["to_LocationId"]!=0){
// 			$sql.=" AND t.to_location = ".trim($data["to_LocationId"]);
// 		}
// 		$result =  $this->GetuserInfo();
// 		if ($result["level"]!=1 AND $result["level"]!=2 ){
// 			$sql.=" AND t.to_location = ".trim($result["location_id"]);
// 		}
			
// 		$sql.=" GROUP BY transfer_no,p.pro_id ";
		return $db->fetchAll($sql);
	}
	
	public function geProducttQty($data){
		$db=$this->getAdapter();
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
	
		// 		$sql = "SELECT p.item_name, p.item_code, SUM( ti.qty) AS qty,br.Name AS Brand,cg.Name AS cate_name
		// 		FROM tb_product AS p,tb_category As cg,tb_branch AS br, tb_transfer_item AS ti, tb_stocktransfer AS t
		// 			WHERE
		// 	             ti.transfer_id= t.transfer_id
		// 	             AND p.pro_id = ti.pro_id
		// 				 AND cg.CategoryId=p.cate_id
		// 				 AND br.branch_id=p.brand_id
		// 		AND t.transfer_date  BETWEEN '$start_date' AND '$end_date'";
		$sql = " SELECT 
				  pl.id,
				  p.`item_name`,
				  p.`item_code`,
				  pl.qty,
				
				  (SELECT sl.name FROM `tb_sublocation` AS sl WHERE sl.id = pl.`location_id`) AS location,
				  b.`name` AS branch,
				  c.`name` AS category
				FROM
				  tb_prolocation  AS pl,
				  `tb_brand` AS b,
				  `tb_category` AS c,
				   tb_product AS p
				  WHERE b.`id`=p.`brand_id`
				  AND c.`id`=p.`cate_id`
				  AND pl.`pro_id`=p.`id` ";	
					
// 		if(($data["item"]!="" AND $data["item"]!=0 )){
// 		$sql.="AND p.pro_id = ".trim($data["item"]);
// 	    }
// 		if($data["category_id"]!="" AND $data["category_id"]!=0){
// 		$sql.=" AND c.`CategoryId` = ".trim($data["category_id"]);
// 		}
// 				if($data["branch_id"]!="" AND $data["branch_id"]!=0){
// 				$sql.=" AND b.`branch_id` = ".trim($data["branch_id"]);
// 		}
// 		if($data["LocationId"]!="" AND $data["LocationId"]!=0){
// 		$sql.=" AND LocationId = ".trim($data["LocationId"]);
// 		}
// 				$result =  $this->GetuserInfo();
// 				if ($result["level"]!=1 AND $result["level"]!=2 ){
// 		$sql.=" AND LocationId = ".trim($result["location_id"]);
// 		}
			
// 		$sql.=" ORDER BY pl.pro_id ";
		return $db->fetchAll($sql);
	}
	public function getTopTenProductSO(){
		$db = $this->getAdapter();
		$sql = " SELECT  p.item_name, SUM( si.qty_order ) AS qty
					FROM tb_product AS p,tb_sales_order_item AS si, tb_sales_order AS s
					WHERE p.pro_id = si.pro_id
					AND si.order_id = s.order_id AND s.status=4 AND s.date_order  >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)
		 GROUP BY si.pro_id ORDER BY qty DESC LIMIT 10 ";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	
	
	public function getTopTenProductSOByDate($data){
		$db = $this->getAdapter();
		$start_date = trim($data["start_date"]);
		$end_date = trim($data["end_date"]);
		$sql = " SELECT  p.item_name, SUM( si.qty_order ) AS qty
		FROM tb_product AS p,tb_sales_order_item AS si, tb_sales_order AS s
		WHERE p.pro_id = si.pro_id
		AND si.order_id = s.order_id AND s.status=4 AND s.date_order  BETWEEN '$start_date' AND '$end_date'
		GROUP BY si.pro_id ORDER BY qty DESC LIMIT 10 ";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	
	public function getTopTenProductPO(){
		$db = $this->getAdapter();
		$sql = " SELECT p.item_name, SUM( pi.qty_order ) AS qty
					FROM tb_product AS p,tb_purchase_order_item AS pi, tb_purchase_order AS pur
				WHERE p.pro_id = pi.pro_id
				AND pi.order_id = pur.order_id
				AND pur.status = 4
				AND pur.date_in >= DATE_ADD(LAST_DAY(DATE_SUB(NOW(), INTERVAL 2 MONTH)), INTERVAL 1 DAY)
		                GROUP BY pi.pro_id ORDER BY qty DESC LIMIT 10 ";
		$rows = $db->fetchAll($sql);
		return $rows;
	}
	public function getAllQuotation($search){//4
		$db= $this->getAdapter();
		$sql=" SELECT id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT NAME FROM `tb_sale_agent` WHERE id=s.saleagent_id LIMIT 1) AS agent_name,
		s.quoat_number,s.date_order,all_total,discount_value,net_total,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT name_en FROM `tb_view` WHERE type=7 AND key_code=is_approved LIMIT 1) as is_approved ,
		(SELECT name_en FROM `tb_view` WHERE type=8 AND key_code=pending_status LIMIT 1) pending_status,
		s.currency_id,
		s.net_total,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.approved_userid LIMIT 1 ) AS approval_by
		FROM `tb_quoatation` AS s ";
		$from_date =(empty($search['start_date']))? '1': " s.date_order >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_order <= '".$search['end_date']." 23:59:59'";
		$where = " WHERE ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " s.quoat_number LIKE '%{$s_search}%'";
			$s_where[] = " s.net_total LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>0){
			$where .= " AND s.customer_id = ".$search['customer_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getQuotationById($id){//5
		$db = $this->getAdapter();
		$sql=" 
		SELECT
		(SELECT NAME FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		s.quoat_number,s.date_order,s.remark,s.discount_value,
		(SELECT NAME FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		(SELECT u.fullname FROM tb_acl_user AS u WHERE u.user_id = s.approved_userid LIMIT 1 ) AS approval_by,
		so.qty_order,so.price,so.old_price,so.sub_total,s.net_total
		FROM `tb_quoatation` AS s,
		`tb_quoatation_item` AS so WHERE s.id=so.quoat_id
		AND s.status=1 AND s.id = $id ";
		return $db->fetchAll($sql);
	}
	public function getAllDeliveryReport($search){//4
		$db= $this->getAdapter();
		$sql=" SELECT s.id,
		(SELECT name FROM `tb_sublocation` WHERE tb_sublocation.id = s.branch_id AND status=1 AND name!='' LIMIT 1) AS branch_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT name FROM `tb_sale_agent` WHERE id=s.saleagent_id LIMIT 1) AS agent_name,
		s.sale_no,s.date_sold,s.tax,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id limit 1) As curr_name,
		s.currency_id,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		iv.sub_total,iv.discount,iv.paid_amount,iv.balance,iv.invoice_no,iv.invoice_date
		FROM `tb_sales_order` AS s ,tb_invoice as iv WHERE iv.sale_id = s.id ";
		$from_date =(empty($search['start_date']))? '1': " s.date_sold >= '".$search['start_date']." 00:00:00'";
		$to_date = (empty($search['end_date']))? '1': " s.date_sold <= '".$search['end_date']." 23:59:59'";
		$where = " AND ".$from_date." AND ".$to_date;
		if(!empty($search['text_search'])){
			$s_where = array();
			$s_search = trim(addslashes($search['text_search']));
			$s_where[] = " s.order_number LIKE '%{$s_search}%'";
			$s_where[] = " s.net_total LIKE '%{$s_search}%'";
			$s_where[] = " s.paid LIKE '%{$s_search}%'";
			$s_where[] = " s.balance LIKE '%{$s_search}%'";
			$where .=' AND ('.implode(' OR ',$s_where).')';
		}
		if($search['customer_id']>0){
			$where .= " AND s.customer_id = ".$search['customer_id'];
		}
		if($search['branch_id']>0){
			$where .= " AND branch_id =".$search['branch_id'];
		}
		$dbg = new Application_Model_DbTable_DbGlobal();
		$where.=$dbg->getAccessPermission();
		$order=" ORDER BY id DESC ";
		return $db->fetchAll($sql.$where.$order);
	}
	function getProductDelivyerId($id){//5
		$db = $this->getAdapter();
		$sql=" SELECT
		(SELECT NAME FROM `tb_sublocation` WHERE id=s.branch_id) AS branch_name,
		s.sale_no,s.date_sold,s.remark,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT qty_perunit FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS qty_perunit,
		(SELECT unit_label FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS unit_label,
		(SELECT serial_number FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS serial_number,
		(SELECT name_en FROM `tb_view` WHERE TYPE=2 AND key_code=(SELECT model_id FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) LIMIT 1) As model_name,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT u.username FROM tb_acl_user AS u WHERE u.user_id = s.user_mod LIMIT 1 ) AS user_name,
		so.qty_order,so.qty_unit,so.qty_detail,so.price,so.old_price,so.sub_total,s.net_total,
		s.paid,s.discount_real,s.tax,
		s.balance,v.invoice_no,(SELECT tb_deliverynote.deli_date FROM `tb_deliverynote` WHERE tb_deliverynote.invoice_id=v.id) AS deli_date
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so ,tb_invoice as v WHERE v.sale_id =s.id AND s.id=so.saleorder_id
		AND s.status=1 AND s.id = $id ";
		return $db->fetchAll($sql);
	}
	function getInvoiceById($id){//5
		$db = $this->getAdapter();
		$sql="SELECT
		(SELECT name FROM `tb_sublocation` WHERE id=s.branch_id LIMIT 1) AS branch_name,
		(SELECT item_name FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS item_name,
		(SELECT item_code FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS item_code,
		(SELECT qty_perunit FROM `tb_product` WHERE id= so.pro_id LIMIT 1) AS qty_perunit,
		(SELECT unit_label FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) AS unit_label,
		(SELECT name_en FROM `tb_view` WHERE TYPE=2 AND key_code=(SELECT model_id FROM `tb_product` WHERE id=so.pro_id LIMIT 1 ) LIMIT 1) AS model_name,
		(SELECT symbal FROM `tb_currency` WHERE id=s.currency_id LIMIT 1) AS curr_name,
		(SELECT cust_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS customer_name,
		(SELECT phone FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS phone,
		s.customer_id,
		(SELECT contact_name FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS contact_name,
		(SELECT email FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS email,
		(SELECT address FROM `tb_customer` WHERE tb_customer.id=s.customer_id LIMIT 1 ) AS add_name,
		(SELECT name FROM `tb_sale_agent` WHERE tb_sale_agent.id =s.saleagent_id  LIMIT 1 ) AS staff_name,
		so.qty_order,so.sub_total AS dsub_total,so.qty_unit,so.qty_detail,so.price,so.old_price,
		(SELECT tb_acl_user.username FROM tb_acl_user  WHERE tb_acl_user.user_id = v.user_id LIMIT 1 ) AS biller,
		v.invoice_no,v.invoice_date,v.user_id,v.balance_after,v.sub_total,v.discount AS vdiscount,v.paid_amount,v.balance
		FROM `tb_sales_order` AS s,
		`tb_salesorder_item` AS so ,tb_invoice AS v WHERE v.sale_id =s.id AND s.id=so.saleorder_id
		AND s.status=1 AND s.id =$id ";
		return $db->fetchAll($sql);
	}
	function getCustomerPayment($customer_id,$sale_id){
		$db = $this->getAdapter();
		$sql=" SELECT v.id,v.sale_id,v.invoice_no,v.invoice_date,v.sub_total,v.discount,v.paid_amount,
		v.balance,v.balance_after,v.is_approved FROM 
		`tb_invoice` AS v,tb_sales_order As s WHERE v.sale_id = s.id AND customer_id = $customer_id 
		AND s.id!= $sale_id AND v.is_fullpaid !=1 ";
		return $db->fetchAll($sql);
	}
	
}

?>
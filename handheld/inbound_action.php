<?php
require_once('../config.php');
require_once('../class_my.php');
require_once('../func.php');
$db = DB();
// ยังไม่ได้เช็ค ว่า id ที่อยู่ถูกต้อหรือไม่
//$userID = $_SESSION['userID'];
$userID = $_SESSION['userID'];
if(isset($_POST['actionType']) && $userID != ''){
    $action = $_POST['actionType'];
    $actionBack = $_SERVER['HTTP_REFERER'];
    if($action == 'addLocation'){
        $poID = $_POST['poID'];
        $inboundID = $_POST['inboundID'];
        $actionBack = $_POST['actionBack'];
        $productNo = $_POST['productNo'];
        $productName = $_POST['productName'];
        $productUnit = $_POST['productUnit'];
        foreach($_POST['locationID'] as $key => $val){
            $sqlLocationID = $db->get_where('tb_address',array('add_name'=>$val));
            $rsLocationID = $sqlLocationID->row_array();
            $locationID = $rsLocationID['add_id'];
            $qty = $_POST['numIn'][$key];
            $data = array(
                'inbound_id' => $inboundID,
                'location_id' => $locationID,
                'qty' => $qty,
                'time' => _DATE_TIME_,
                'update_time' => _DATE_TIME_,
                'user_id' => $userID
            );
            $db->insert('inbound_location',$data);
            insert_tb_stock_product($productNo,$productName,$productUnit,$qty,$userID);
            $db->update('inbound_po',array('product_date_in'=>_DATE_TIME_,'po_status'=>1),array('inbound_id'=>$inboundID));
            checkStatusInbound($poID);
        }
    }else if($action == 'addLocationMany'){//------------------------------------------------------
        $inboundID = $_POST['inboundID'];
        $poID = $_POST['poID'];
        $sqlLocationID = $db->get_where('tb_address',array('add_name'=>$_POST['locationID']));
        $rsLocationID = $sqlLocationID->row_array();
        $locationID = $rsLocationID['add_id']; // get location ID

        foreach($inboundID as $key => $val){
            $sqlInbound = $db->get_where('inbound_po',array('inbound_id' => $val));
            $rsInbound = $sqlInbound->row_array();
            $data = array(
                'inbound_id' => $val,
                'location_id' => $locationID,
                'qty' => $rsInbound['product_qty'],
                'qty_remain' => $rsInbound['product_qty'],
                'time' => _DATE_TIME_,
                'update_time' => _DATE_TIME_,
                'user_id' => $userID
            );
            if($db->insert('inbound_location',$data)){
                insert_tb_stock_product($rsInbound['product_no'],$rsInbound['product_name'],$rsInbound['product_unit'],$rsInbound['product_qty'],$userID);
                $db->update('inbound_po',array('product_date_in'=>_DATE_TIME_,'po_status'=>1),array('inbound_id'=>$val));
                checkStatusInbound($poID);
            }
        }
    }else if($action == 'choiceCar'){
        $carID = $_POST['carID'];
        $rtID = $_POST['rtID'];
        // 17-09-2558
        $time = strtotime("now");
        $date = date('Ymd');
        
        foreach($rtID as $key => $val){
            $data = array(
                'car_id' => $carID,
                'outbound_rt' => $val,
                'user_id' => $userID,
                'date_time' => _DATE_TIME_,
            		'organize_truck_id' => $date.$time
            );
            $db->insert('outbound_car',$data);
            $dataStatus = array(
                'status' => 4,
                'update_time' => _DATE_TIME_,
                'user_id' => $userID
            );
            $db->update('outbound_rt_status',$dataStatus,array('rt_id'=>$val));
        }
    }else if($action == 'confirm_item_branch'){
		$actionBack = '?page=confirm_to_branch';
		$rtID = $_POST['rtID'];
		$note = $_POST['note'];
		if($_POST['status']=='7'){$status = 7;
			$db->update('outbound_rt_status',array('status'=>$status,'note_confirm_branch'=>$note,'time_branch_confirm'=>_DATE_TIME_),array('rt_id'=>$rtID));
			//echo $db->last_query();
		}
	}
    header('location:'.$actionBack);
}

//------------------------------  function - --------------------------------------
function insert_tb_stock_product($productNo,$productName,$productUnit,$qty,$userID){
    $db = DB();
    $sqlNumStock = $db->get_where('stock_product',array('product_id'=>$productNo));
    //echo $db->last_query();
    $numStock = $sqlNumStock->num_rows();
    if($numStock > 0){// หากมีรายการแล้ว
        $rsStock = $sqlNumStock->row_array();
        $qtySum = $qty+$rsStock['product_qty'];
        $dataLog = array(
            'product_id' => $rsStock['product_id'],
            'product_qty' => $rsStock['product_qty'],
            'stock_update' => $rsStock['product_update'],
            'user_id' => $rsStock['user_id']
        );
        $db->insert('stock_product_log',$dataLog);
        $dataStock = array(
            'product_qty' => $qtySum,
            'product_update' => _DATE_TIME_,
            'user_id' => $userID
        );
        $whereStock = array(
            'product_id'=>$productNo
        );
        $db->update('stock_product',$dataStock,$whereStock);
    }else if($numStock == 0){ //หากยังไม่มีรายการ
        $dataStock = array(
            'product_id' => $productNo,
            'product_name' => $productName,
            'product_unit' => $productUnit,
            'product_qty' => $qty,
            'product_update' => _DATE_TIME_,
            'user_id' => $userID
        );
        $db->insert('stock_product',$dataStock);
        //echo $db->last_query();
    }
}
// function เช็คของ ใน PO นั้นๆ ทั้งหมดได้ เก็ย เรียบร้อยแล้วหรือยัง ถ้าครบหมดแล้ว เปลี่ยน สถาน ที่ ตาราง inbound status เก้บของเสร็จละ
// check po getstock  == true change status in inbound_status = success
function checkStatusInbound($poID){
    $db = DB();
    $sql = $db->get_where('inbound_po',array('po_id'=>$poID,'po_status'=>0));
    $num = $sql->num_rows();
    if($num == 0){
        $db->update('inbound_status',array('status'=>1,'time_in_success'=>_DATE_TIME_),array('inbound_id'=>$poID));
    }
}
?>
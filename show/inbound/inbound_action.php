<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
include('../../lib/barcode/tcpdf_barcodes_1d.php');
$db = DB();
$user_id = $_SESSION['userID'];
if(!isset($_POST['method'])){
	exit();
}

if($_POST['method']=='update_fefo'){
    $data = array(
        'product_fefo'=>1,
        'product_fefo_date'=>$_POST['date_fefo'],
        'product_create_date' => $_POST['product_create']
    );
	$db->update('inbound_po',$data,array('inbound_id'=>$_POST['id']));
}else if($_POST['method'] == 'start_inbound'){
    $db->insert('inbound_status',array('inbound_id'=>$_POST['poID'],'start_date'=>_DATE_TIME_));
    $ex = explode('&', $_SERVER['HTTP_REFERER']);
    $ex[0] = '';
    $url = implode('&',$ex);
    header('location:'._BASE_URL_.'?page=inbound_items&'.$url);
}else if($_POST['method'] == 'addday'){
    if($_POST['dateIn']!=''){
        $date['date'] =  addDay($_POST['dateIn'],$_POST['numExp']);
        $date['remain'] = date_sum_remain($date['date']);
    }else{
        $date['remain'] = date_sum_remain($_POST['dateExp']);
    }
    echo json_encode($date);
}else if($_POST['method'] == 'updateNumQTY'){
    $numQTY = $_POST['numQTY'];
    foreach($numQTY as $key => $value){
        $inboundKey = $key;
        $sqlGet = $db->get_where('inbound_po',array('inbound_id'=>$inboundKey));
        $rsGet = $sqlGet->row_array();
        if($rsGet['product_qty'] != $value && $value != 0 && $value != ''){
            $poID = $rsGet['po_id'];
            $inboundID = $rsGet['inbound_id'];
            $oldQty = $rsGet['product_qty'];
            $oldUser = $rsGet['user_update'];
            $oldDate = $rsGet['dateupdate'];
            if($oldQty != 0){
                $old = array(
                    'inbound_key' => $inboundKey,
                    'qty' => $oldQty,
                    'user_id' => $oldUser,
                    'update_time' => $oldDate
                );
                if(isset($_POST['confirmChk'])){
                    $old['po_status'] = 1;
                }
                $db->insert('inbound_change_qty_log',$old);
            }
            $new = array(
                'product_qty' => $value,
                'user_update' => $user_id
            );
            if(isset($_POST['confirmChk'])){
                $new['po_status'] = 1;
            }
            $whereNew = array(
                'inbound_id' => $inboundKey
            );
            $db->update('inbound_po',$new,$whereNew);
            if($db->affected_rows() == '1'){
                if(isset($_POST['confirmChk'])){
                    $inStatus = array(
                        'status' => 1,
                        'time_get_product' => _DATE_TIME_
                    );
                    $whereStatus = array(
                        'inbound_id' => $poID
                    );
                    $db->update('inbound_status',$inStatus,$whereStatus);
                    if($db->affected_rows() == '1'){
                        $data['changeStatus'] = 'true';
                    }
                }
            }
        }
    }
    $data['save'] = 'true';
    echo json_encode($data);
}else if($_POST['method'] == 'create_pallet'){
    $insertID = '';
    $success = 0;
    foreach($_POST['inboundKey'] as $key ){
        $qty = $_POST['qty'][$key];
        $data = array(
            'create_time' => _DATE_TIME_,
            'user_create' => $user_id
        );
        if($insertID == ''){
            $db->insert('inbound_pallet',$data);
            $insertID = $db->insert_id();
        }
        $sqlGetInbound = $db->get_where('inbound_po','inbound_id = '.$key);
        $rsGetInbound = $sqlGetInbound->row_array();
        $data2 = array(
            'pallet_id' => $insertID,
            'po_id' => $rsGetInbound['po_id'],
            'inbound_key' => $rsGetInbound['inbound_id'],
            'supplier_id' => $rsGetInbound['po_supplier'],
            'qty' => $qty,
            'create_time' => _DATE_TIME_,
            'user_id' => $user_id
        );
        $db->insert('inbound_pallet_item',$data2);
        if($db->affected_rows()>0){
            $success = 1;
        }
        //echo $db->last_query();
    }
    $barcode = str_pad($insertID,10,"0",STR_PAD_LEFT);
    $barcodeobj = new TCPDFBarcode($barcode, 'C128');
    $showBarcode = $barcodeobj->getBarcodePNG(2, 30, array(0,0,0),$insertID);
    $back['barcode'] ='
        <span class="detailBarcode" style="text-align: center; line-height:40px;font-size: 16px;float:right;">
            <span class="barcodeImg" style="margin-left:10px; margin-top:10px;">
                <img class="imgBarcode" src="data:image/png;base64,'.$showBarcode.'" style="" />
            </span>
        </span>
    ';
    $back['save'] = $success;
    $back['actionID'] = $insertID;
    echo json_encode($back);
}else if($_POST['method'] == 'update_pallet'){
    $updateID = $_POST['palletID'];
    foreach($_POST['inboundKey'] as $key ){
        $sqlRow = $db->get_where('inbound_pallet_item',array('pallet_id'=>$updateID,'inbound_key'=>$key));
        $rsRow = $sqlRow->row_array();
        $numRow = $sqlRow->num_rows();
        //echo $db->last_query();
        if($numRow == 0){
        $qty = $_POST['qty'][$key];
        $sqlGetInbound = $db->get_where('inbound_po','inbound_id = '.$key);
        $rsGetInbound = $sqlGetInbound->row_array();
        $data = array(
            'pallet_id' => $updateID,
            'po_id' => $rsGetInbound['po_id'],
            'inbound_key' => $rsGetInbound['inbound_id'],
            'supplier_id' => $rsGetInbound['po_supplier'],
            'qty' => $qty,
            'create_time' => _DATE_TIME_,
            'user_id' => $user_id
        );
        $db->insert('inbound_pallet_item',$data);
        }else if($numRow == 1){
            $qty = $_POST['qty'][$key] + $rsRow['qty'];
            $data = array(
                'qty' => $qty
            );
            $db->update('inbound_pallet_item',$data,'id ='.$rsRow['id']);
        }
        $barcode = str_pad($updateID,10,"0",STR_PAD_LEFT);
        $barcodeobj = new TCPDFBarcode($barcode, 'C128');
        $showBarcode = $barcodeobj->getBarcodePNG(2, 30, array(0,0,0),$updateID);
        $back['barcode'] ='
            <span class="detailBarcode" style="text-align: center; line-height:40px;font-size: 16px;float:right;">
                <span class="barcodeImg" style="margin-left:10px; margin-top:10px;">
                    <img class="imgBarcode" src="data:image/png;base64,'.$showBarcode.'" style="" />
                </span>
            </span>
        ';

        //echo $db->last_query();
    }
    $back['actionID'] = $updateID;
    echo json_encode($back);
}else if($_POST['method'] == 'manage_pallet_success'){
    $palletID = $_POST['palletID'];
    $db->edit('inbound_pallet',array('pallet_status'=>1,'confirm_time'=>_DATE_TIME_),array('pallet_id' => $palletID));
    $sql = $db->get_where('inbound_pallet',array('pallet_id' => $palletID));
    $rs = $sql->row_array();
    echo json_encode($rs);
}else if($_POST['method'] == 'get_product_in_pallet'){
    $palletID = $_POST['palletID'];
    $sql1 = $db->get_where('inbound_pallet',array('pallet_id' => $palletID));
    $rs1 = $sql1->row_array();
    $array['palletStatus'] = $rs1['pallet_status'];

    $db->select('ipt.inbound_key,SUM(ipt.qty) as total ,ipt.supplier_id ,ipt.po_id ');
    $db->select('inp.product_no , inp.product_name , inp.product_unit ');
    $db->join('inbound_po inp','inp.inbound_id = ipt.inbound_key');
    $db->group_by('ipt.inbound_key');
    $sql = $db->get_where('inbound_pallet_item ipt',array('ipt.pallet_id'=>$palletID));
    $rs = $sql->result_array();
    $array['detail'] = $rs;
    echo json_encode($array);
}else if($_POST['method'] == 'get_product_where_pallet'){
    $rs = array();
    $inboundKey = $_POST['inboundKey'];

    $sqlIn = $db->get_where('inbound_po',array('inbound_id' => $inboundKey));
    $rsIn = $sqlIn->row_array();
    $rs['po_id'] = $rsIn['po_id'];
    $rs['product_no'] = $rsIn['product_no'];
    $rs['po_supplier'] = $rsIn['po_supplier'];
    $rs['product_unit'] = $rsIn['product_unit'];

    $db->select('pallet_id,SUM(qty) as total,create_time');
    $db->group_by('pallet_id');
    $sqlPallet = $sql = $db->get_where('inbound_pallet_item',array('inbound_key'=>$inboundKey));
    $rs['pallet'] = $sqlPallet->result_array();
    echo json_encode($rs);
}
?>
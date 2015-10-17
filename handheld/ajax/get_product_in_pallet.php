<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$data = array();
$detail = '';
$db = DB();
$val = $_POST['val'];
if($_POST['method']== 'getProduct'){
    $checkPallet = $db->get_where('inbound_pallet',array('pallet_id' => $val));
    $rsCheck = $checkPallet->row_array();
    if($val != '' && $rsCheck['pallet_status'] == 1){
        $db->select('SUM(ipi.qty) as qty');
        $db->select('ip.inbound_id,ip.product_no,ip.product_name');
        $db->group_by(array('ipi.inbound_key','ipi.pallet_id'));
        $db->join('inbound_po ip','ip.inbound_id = ipi.inbound_key');
        $db->where('ip.po_status','1');
        $db->where('ipi.qty >','0');
        $sqlList = $db->get_where('inbound_pallet_item ipi','ipi.pallet_id ='.$val);
        $data['last_query'] = $db->last_query();
        if ($sqlList->num_rows() > 0) {
            foreach($sqlList->result_array() as $rs){
                $detail .= '<tr><td colspan=2>'.$rs['product_name'].'</td></tr>';
                $detail .= '<tr>
                                <td>'.$rs['product_no'].'</td>
                                <td><input type=hidden name=qty['.$rs['inbound_id'].'] value='.$rs['qty'].'>'.$rs['qty'].'</td>
                            </tr>';
            }
            $status = 1;
        }else {
            $detail = '<tr><td colspan=3>ไม่พบข้อมูล</td>';
            $status = 2;

        }
    }
    else {
        if($rsCheck['pallet_status'] == 0){
            $detail = '<tr><td colspan=3>โปรดยืนยันการจัดพาเลท</td>';
            $status = 3;
        }else if($rsCheck['pallet_status'] == 2){
            $detail = '<tr><td colspan=3>ได้ดำเนินการนี้แล้ว</td>';
            $status = 4;
        }

    }
    $data['detail'] = $detail;
    $data['status'] = $status;
}else if($_POST['method'] == 'getLocationID'){
    $locationID = getLocationID($val);

    $data['locationID'] = $locationID;
}
echo json_encode($data);
//print_r($rs);

function getLocationID($name){
    $db = DB();
    $db->select('add_id');
    $sqlID = $db->get_where('tb_address',"add_name = '".$name."'");
    if($sqlID->num_rows()>0){
        $rsID = $sqlID->row_array();
        return $rsID['add_id'];
    }  else {
        return 'false';
    }
}
?>


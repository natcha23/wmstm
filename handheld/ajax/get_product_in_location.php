<?php
require_once('../../config.php');
require_once('../../class_my.php');
require_once('../../func.php');
$data = array();
$detail = '';
$db = DB();
$locationID = getLocationID($_POST['location']);
if($_POST['method']== 'getProduct'){
    if($locationID != 'false'){

        $db->select('SUM(il.qty_remain) as qty');
        $db->select('ip.inbound_id,ip.product_no,ip.product_name');
        $db->group_by(array('il.inbound_id','il.location_id'));
        $db->join('inbound_po ip','ip.inbound_id = il.inbound_id');
        $db->where('ip.po_status','2');
        $db->where('il.qty_remain >','0');
        $sqlList = $db->get_where('inbound_location il','il.location_id ='.$locationID);
        //echo $db->last_query();
        if ($sqlList->num_rows() > 0) {
            foreach($sqlList->result_array() as $rs){
                $detail .= '<tr><td colspan=3>'.$rs['product_name'].'</td></tr>';
                $detail .= '<tr>
                                <td><input type="checkbox" name="inKey[]" value="'.$rs['inbound_id'].'"/></td>
                                <td>'.$rs['product_no'].'</td>
                                <td><input type=hidden name=qty['.$rs['inbound_id'].'] value='.$rs['qty'].'>'.$rs['qty'].'</td>
                            </tr>';
            }
            $data['locationID'] = $locationID;
            $status = 1;
        }else {
            $detail = '<tr><td colspan=3>ไม่พบข้อมูล</td>';
            $status = 2;
        }
    }
    else {
        $detail = '<tr><td colspan=3>ไม่พบตำแหน่ง</td>';
        $status = 3;
    }
    $data['detail'] = $detail;
    $data['status'] = $status;
}else if($_POST['method'] == 'getLocationNewID'){
    $data['locationID'] = $locationID;
}
echo json_encode($data);
//print_r($rs);

function getLocationID($name){
    $db = DB();
    $db->select('add_id');
    $sqlID = $db->get_where('tb_address',"add_name = '".$_POST['location']."'");
    if($sqlID->num_rows()>0){
        $rsID = $sqlID->row_array();
        return $rsID['add_id'];
    }  else {
        return 'false';
    }
}
?>


<?php
$sqlBranchID = $db->get_where('user',array('user_id'=>$_SESSION['userID']));
$rsBrachID = $sqlBranchID->row_array();
$sql = $db->get_where('outbound_rt_status',array('status'=>6,'rt_branch'=>$rsBrachID['branch_id']));
?>
<table id="table-outbound-list" class="table" border="1">
    <thead>
        <tr>
            <th>RT NO</th>
            <th>จำนวนรายการ</th>
            <th>ทะเบียนรถ</th>
            <th>พลขับ</th>
            <th>--</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach($sql->result_array() as $rs){
            $db->select('car.*');
            $db->join('car_list car','car.car_id = oc.car_id');
            $sqlCar = $db->get_where('outbound_car oc',array('oc.outbound_rt'=>$rs['rt_id']));
            $rsCar = $sqlCar->row_array();
        ?>
        <tr>
            <td><?php echo $rs['rt_id']?></td>
            <td><?php echo $rs['rt_product_amount']?></td>
            <td><?php echo $rsCar['car_code']?></td>
            <td><?php echo $rsCar['car_man']?></td>
            <td><a href="?page=confirm_detail&rtID=<?php echo $rs['rt_id']?>">เลือก</a></td>
        </tr>
        <?php } ?>
    </tbody>
</table>
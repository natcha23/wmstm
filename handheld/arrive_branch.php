<?php
if(isset($_SESSION['user_car_id'])){
    $userID = $_SESSION['userID'];
    //$db->select('ors.rt_branch');
    $db->join('outbound_rt_status ors','ors.rt_id = oc.outbound_rt');
    $sql = $db->get_where('outbound_car oc',array('oc.driver_id'=>$userID,'oc.status_process'=>'1','ors.status'=>5,));
    //echo $db->last_query();
?>
<table border='1' >
    <tr bgcolor="#0099FF">
        <th>#</th>
        <th>ปลายทาง</th>
        <th> -- </th>
    </tr>
<?php
$n = 1;
foreach($sql->result_array() as $arr){
    //$db->select('*')->from('inbound_location')->where(array('inbound_id'=>$arr['inbound_id']));
    //$sqlLocation = $db->get();
?>
    <tr <?php if($n%2==0){echo 'style=background-color:#D8D8D8';}?> >
        <td><?php echo $n;?></td>
        <td><?php echo $arr['rt_branch'];?></td>
        <td><a href="javascript:goForm('<?php echo $arr['car_id']; ?>','<?php echo $arr['rt_branch'];?>')">ถึงปลายทาง</a></td>
    </tr>
<?php
$n++;
}?>
    </table>
<form action='outbound_action.php' method='post' id="formCar">
    <input type="hidden" name="actionType" value="stayBranch" />
    <input type="hidden" name="branchID" id="branchID" />
    <input type="hidden" name="carID" id="carID" />
</form>
<?php } ?>
<script>
function goForm(carID,branchID){
    if(confirm('ยืนยันถึงที่หมาย')){
        $('#branchID').val(branchID);
        $('#carID').val(carID);
        $('#formCar').submit();
    }
}
</script>


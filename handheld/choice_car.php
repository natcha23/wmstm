<?php
$sqlCar = $db->get('car_list');
$sqlListChk = $db->get_where('outbound_rt_status',array('status'=>3));
?>
<form action="inbound_action.php" method="post" id="formAdd" onsubmit="return chkSubmit();"> <!-- action to inbound_action-->
    <input name="actionType" type="hidden" value="choiceCar"/>
    <select id="carID" name="carID">
        <option>-เลือก-</option>
        <?php
        foreach($sqlCar->result_array() as $rsCar){
            echo "<option value='$rsCar[car_id]'>($rsCar[car_code])$rsCar[car_man]</option>";
        }
        ?>
    </select>
    <button type="submit">เลือก</button>
    <input type="button" value="เมนู" onclick="javascript:window.location.href='?'"/>
</form>
<br/>
<table border="1">
    <tr>
        <th>#</th>
        <th>RT</th>
        <th>ปลายทาง</th>
    </tr>
    <?php
    foreach($sqlListChk->result_array() as $rsListChk){
    ?>
    <tr>
        <td><input type="checkbox" name="rtID[]" value="<?php echo $rsListChk['rt_id']?>"/></td>
        <td><?php echo $rsListChk['rt_id']?></td>
        <td><?php echo $rsListChk['rt_branch']?></td>
    </tr>
    <?php } ?>
</table>
<script>
    function chkSubmit(){
        $('.rtNumber').remove();
        var addInput = '';
        if(confirm('ยืนยันที่จะบันทึก') && $('[name="rtID[]"]:checked').length > 0 && $('#carID').val()){

            $('input[name="rtID[]"]:checked').each(function(i,e){
                addInput += "<input type='hidden' name='rtID[]' class='rtNumber' value='"+$(e).val()+"'/>";
            });
            $('#formAdd').append(addInput);

        }else {
            alert('เลือกข้อมูลให้ถูกต้อง');
            return false;
        }
    }
</script>
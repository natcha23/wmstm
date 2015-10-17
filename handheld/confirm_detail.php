<?php
$rtID = $_GET['rtID'];
$db->select();
$db->join('outbound_check oc','rt.id=oc.outbound_id');
$sql = $db->get_where('outbound_rt rt',array('rt.rt_refid'=>$rtID));

echo 'RT NO : '.$rtID;
?>
<a href="<?php echo $_SERVER['HTTP_REFERER'];?>">ย้อนกลับ</a>
<form id='formGo' action='inbound_action.php' method='post'>
<input type='hidden' name='actionType' value='confirm_item_branch'/>
<input type='hidden' name='rtID' value='<?php echo $rtID; ?>'/>
<input type='hidden' name='status' value='7'/>
<input type='hidden' name='note' id='note' value=''/>

</form>
<table id="table-outbound-list" class="table" border="1">
    <thead>
        <tr>
            <th>Barcode</th>
            <th>ชื่อ</th>
            <th>จำนวน</th>
            <th>หน่วย</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach($sql->result_array() as $rs){
        ?>
        <tr>
            <td><?php echo $rs['barcode']?></td>
            <td><?php echo $rs['goods_name']?></td>
            <td><?php echo $rs['check_qty']?></td>
            <td><?php echo $rs['unit']?></td>
        </tr>
        <?php } ?>
        <tr>
            <td colspan="4">
				<textarea id='noteForm'></textarea></br>
                <button onclick="if(confirm('ยืนยันการรับของ')==true){changStauts();}">รับของ</button>
            </td>
        </tr>
    </tbody>
</table>
<script>
	function changStauts(){
		$('#note').val($('#noteForm').val());
		$('#formGo').submit();
	}
</script>
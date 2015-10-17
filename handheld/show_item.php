<script>
    function choiceAll(){
        var chk = '';
        var inboundID = '';
        $('.inboundIDHideen').remove();
        if($('[name="product_id[]"]:checked').length > 0 && $('#locationID').val()){
            if(confirm('ยืนยันที่จะทำต่อใช่หรือไม่')){

                $('[name="product_id[]"]:checked').each(function(i,e){
                    inboundID += "<input type='hidden' class='inboundIDHideen' name='inboundID[]' value='"+$(e).val()+"' />" ;
                });
                $('#formChoiceMany').append(inboundID);
            }
        }else{
            alert('เลือกรายการ และ สถานที่');
            chk = false;
        }
        return chk;
    }
</script>
<a href="?page=show_po" border='1'>ย้อนกลับ</a><br/>
<?php echo 'PO : '.$_GET['po_id'];?><br/>
<form action="inbound_action.php" method="post" id="formChoiceMany" onsubmit="return choiceAll();" >
    <input name="actionType" type="hidden" value="addLocationMany"/>
    <input name="poID" type="hidden" value="<?php echo $_GET['po_id'];?>"/>
    <input id="locationID" name="locationID" size="10" value="" onkeypress="if(event.keyCode == 13){ return false;}"/>
    <button type="submit">เลือก</button>
</form>
<table border='1' >
    <tr bgcolor="#0099FF">
        <th>#</th>
        <th>รหัส</th>
        <th>ชื่อ</th>
        <th>จำนวน</th>
        <th>หน่วยนับ</th>
        <th>ตำแหน่ง</th>
    </tr>
<?php
$db->select('*')->from('inbound_po')->where(array('po_id'=>$_GET['po_id'],'po_status'=>0));
$sql = $db->get();
$row = $sql->num_rows();
$n = 0;
if($row > 0){
    foreach($sql->result_array() as $arr){
        //$db->select('*')->from('inbound_location')->where(array('inbound_id'=>$arr['inbound_id']));
        //$sqlLocation = $db->get();
    ?>
        <tr <?php if($n%2==0){echo 'style=background-color:#D8D8D8';}?> >
            <td><input type='checkbox' class="checkedAll" name='product_id[]' value="<?php echo $arr['inbound_id'];?>"></td>
            <td><?php echo $arr['product_no'];?></td>
            <td><?php echo $arr['product_name'];?></td>
            <td><?php echo $arr['product_qty'];?></td>
            <td><?php echo $arr['product_unit'];?></td>
            <td><a href="?page=choice_item&inbound_id=<?php echo $arr['inbound_id']?>">เลือก</a></td>
        </tr>
    <?php
    $n++;
    }
}else{?>
        <tr>
            <td colspan="6">ไม่มีรายการ</td>
        </tr>
    <?php
}
?>
</table>
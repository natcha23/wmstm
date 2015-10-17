<script>
	function addField(){
		var next = $('#next').val();
        var data = '<tr>';
		data += '<td><input id="locationID'+next+'" name="locationID[]" class="locationForm" rel="'+next+'" size="10" onkeyup="nextInput(this.id,event.keyCode);" onkeypress="if(event.keyCode == 13){ return false;}" /></td> ';
        data += '<td><input id="numIn'+next+'" name="numIn[]"  size="7" class="numInForm" onkeypress="if(event.keyCode == 13){ return false;}"/></td>';
        data += '</tr>';
		$('#tableLocation').append(data);
		next++;
		$('#next').val(next);
	}
	function nextInput(id,event){
		if(event == 13){
			var next = $('#'+id).attr('rel');
			$('#numIn'+next).focus();
		}
	}
    function chkSubmit(){
        var chk = 'true';
        var sumQTY = 0;
        var mainQTY = parseInt($('#mainQTY').val());
        var qtyStatus = '';
        $('.locationForm').each(function(i,e){
            var numRel = $(e).attr('rel');
            if($(e).val()){
                if(!$('#numIn'+numRel).val()){
                    chk = 'false';
                }
                else{
                    var sQTY = parseInt($('#numIn'+numRel).val());
                    if(sQTY){
                        sumQTY += sQTY;
                    }
                }
            }
        });
        if(mainQTY != sumQTY || sumQTY < 0){
            alert('จำนวน ไม่ถูกต้อง');
            chk = 'false';
        }
        if(chk == 'false'){
            return false;
        }else{
            if(!confirm('ยืนยันข้อมูลถูกต้อง')){
                return false;
            }
        }
    }
</script>
<a href="<?php echo $_SERVER['HTTP_REFERER'];?>" border='1'>ย้อนกลับ</a><br/>
<?php
if(isset($_GET['inbound_id'])&& $_GET['inbound_id'] != ''){
    $inboundID = $_GET['inbound_id'];
    $sql = $db->get_where('inbound_po',array('inbound_id'=>$inboundID));
    $rs = $sql->row_array();
?>
<form action="inbound_action.php" id="formSubmit" method="POST" onsubmit="return chkSubmit();" >
    <div>
    PO: <?php echo $rs['po_id'];?><br/>
    BARCODE : <?php echo $rs['product_no'];?><br/>
    NAME : <?php echo $rs['product_name'];?><br/>
    QTY : <?php echo $rs['product_qty'].' '.$rs['product_unit'];?><br/>
    <input type="hidden" name="mainQTY" id="mainQTY" value="<?php echo $rs['product_qty']?>"/>
    <input id='next' type="hidden" value="1"/>
	<div id="divLocation">
        <table border='1' id="tableLocation">
            <tr>
                <td>ที่เก็บ</td><td>จำนวน</td>
            </tr>
            <tr>
                <td>
                    <input id="locationID" name="locationID[]" class="locationForm" size="10" value="" rel="" onkeyup="nextInput(this.id,event.keyCode);"
                       onkeypress="if(event.keyCode == 13){ return false;}"/>
                </td>
                <td>
                    <input id="numIn" type='text' name="numIn[]" class="numInForm"  size="7" value="" onkeypress="if(event.keyCode == 13){ return false;}"/>
                </td>
            </tr>
        </table>
	</div>
	<br/>
    <input type="hidden" name="poID" value="<?php echo $rs['po_id'];?>"/>
    <input type="hidden" name="inboundID" value="<?php echo $inboundID?>"/>
    <input type="hidden" name="productNo" value="<?php echo $rs['product_no']?>"/>
    <input type="hidden" name="productName" value="<?php echo $rs['product_name']?>"/>
    <input type="hidden" name="productUnit" value="<?php echo $rs['product_unit']?>"/>
    <input type="hidden" name="actionType" value="addLocation"/>
    <input type="hidden" name="actionBack" value="<?php echo $_SERVER['HTTP_REFERER'];?>"/>
    <button type="submit">บันทึก</button>
    <button type="button" onclick="javascript:addField();" >เพิ่มที่เก็บ</button>
</div>
</form>
<?php } ?>
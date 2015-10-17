<?php
if($_GET['poID']){
    $poID = $_GET['poID'];
}
$sqlItem = $db->get_where('inbound_po',array('po_id'=>$poID));
$sqlStatus = $db->get_where('inbound_status',array('inbound_id'=>$poID));
$numStatus = $sqlStatus->num_rows();
if($numStatus != 0){
    $rsStatus = $sqlStatus->row_array();
}
?>
<form id="formStatus" action="show/inbound/inbound_action.php" method="post">
    <input type="hidden" name="poID" value="<?php echo $poID;?>"/>
    <input type="hidden" name="method" value="start_inbound"/>
</form>
<div class="ibox-title">
    <button onclick="history.back();">ย้อนกลับ</button>
    <?php if($numStatus == 0){?>
        <button type="button" class="btn btn-w-m btn-primary" style="float:right;" onclick="startInbound();">เริ่มดำเนินการ</button>
    <?php
    }else if(isset($rsStatus['status']) && $rsStatus['status'] == 0){?>
        <button class="btn btn-primary" >ดึงข้อมูล IBP </button>
    <?php
    }else if(isset($rsStatus['status']) && $rsStatus['status'] > 0){
        getStatus_PO($rsStatus['status']);
    }

    ?>
    <p></p>
<table id="table-inbound" class="table table-striped table-bordered table-hover">
<thead>
	<tr>
		<th>#</th>
		<th>กำหนดส่งสินค้า</th>
		<th>PO Reference No.</th>
		<th>Receipt Type</th>
		<th>Supplier No.</th>
		<!-- <th>Supplier name</th> -->
		<th>Barcode</th>
		<th>Name</th>
        <th>จำนวนที่สั่ง</th>
		<th>จำนวนรับจริง</th>
		<th>Unit</th>
		<th>Location</th>
		<th style="width:150px">วันหมดอายุ</th>
	</tr>
</thead>
<tbody>

<?php
$n=1;
foreach($sqlItem->result_array() as $rsItem){
    $product_fefo_date = '';
    $product_create_date = '';
    $product_fefo = $rsItem['product_fefo'];
    $row_id = $rsItem['inbound_id'];
    if($rsItem['product_fefo'] == 1){
        $product_fefo_date = $rsItem['product_fefo_date'];
        if($rsItem['product_create_date'] != "0000-00-00"){
            $product_create_date = $rsItem['product_create_date'];
        }
    }
    $numExp = '';
    $db->select('num_exp');
    $sqlExpSetting = $db->get_where('stock_product',array('product_id'=>$rsItem['product_no']));
    $rsExp = $sqlExpSetting->row_array();
    if($rsExp['num_exp']!= NULL){
        $numExp = $rsExp['num_exp'];
    }
?>
	<tr>
		<td><?php echo $n; ?></td>
		<td><?php echo $rsItem['po_delivery_date'];?></td>
		<td><?php echo $rsItem['po_id']; ?></td>
		<td><?php echo $DT_DOCCODE; ?></td>
		<td><?php echo $rsItem['po_supplier']; ?></td>
		<!-- <td><?php echo $rsItem['product_no']; ?></td> -->
		<td style="color:red;font-weight:bold"><?php echo $rsItem['product_no']; ?></td>
		<td><?php echo $rsItem['product_name']; ?></td>
        <td style="color:red;font-weight:bold;text-align:right"><?php echo number_format($rsItem['order_qty']); ?></td>
        <td>
            <input type="text" id="numQty<?php echo $row_id; ?>" rel="<?php echo $row_id; ?>" value="<?php echo number_format($rsItem['product_qty']); ?>" class="numQty" placeholder="จำนวนที่รับ" class="form-control" size='7'
                   onblur="$.checkInputNum('<?php echo $rsItem['order_qty']?>',this.value,'<?php echo $row_id; ?>')"/>
        </td>
		<td><?php echo $rsItem['product_unit']; ?></td>
		<td>
            <?php
            if($rsItem['po_status'] == '1'){
                $sqlLocation = $db->get_where('inbound_location',array('inbound_id'=>$rsItem['inbound_id']));
                foreach($sqlLocation->result_array() as $rsLocation){
                    echo getNameLocation($rsLocation['location_id']).'('.$rsLocation['qty'].')</br>';
                }
            }
            ?>
		</td>
		<td>
            <input id="chk_<?php echo $row_id; ?>" type="checkbox" value="<?php echo $row_id; ?>" <?php if($product_fefo==1){ ?> checked <?php } ?> onChange="$.checkFefo(this)" >
			<span id="div_fefo_<?php echo $row_id; ?>" <?php if($product_fefo==0){ echo 'style="display:none"'; } ?>>
                <input type="text" id="date_create_<?php echo $row_id; ?>" class="create_datepicker" style="width:80px"
                       data-mask="9999-99-99" value="<?php echo $product_create_date; ?>" placeholder="วันผลิต" rel="<?php echo $row_id; ?>" />
                <input id="numExp<?php echo $row_id; ?>" value="<?php echo $numExp?>" type="hidden"/>
				<input type="text" id="date_fefo_<?php echo $row_id; ?>" class="fefo_datepicker" style="width:80px"
                       data-mask="9999-99-99" value="<?php echo $product_fefo_date; ?>" placeholder="วันหมดอายุ" rel="<?php echo $row_id; ?>" />

                <button id="bS_<?php echo $row_id; ?>" <?php if($product_fefo==1){ echo 'style="display:none"'; } ?> class="btn btn-xs btn-success" onclick="$.updateFefo(<?php echo $row_id; ?>)"><i class="fa fa-floppy-o"></i></button>
				<button id="bE_<?php echo $row_id; ?>" <?php if($product_fefo==0){ echo 'style="display:none"'; } ?> class="btn btn-xs btn-warning" onclick="$.updateFefo(<?php echo $row_id; ?>)"><i class="fa fa-pencil-square-o"></i></button>
				<button id="bD_<?php echo $row_id; ?>" <?php if($product_fefo==0){ echo 'style="display:none"'; } ?> class="btn btn-xs btn-danger" onclick="$.removeFefo(<?php echo $row_id; ?>)"><i class="fa fa-times"></i></button>
                <br/>เหลือ<input type="text" id="remainDate<?php echo $row_id; ?>" placeholder="วันคงเหลือ" class="form-control" size='4' value="<?php echo date_sum_remain($product_fefo_date);?>"/>วัน
            </span>
		</td>
	</tr>
<?php
$n++;
}
?>
</tbody>
</table>

</div>
<?php // echo $count_po; ?>
<script>
$(function(){
    //-------------------------------------------------------------------------------
	var oTable = $('#table-inbound').dataTable({
		"pageLength": 100,
	});
	$('.create_datepicker').datepicker({
	 	format: 'yyyy-mm-dd',
        autoclose:true
	}).on('changeDate',function(e){
        var id = $(this).attr('rel');
        $.changeCreateExp(this.value,$('#numExp'+id).val(),id);
    });
    $('.fefo_datepicker').datepicker({
	 	format: 'yyyy-mm-dd',
        autoclose:true
	}).on('changeDate',function(e){
        var id = $(this).attr('rel');
        var dateExp = $(this).val();
        $.changeCreateExp('','',id,dateExp);
    });

	var search = $('#table-inbound_filter').find('input[type="search"]');
	$(search).val('<?php echo $search; ?>');
	$(search).focus();

    var checkConfirm = $('<input />',{ class:'btn btn-primary',name:'confirmChk',type:'checkbox',value:'1',id:'confirmChk'}).css({'margin-left':'20px' });
    var textConfirm = $('<label>').html('ยืนยันรับสินค้า');
	$(checkConfirm).click(function(e){
        console.log($('#confirmChk').prop('checked'));
	});

    var buttonSave = $('<button />',{ class:'btn btn-primary' }).html('บันทึก').css({ 'float':'right'});
	$(buttonSave).click(function(e){
        $.save();
	});
    <?php
    if(isset($rsStatus['status']) && $rsStatus['status']==0){
        echo "$('#table-inbound_filter').append(checkConfirm);
              $('#table-inbound_filter').append(textConfirm);
              $('#table-inbound_filter').append(buttonSave);";
    }
    ?>

/*
	var input = $('<input />',{ id:'search_date', type:'text', class:'form-control input-sm' });
	$(input).val('<?php echo $todate; ?>');
	$(input).datepicker({
	 	format: 'yyyy-mm-dd'
	});

	var label = $('<label />').html(' Date: ');
	$(label).append(input);

	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=inbound_items&todate='+search_date;
	});
	//$('#table-inbound_filter').append(label);
	//$('#table-inbound_filter').append(button);
*/
//------------------------------------------------------------------------------------------------------
	$.checkFefo = function(obj){
		var id = obj.value;
		if($(obj).is(':checked')){
			$('#div_fefo_'+id).show();
		}else{
			$('#div_fefo_'+id).hide();
		}
	}
//-------------------------------------------------------------------------------------
	$.updateFefo = function(id){
		var date_fefo = $('#date_fefo_'+id).val();
        var product_create = $('#date_create_'+id).val();
		if(date_fefo!=''){
			$.post('show/inbound/inbound_action.php',{ method:'update_fefo', id:id, date_fefo:date_fefo,product_create:product_create },function(rs) {
				console.log(rs);
			});
		}
	}
//-------------------------------------------------------------------------------------
	$.removeFefo = function(id){
		if(confirm("ลบวันหมดอายุ")==true){
			$('#chk_'+id).prop('checked', false);
			$('#div_fefo_'+id).hide();
			$('#date_fefo_'+id).val('');
			$('#bS_'+id).show();
			$('#bE_'+id).hide();
			$('#bD_'+id).hide();
		}
	}
    //-----------------------------------------------------------------------------------
    $.checkInputNum = function(numMax,numIn,id){
        //console.log(id);
        if(numIn>numMax){
            alert('จำนวนรับสินค้าเกินจำนวน');
            $('#numQty'+id).focus();
            console.log($('#numQty'+id).val());
        }
    }
    //-----------------------------------------------------------------------------------
    $.changeCreateExp = function(dateIn,numExp,id,dateExp){
        var data = new Array();
        data.push({name:'method',value:'addday'});
        data.push({name:'inID',value:id});
        if(numExp != '' && dateIn != '' && id != ''){
            data.push({name:'numExp',value:numExp});
            data.push({name:'dateIn',value:dateIn});
            $.post('show/inbound/inbound_action.php',data,function(html){
                $('#remainDate'+id).val(html.remain);
                $('#date_fefo_'+id).val(html.date);
            },'json');
        }else if(dateExp != ''){
            data.push({name:'dateExp',value:dateExp});
            $.post('show/inbound/inbound_action.php',data,function(html){
                $('#remainDate'+id).val(html.remain);
            },'json');
        }
    };
    //-----------------------------------------------------------------------------------
    $.save = function(){
        var data = new Array();
        data.push({name:'method',value:'updateNumQTY'});
        if($('#confirmChk').prop('checked') == true){
            data.push({name:'confirmChk',value:'1'});
        }
        $('.numQty').each(function(i,e){
            if($(this).val()!= '' && $(this).val() != 0 ){
                var rel = $(this).attr('rel');
                var val = $(this).val();
                data.push({name:'numQTY['+rel+']',value:val});
            }
        });
        console.log(data);
        $.post('show/inbound/inbound_action.php',data,function(html){
            if(html.save == 'true'){
               $.loader('save');
               $.unloader('save');
            }
            if(html.changeStatus == 'true'){
                location.reload();
            }
        },'json');
    };
});
function startInbound(){
    if(confirm('คุณต้องการที่จะดำเนินการนี้หรือไม่')){
        $('#formStatus').submit();
    }
}
</script>
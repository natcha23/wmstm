<?php
if($_GET['poID']){
    $poID = $_GET['poID'];
    $supplierID = $_GET['supplier_id'];
}
$sqlItem = $db->get_where('inbound_po inpo',array('inpo.po_id'=>$poID));
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
    }else if(isset($rsStatus['status'])){
        getStatus_PO($rsStatus['status']);
    }
    ?>
    <div>
        <p><h5> PO Reference No. <?php echo $poID;?></h5>.</p>
        <div><h5> Supplier No. <?php echo $supplierID;?></h5></div>
    </div>
    <!-- Modal -->
    <div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div id="modalContent" class="modal-content animated bounceInRight">
                <!--<div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
                    <h4 class="modal-title">Modal title</h4>
                    <small class="font-bold">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</small>
                </div>-->
                <div class="modal-body" id="modalBodyContent">
                    <select id="updatePalletID" onchange="$.showProductInPallet(this.value);">
                        <option value="">เลือก</option>
                        <?php //--- GET inbound_pallet
                        $sqlPallet = $db->get_where('inbound_pallet','pallet_status = 0');
                        foreach($sqlPallet->result_array() as $rsPallet){
                            echo "<option value='".$rsPallet['pallet_id']."' onchange=''>".$rsPallet['pallet_id']."</option>";
                        }
                        ?>
                    </select>
                    <button class="btn btn btn-primary" onclick="$.addPallet('updatePallet')">นำเข้า</button>
                    <button class="btn btn btn-primary" style="float:right;clear: both;" onclick="$.addPallet('addNewPallet')">จัดพาเลทใหม่</button>
                    <div class="ibox-title" id="productInPallet"></div>
                </div>
                <div class="modal-footer">
                    <!--<div class="ibox-title" id="detailPallet" style="text-align:left;"></div>-->
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                    <!--<button type="button" class="btn btn-primary">Save changes</button>-->
                </div>
            </div>
        </div>
    </div>
    <!-- Modal -->
        <div style="float:right;"><button class="btn btn-primary" onclick="$.addPallet();">จัดพาเลท</button></div><!-- button add pallet -->
    <p>.</p>

    <table id="table-inbound" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <!-- <th>Supplier name</th> -->
                <th>Barcode</th>
                <th>Name</th>
                <?php if($rsStatus['status'] != 1){?>
                    <th>จำนวนที่สั่ง</th>
                <?php } ?>
                <?php if($rsStatus['status'] != 0){?>
                <th>จำนวนรับจริง</th>
                <th>จำนวนขึ้นพาเลท</th>
                <?php } ?>
                <th>Unit</th>
                <th>--</th>
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
        $db->select_sum('qty','sumQTY');
        $sqlSum = $db->get_where('inbound_pallet_item',array('inbound_key'=>$row_id));
        $rsSum = $sqlSum->row_array();
    ?>
        <tr>
            <td><input type="checkbox" id="checkbox<?php echo $row_id; ?>" name="inboundID[]" value="<?php echo $row_id; ?>"/></td>
            <!-- <td><?php echo $rsItem['product_no']; ?></td> -->
            <td style="color:red;font-weight:bold"><?php echo $rsItem['product_no']; ?></td>
            <td ><?php echo $rsItem['product_name']; ?></td>
            <td style="color:red;font-weight:bold;"><?php echo number_format($rsItem['product_qty']); ?></td>
            <td>
                <?php
                $sumQTY = $rsSum['sumQTY'];
                $remainSum = 0;
                if($sumQTY != NULL && $sumQTY!='' && $sumQTY < $rsItem['product_qty']){
                    $remainSum = $rsItem['product_qty'] - $sumQTY;
                }else if($sumQTY == NULL){
                    $remainSum = $rsItem['product_qty'];
                }
                ?>
                <input type="text" id="qty<?php echo $row_id?>" rel="<?php echo $row_id?>" value="<?php echo $remainSum; ?>"
                class="numQty" placeholder="จำนวน" size="7" onblur="$.checkInputNum('<?php echo $remainSum;?>',this.value,<?php echo $row_id?>)"/>
                <?php
                    if($remainSum == 0){
                        echo"<script>
                                $('#checkbox".$row_id."').attr('disabled',true);
                                $('#qty".$row_id."').attr('disabled',true);
                            </script>";
                    }
                ?>
            </td>
            <td><?php echo $rsItem['product_unit']; ?></td> <!--  หน่วนนับสินค้า -->
            <td>
                <?php
                    $sqlProductWhere = $db->get_where('inbound_pallet_item ipt',array('inbound_key'=>$row_id));
                    $rowProductWhere = $sqlProductWhere->num_rows();
                ?>
                <button onclick="$.showProductWherePallet('<?php echo $row_id;?>','<?php echo $rowProductWhere;?>');">รายละเอียด</button>
            </td>
        </tr>
    <?php
    $n++;
    }
    ?>
    </tbody>
    </table>
    <!-- Modal dialog -->
    <div class="modal inmodal" id="modalDialog" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <div class="modal-body">
                    <div class="ibox-title" id="modalDialogContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal dialog -->
    <!-- Modal dialog -->
    <div class="modal inmodal" id="managePallet" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content animated bounceInRight">
                <div class="modal-body">
                    <div class="ibox-title" id="palletContent"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-dismiss="modal" onclick="javascript:location.reload();">Close</button>
                </div>
            </div>
        </div>
    </div>
<!-- Modal dialog -->
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

//------------------------------------------------------------------------------------------------------
	$.checkFefo = function(obj){
		var id = obj.value;
		if($(obj).is(':checked')){
			$('#div_fefo_'+id).show();
		}else{
			$('#div_fefo_'+id).hide();
		}
	}
//------------------------------------------------------------------------------------------------------
    $.addPallet = function(method,palletID){
        var data = new Array();
        if($('input[name="inboundID[]"]:checked').length == 0){
            alert('กรุณาเลือกรายการ');
            return false;
        }else{
            $('#myModal').modal('show');
            $('input[name="inboundID[]"]:checked').each(function(){
                var id = $(this).val();
                data.push({name:'inboundKey[]' , value:id});
                data.push({name:'qty['+id+']' , value:$('#qty'+id).val()});
            });
            if(method == 'addNewPallet'){
                if(confirm('ยืนยัน')){
                    data.push({name:'method',value:'create_pallet'});
                    $.post('show/inbound/inbound_action.php',data,function(html){
                        if(html.save == 1){
                            $.managePallet(html.actionID,html.barcode);
                        }
                       // location.reload();
                    },'json');
                    $('#myModal').modal('hide');
                }
            }else if(method == 'updatePallet'){
                if($('#updatePalletID').val() == ''){;
                    alert('กรุณาเลือก พาเลท');
                }else{
                    if(confirm('ยืนยัน')){
                        var actionID = $('#updatePalletID').val();
                        data.push({name:'method',value:'update_pallet'});
                        data.push({name: 'palletID',value:actionID});
                        $.post('show/inbound/inbound_action.php',data,function(html){
                            alert('บันทึกข้อมูลเรียบร้อย');
                            $.managePallet(html.actionID,html.barcode);
                            //location.reload();
                        },'json');
                        $('#myModal').modal('hide');
                    }
                }
            }
            //$('#modalContent').html('data');
        }
    }
    //---------------------------
    $.managePallet = function(palletID,barcode){
        var show = '';
        var data = new Array();
        data.push({name:'method',value:'get_product_in_pallet'});
        data.push({name:'palletID',value:palletID});
        $.post('show/inbound/inbound_action.php',data,function(html){
            show += '<div style="padding-bottom:10px;">';
            if(html.palletStatus == 0){
                show += "<span id=spanBtnConfirm style='float:right;'><button id='btnConfirmPallet' class='btn btn-primary' onclick='$.confirmPallet("+palletID+");' >จัดพาเลทเสร็จ</button></span>";
            }
            show += "<button onclick=$.printDiv('printPallet'); >พิมพ์</button></div>";
            show += "<div id='printPallet'>";
            show += barcode;
            show += "<table border=1 width=100% class='table table-bordered'>";
            show += "<tr>";
            show += "<th>Po</th>";
            show += "<th>Barcode</th>";
            show += "<th>ชื่อ</th>";
            show += "<th>จำนวน</th>";
            show += "<th>หน่วยนับ</th>";
            show += "<th>supplier</th>";
            show += "</tr>";
            $(html.detail).each(function(i,e){
                show += "<tr>";
                show += "<td>"+e.po_id+'</td>';
                show += "<td>"+e.product_no+'</td>';
                show += "<td>"+e.product_name+'</td>';
                show += "<td>"+e.total+'</td>';
                show += "<td>"+e.product_unit+'</td>';
                show += "<td>"+e.supplier_id+'</td>';
                show += "</tr>";
            });
            show += "</table></div>";
            $('#palletContent').html(show);
        },'json');
        $('#managePallet').modal('show');
    }
//------------------------------------------------------------------------------------------------------
    $.checkInputNum = function(numMax,numIn,id){
        numIn = numIn*1;
        //console.log(id);
        if(numIn>numMax){
            alert('จำนวนรับสินค้าเกินจำนวน');
            $('#qty'+id).focus();
            //console.log($('#qty'+id).val());
        }
    }
//-----------------------------------------------------------------------------------------------------
    $.showProductInPallet = function(palletID){
        var data = new Array();
        data.push({name:'method',value:'get_product_in_pallet'});
        data.push({name:'palletID',value:palletID});
        var show = '';
        $.post('show/inbound/inbound_action.php',data,function(html){
            show += "<div><table border=1 width=100% class='table table-bordered'>";
            $(html.detail).each(function(i,e){
                show += "<tr>";
                show += "<td>"+e.product_no+'</td>';
                show += "<td>"+e.product_name+'</td>';
                show += "<td>"+e.total+'</td>';
                show += "<td>"+e.product_unit+'</td>';
                show += "</tr>";
            });
            show += "</table></div>";
            $('#productInPallet').html(show);
        },'json');
    }
    $.showProductWherePallet = function(inboundKey,row){
        if(row > 0){
            $('#modalDialog').modal('show');
            var data = new Array();
            data.push({name:'method',value:'get_product_where_pallet'});
            data.push({name:'inboundKey',value:inboundKey});
            var show = '';
            $.post('show/inbound/inbound_action.php',data,function(html){
                show += "<div><h3>Po:  "+html.po_id+" </br> Supplier No : "+html.po_supplier+" </br>  product No : "+html.product_no+"</h3>";
                show += "<table border=1 width=100% class='table table-bordered'>";
                show += "<tr><th>ID Pallet</th><th>จำนวน</th><th>หน่วยนับ</th><th>เวลาวางของ</th></tr>";
                $(html.pallet).each(function(i,e){
                    show += "<tr>";
                    show += "<td>"+e.pallet_id+'</td>';
                    show += "<td>"+e.total+'</td>';
                    show += "<td>"+html.product_unit+'</td>';
                    show += "<td>"+e.create_time+'</td>';
                    show += "</tr>";
                });
                show += "</table></div>";
                $('#modalDialogContent').html(show);
            },'json');
        }else{
            alert('ยังไม่ได้วางสินค้าลงพาเลท');
        }
    }
    $.confirmPallet = function(id){
        var data = new Array();
        data.push({name:'method',value:'manage_pallet_success'});
        data.push({name:'palletID',value:id});
        if(confirm('จัดสินค้าเรียบร้อย')){
            $.post('show/inbound/inbound_action.php',data,function(html){
                if(html.pallet_status == 1){
                    $('#btnConfirmPallet').hide();
                    $('#spanBtnConfirm').html('<span style=color:green>จัดพาเลทเสร็จ</span>');
                    alert('บันทึกเรียบร้อย');
                }
            },'json');
        }
    }
    $.printDiv = function(divName) {
       var printContents = $('#'+divName).html();
       //var originalContents = document.body.html;
       console.log(printContents);
        //document.body.innerHTML = printContents;
       var w = window.open('','_new');
       w.document.write(printContents);
       w.window.print();
       w.close();

     //document.body.innerHTML = originalContents;
    }
});
//-------------------------------------------------------------------------------------
function startInbound(){
    if(confirm('คุณต้องการที่จะดำเนินการนี้หรือไม่')){
        $('#formStatus').submit();
    }
}
</script>
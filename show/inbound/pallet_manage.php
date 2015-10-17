<?php
include('lib/barcode/tcpdf_barcodes_1d.php');
$sql = $db->get_where('inbound_pallet','pallet_status != 2');
?>

<div class="ibox-title">
    <div class="tab-content">
        <div id="tab-1" class="tab-pane active">
            <table id="table-inbound-po" class="table table-striped table-bordered table-hover dataTable">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>วันที่สร้าง</th>
                        <th>status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    foreach($sql->result_array() as $arr){
                        $db->select('ipt.pallet_id,ipt.po_id,ipt.supplier_id,ipt.inbound_key,SUM(ipt.qty) as toal');
                        $db->select('ip.product_no,ip.product_name,ip.product_unit');
                        $db->join('inbound_po ip','ip.inbound_id = ipt.inbound_key');
                        $db->group_by('ipt.inbound_key');
                        $sqlItem = $db->get_where('inbound_pallet_item ipt','ipt.pallet_id = '.$arr["pallet_id"]);
                        $rsItem = $sqlItem->result_array();

                        if($arr['pallet_status']==0){$status = '<h5 style=color:red;>กำลังจัดพาเลท<h5>';}
                        else if($arr['pallet_status'] == 1){ $status = '<h5 style=color:orange;font-weight: bold;>รอเก็บเข้าตำแหน่ง</h5>';}
                ?>
                        <tr>
                            <td><?php echo $arr['pallet_id']; ?></td>
                            <td><?php echo $arr['create_time']; ?></td>
                            <td><?php echo $status;?></td>
                            <td>
                                <button onclick="$.managePallet(<?php echo $arr['pallet_id']; ?>)">จัดการ</button>
                            </td>
                        </tr>
                        <div id="barcode<?php echo $arr['pallet_id']; ?>" style="display: none;">
                            <?php

                            $barcode = str_pad($arr['pallet_id'],10,"0",STR_PAD_LEFT);
                            $barcodeobj = new TCPDFBarcode($barcode, 'C128');
                            $showBarcode = $barcodeobj->getBarcodePNG(2, 30, array(0,0,0),$arr['pallet_id']);
                            echo'
                                <span class="detailBarcode" style="text-align: center; line-height:40px;font-size: 16px;float:right;">
                                    <span class="barcodeImg" style="margin-left:10px; margin-top:10px;">
                                        <img class="imgBarcode" src="data:image/png;base64,'.$showBarcode.'" style="" />
                                    </span>
                                </span>
                            ';
                            ?>
                        </div>
                <?php
                    }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<!-- Modal dialog -->
    <div class="modal inmodal" id="mainModal" tabindex="-1" role="dialog" aria-hidden="true">
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
<script>
$(function(){
	$('#nav-inbound').parent().addClass('active');
	$('#nav-inbound').addClass('in');
	$('#pallet_manage').addClass('active');

    $.managePallet = function(palletID){

        var show = '';
        var data = new Array();
        data.push({name:'method',value:'get_product_in_pallet'});
        data.push({name:'palletID',value:palletID});
        $.post('show/inbound/inbound_action.php',data,function(html){
            show += '<div style="padding-bottom:10px;">';
            if(html.palletStatus == 0){
                show += "<button id='btnConfirmPallet' class='btn btn-primary' onclick='$.confirmPallet("+palletID+");' style='float:right;' >จัดพาเลทเสร็จ</button>";
            }
            show += "<button onclick=$.printDiv('printPallet'); >พิมพ์</button></div>";
            show += "<div id='printPallet'>";
            show += $('#barcode'+palletID).html();
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
            $('#modalDialogContent').html(show);
        },'json');
        $('#mainModal').modal('show');
    }

    $.confirmPallet = function(id){
        var data = new Array();
        data.push({name:'method',value:'manage_pallet_success'});
        data.push({name:'palletID',value:id});
        if(confirm('จัดสินค้าเรียบร้อย')){
            $.post('show/inbound/inbound_action.php',data,function(html){
                if(html.pallet_status == 1){
                    $('#btnConfirmPallet').hide();
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

	var oTable = $('#table-inbound-po').dataTable({
		"pageLength": 50,
	});


	var button = $('<button />',{ class:'btn btn-sm btn-success' }).html('search').css({ 'margin-left':'3px' });
	$(button).click(function(e){
	 	var search_date = $('#search_date').val();
	 	window.location.href = '?page=inbound_pallet&date_delivery='+search_date;
	})
	//$('#table-inbound-po_filter').append(label);
	//$('#table-inbound-po_filter').append(button);
})
</script>


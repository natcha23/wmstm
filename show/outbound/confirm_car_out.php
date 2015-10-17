<?php
$db->distinct();
$db->select('oc.*');
$db->join('outbound_car oc','oc.outbound_rt = ors.rt_id' );
// $db->where('ors.status','4');
$db->group_by('organize_truck_id');
$sql = $db->get('outbound_rt_status ors');

?>


<div class="ibox-title">
<table id="table-outbound-list" class="table table-striped table-bordered table-hover" border="1">
    <thead>
        <tr>
            <th>ทะเบียนรถ</th>
            <th>ชื่อ-นามสกุล ผู้ขับ</th>
            <th>รายละเอียดรถ</th>
            <th>รายการใบ RT</th>
            <th>Delivery order</th>
			<th> -- </th>
        </tr>
    </thead>
    <tbody>
        <?php
        $loop = 1;
        foreach($sql->result_array() as $rs){
			$sqlCar = $db->get_where('car_list',array('car_id'=>$rs['car_id']));
			$rsCar = $sqlCar->row_array();
            $sqlCarMan = $db->get_where('user',array('user_permit_level'=>7,'car_id'=>$rs['car_id']));
            $rsCarMan = $sqlCarMan->row_array();

			$db->select('car.*, ors.delivery_order_id');
			$db->select('ors.rt_id, oc.out_car_id, oc.driver_id, oc.driver_name '.
					', oc.car_id, oc.other_car_code AS other_car_code '.
					', oc.date_time as car_out_date, oc.organize_truck_id');
			$db->join('outbound_car oc','oc.outbound_rt=ors.rt_id');
			$db->join('car_list AS car', 'oc.car_id = car.car_id', 'LEFT');
			$db->where('organize_truck_id', $rs['organize_truck_id']);
// 			$db->where('oc.car_id', $rs['car_id']);
// 			$db->where('ors.status','4');

			$sqlRt = $db->get('outbound_rt_status ors');
			$result = $sqlRt->row_array();

			foreach ($result as $index => $val) {
				if($val['car_id'] == $rsCar[$index]['car_id']) {
					array_merge($result, $rsCar);
				}
			}
			
        ?>
        <tr>
            <td>
            <!-- <input type="text" class="form-control" size="10" value="<?php echo $rsCar['car_code']?>">-->
            <div class="row">
				<div class="col-md-5">
	            	<select class="form-control m-b" name="car" id="car_<?php echo $loop; ?>">
	            	<?php
                		$db->select("car_id, car_code")->from("car_list");
                		$db->where(array("status" => 0));

						$sql = $db->get();
                		$car_arr = $sql->result_array();
                		foreach($car_arr as $car) {
                	?>
		            	<option value="<?php echo $car['car_id']; ?>" <?php if($result['car_id'] == $car['car_id'] ) { echo "selected"; } ?> ><?php echo $car['car_code']; ?></option>
		            <?php } ?>
				        <option value="other" <?php if($result['car_id'] == -1) { echo "selected"; }?>>Other</option>
			        </select>
		        </div>
		        <div class="col-md-7" <?php if($result['car_id'] != -1) { echo 'style="display:none"'; }?> id="divothercar_<?php echo $loop;?>"><input type="text" value="<?php echo $result['other_car_code'];?>" name="car_other" id="car_other_<?php echo $loop;?>" class="form-control" placeholder="Car" size="10"></div>
		    </div>
            </td>
            <td><?php //echo $rsCarMan['user_fname'].'-'.$rsCarMan['user_lname']?>

            <div class="form-group">
				<div class="col-md-12">
					<div class="row">
						<div class="col-md-4">
		                	<select class="form-control m-b" name="driver" id="driver_<?php echo $loop; ?>">

		                	<?php
		                		$db->select("user_id, user_pname, user_fname, user_lname, user_permit_level, CONCAT_WS(' ', user_fname, user_lname) AS drivername", FALSE)->from("user");
								$db->join("user_permission AS perm", "user.user_permit_level = perm.permission_id", "LEFT");
								$db->where(array("user.status" => 0, "user.user_permit_level" => 7));

								$sql = $db->get();
		                		$drivers_arr = $sql->result_array();
		                		foreach($drivers_arr as $driver) {
		                	?>
		                    	<option value="<?php echo $driver['user_id']; ?>" <?php if($rsCarMan['user_id'] == $driver['user_id'] ) { echo "selected"; } ?> ><?php echo $driver['drivername']; ?></option>
		                  	<?php } ?>
		                    	<option value="other" <?php if($result['driver_id'] == -1) { echo "selected"; }?>>Other</option>
		                    
							</select>
						</div>
						<div class="col-md-6" <?php if($result['driver_id'] != -1) { echo 'style="display:none"'; }?> id="divother_<?php echo $loop;?>"><input type="text" value="<?php echo $result['driver_name'];?>" name="driver_other" id="driver_other_<?php echo $loop;?>" class="form-control" placeholder="Driver"></div>
					</div>
				</div>
			</div>

            </td>
            <td><?php if($result['car_id'] == -1) { echo "รถจากบริษัทภายนอก"; } else {echo $rsCar['car_detail']; }?></td>
            <td>
				<?php
				foreach($sqlRt->result_array() as $rsRt){
					echo $rsRt['rt_id'].'</br>';
				}
				?>
			</td>
			<td><?php if( !empty($result['delivery_order_id']) ) { echo $result['delivery_order_id']; } else { echo "-"; } ?></td>
			<td>
				
				<!-- <button class="btn btn-primary" type="button" id="caroutBTN">ออกรถ</button></a> 
				<!-- <a data-toggle="modal" data-remote="show/outbound/upload_form.php?rtID=<?php echo $result['rt_id']; ?>&row=<?php echo $loop;?>" data-target="#myModal" data-loop="<?php echo $loop; ?>"><button class="btn btn-primary" type="button">ออกรถ</button></a>
				<a data-toggle="modal" data-remote="show/outbound/delivery_form.php?rtID=<?php echo $result['rt_id']; ?>&row=<?php echo $loop;?>&car_id=<?php echo $result['car_id']; ?>" data-target="#myModal" data-loop="<?php echo $loop; ?>"><button class="btn btn-primary" type="button">ออกรถ</button></a> -->
				<button class="btn btn-primary" type="button" id="" onclick="$.modalBTN('<?php echo $loop; ?>');" <?php if ( !empty($result['delivery_order_id']) ) {?> disabled="disabled" <?php } ?>>
					ออกรถ
				</button>
				<?php if ( !empty($result['delivery_order_id']) ) { ?>
				
				<button type="button" class="btn btn-success" onclick="$.printCarOut('<?php echo $loop; ?>');">พิมพ์</button>
				<?php } ?>
				
                <!-- <button onclick="confirmOutCar(<?php echo $rs['car_id'];?>);">ออกรถ</button> -->
   				<!-- <input type="file" name="Filedata[<?php echo $rs['car_id'];?>]"> -->
   				<input type="hidden" id="row_<?php echo $loop;?>" value="">
   				<input type="hidden" id="rtid_<?php echo $loop; ?>" value="<?php echo $rsRt['rt_id']; ?>">
   				<input type="hidden" id="car_id_<?php echo $loop; ?>" value="<?php echo $result['car_id'];?>">
   				<input type="hidden" id="car_out_date_<?php echo $loop; ?>" value="<?php echo $result['car_out_date']; ?>">
   				<input type="hidden" id="organize_id_<?php echo $loop; ?>" value="<?php echo $result['organize_truck_id']; ?>">
   				
            </td>


        </tr>
        <?php
        	$loop++;
        } ?>
    </tbody>
</table>
<form id="formCarOut" method="post" action="show/outbound/outbound_action.php" enctype="multipart/form-data">
	<input type="hidden" name="carID" id="carID" value=""/>
	<input type="hidden" name="actionType" id="actionType" value="carOut"/>
</form>

<!-- Modal -->
<div class="modal inmodal" id="myModal" tabindex="-1" role="dialog" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content animated bounceInRight"></div>
	</div>
</div>
<!-- Modal -->

</div>



<div id="dialog" style="display:none">
<!--     <div> -->
    <iframe src="<?php echo _BASE_URL_;?>upload/examDO10092558.pdf" width="700" height="750"></iframe>
<!--     </div> -->
<!-- </div>  -->


</div>
<script>
$(function(){

	$.modalBTN = function(row) {
		if (confirm('ยืนยันการออกรถ') == true) {
			var data = new Array();
			/* gen delivery order */
			var drive = new Array();
	    	var driver = $('#driver_'+row).val();
	    	var driver_name = $('#driver_other_'+row).val();
	
	    	var car = $('#car_'+row).val();
	    	var car_code = $('#car_other_'+row).val();
	    	var car_out_date = $('#car_out_date_'+row).val();
	    	data.push({
	            name: 'driver_id',
	            value: driver
	        });
	    	data.push({
	            name: 'driver_name',
	            value: driver_name
	        });
	    	data.push({
	            name: 'car_id',
	            value: car
	        });
	    	data.push({
	            name: 'other_car_code',
	            value: car_code
	        });
	    	data.push({
	            name: 'car_out_date',
	            value: car_out_date
	        });

	    	var rt_id = $('#rtid_'+row).val();
			var organize_id = $('#organize_id_'+row).val();
			data.push({
	            name: 'rt_id',
	            value: rt_id
	        });
	    	data.push({
	            name: 'organize_id',
	            value: organize_id
	        });

	    	var chk = '';
	    	var chkcar = '';
	    	if(driver == "other" && driver_name == '') {
				chk = 'empty';
	    	}
	
	    	if(chk=='empty') {
				alert("ใส่ข้อมูลผู้ขับรถ");
				$('#myModal').modal('hide');
				$('#driver_other_'+row).focus();
				event.preventDefault();
				return false;
	    	}
	
	    	if(car == "other" && car_code == '') {
	    		chkcar = 'empty';
	    	}
	
	    	if(chkcar=='empty') {
				alert("ใส่เลขทะเบียนรถ");
				$('#myModal').modal('hide');
				$('#car_other_'+row).focus();
				event.preventDefault();
				return false;
	    	}
	    	$.loader();
	
		    $.post('show/outbound/delivery_action.php', data, function (html) {
		    	
		    	$.unloader();
// 				$('#myModal').modal('hide');
// 				window.location.reload();
				console.log(html);
// 				return false;
				event.preventDefault();
		    	var rt_id = $('#rtid_'+row).val();
				var car_id = $('#car_id_'+row).val();
				var organize_id = $('#organize_id_'+row).val();
				$('#myModal').removeData('bs.modal');
				$('#myModal').modal({remote: 'show/outbound/delivery_form.php?rtID='+rt_id+'&car_id='+car_id+'&organize_id='+organize_id});
				$('#myModal').modal('show');
			});
			
		} else {
			event.preventDefault();
		}

	}


	$.printCarOut = function(row) {
		
		var rt_id = $('#rtid_'+row).val();
		var car_id = $('#car_id_'+row).val();
		var organize_id = $('#organize_id_'+row).val();
		$('#myModal').removeData('bs.modal');
		$('#myModal').modal({remote: 'show/outbound/delivery_form.php?rtID='+rt_id+'&car_id='+car_id+'&organize_id='+organize_id});
		$('#myModal').modal('show');

	}

	
	$.printDiv = function(divID) {
        //Get the HTML of div
        var divElements = document.getElementById(divID).innerHTML;
        //Get the HTML of whole page
        var oldPage = document.body.innerHTML;
        //Reset the page's HTML with div's HTML only
        document.body.innerHTML = 
          "<html><head><title></title></head><body>" + 
          divElements + "</body>";

        //Print Page
        window.print();

        //Restore orignal HTML
//         document.body.innerHTML = oldPage;
//         $('#myModal').modal('hide');
        window.location.reload();
    }

	 $('#caroutBTN').click(function(){
        $("#dialog").dialog({
        	 width: 700,
        	 height: 750
        });
      }); 

	
	$('#nav-outbound').parent().addClass('active');
	$('#nav-outbound').addClass('in');
	$('#confirm_car_out').addClass('active');

// 	$('#car_'+id).val();
 	var car_id;
	var car_code;
	
	//triggered when modal is about to be shown
	$('#myModal').on('show.bs.modal', function(e) {
	
	    //get data-id attribute of the clicked element
	    var row = $(e.relatedTarget).data('loop');
	    car_id = $('#car_'+row).val();
	    car_code = $('#car_other_'+row).val();

	    if(car_id != "other") {
			car_code = $( '#car_'+row+' option:selected' ).text();
	    }
	    
	});
	
	$('#myModal').on('loaded.bs.modal', function (e) {
		$.uploadBt();
		$('#car_plate').html(car_code);
	})

	// This is for Bootstrap 3.
	$('body').on('hide.bs.modal', '.modal', function () {
		$(this).removeData('bs.modal');
		window.location.reload();
	})
	$('body').on('hidden.bs.modal', '.modal', function () {
		$(this).removeData('bs.modal');
	})

	var oTable = $('#table-outbound-list').dataTable({
		"pageLength": 50,
		"columns": [
		            { "width": "20%" },
		            { "width": "25%" },
		            { "width": "25%" },
		            null,
		            null,
		            null
		          ]
	});

	/* Toggle other driver textbox */
	$("[id^='driver_']").on('change', function (e) {
		var row = this.id.split("_")[1];
		if(this.value == "other") {
			$('#divother_'+row).show();
		} else {
			$('#divother_'+row).hide();
		}
	});
	$("[id^='car_']").on('change', function (e) {
		var row = this.id.split("_")[1];
		if(this.value == "other") {
			$('#divothercar_'+row).show();
		} else {
			$('#divothercar_'+row).hide();
		}
	});

	/* Delete file upload */
    $.removeFile = function (table, fileId) {
        if (confirm('ยืนยันการลบข้อมูล') == true) {
			//$.loader();
            $.post('show/outbound/upload_action.php', { 'action': 'del_file','table': table,'id': fileId }, function (rs) {
				//$.unloader();
                $('#image_' + fileId).fadeOut('fast', function () {
                    $(this).remove();
                    $('#image_' + fileId).remove();
                });
            });
        }
    }

	$.uploadAction = function(row, rtid) {
    	var data = new Array();
        var temp_id = $('#uploadArea input[name="temp_id[]"]');
        data.push({
            name: 'action',
            value: 'form_action'
        });
        data.push({
            name: 'rt_id',
            value: rtid
        });

    	$(temp_id).each(function (index, element) {
            data.push({
                name: $(element).attr('name'),
                value: $(element).val()
            })
        });

        /* Update driver */
        var drive = new Array();
    	var driver = $('#driver_'+row).val();
    	var driver_name = $('#driver_other_'+row).val();

    	var car = $('#car_'+row).val();
    	var car_code = $('#car_other_'+row).val();
    	var car_out_date = $('#car_out_date_'+row).val();
    	data.push({
            name: 'driver_id',
            value: driver
        });
    	data.push({
            name: 'driver_name',
            value: driver_name
        });
    	data.push({
            name: 'car_id',
            value: car
        });
    	data.push({
            name: 'other_car_code',
            value: car_code
        });
    	data.push({
            name: 'car_out_date',
            value: car_out_date
        });
    	var chk = '';
    	var chkcar = '';
    	if(driver == "other" && driver_name == '') {
			chk = 'empty';
    	}

    	if(chk=='empty') {
			alert("ใส่ข้อมูลผู้ขับรถ");
			$('#myModal').modal('hide');
			$('#driver_other_'+row).focus();
			event.preventDefault();
			return false;
    	}

    	if(car == "other" && car_code == '') {
    		chkcar = 'empty';
    	}

    	if(chkcar=='empty') {
			alert("ใส่เลขทะเบียนรถ");
			$('#myModal').modal('hide');
			$('#car_other_'+row).focus();
			event.preventDefault();
			return false;
    	}

	    $.post('show/outbound/upload_action.php', data, function (html) {
			$('#myModal').modal('hide');
			location.reload();
		});

    }


	/* New Upload Files */
	$.uploadBt = function(){
        $('.btUpload').upload({
            upload_url:'upload_action.php',
            multiFileUpload:1,
            filterType:'image',
            label: 'Attach Files',
            javasvriptCallback:function(f){
            	var imgName = f.file['name'];

             	var img_box = $('<div />',{ id:'image_'+f.id, class:'img_thumb' });
             	var img_remove = $('<i />',{ class:'fa fa-times img_remove' });
                var img_temp = $('<input />').attr({ 'type':'hidden', 'name':'temp_id[]', 'value':f.id });

                $(img_remove).click(function(e){
                	$.removeTemp(f.id);
                	$(this).parent().remove();
                })

                var imgType = ['jpg', 'jpeg', 'gif', 'png'];
                var docType = ['doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'pdf'];
                var mediaType = ['mp3', 'mp4', 'wmv', 'avi'];

                var arr = imgName.split('.');
                var fType = arr[arr.length - 1];
				var imgPath = '/thaimart/lib/img/icon/';

                if ($.inArray(fType, imgType) > -1) {
                    var img = $('<img />', {
                        src: window.URL.createObjectURL(f.file)
                    }).css({ 'width':'100px', 'height':'100px' });
                } else if ($.inArray(fType, docType) > -1) {
                    var i = $.inArray(fType, docType);
                    var img = $('<img />', {
                        src: imgPath + 'filetype_' + docType[i] + '.png'
                    }).css({ 'width':'100px', 'height':'100px' });
                } else if ($.inArray(fType, mediaType) > -1) {
                    var img = $('<img />', {
                        src: imgPath + 'filetype_mov.png'
                    }).css({ 'width':'100px', 'height':'100px' });
                } else {
                    var img = $('<img />', {
                        src: imgPath + 'untitle.png'
                    }).css({ 'width':'100px', 'height':'100px' });
                }

                $(img_box).append(img_remove);
                $(img_box).append(img);
                $(img_box).append(img_temp);
                $('#uploadArea').append(img_box);
            }
        });
    }
//     $.uploadBt();

    $.removeTemp = function(id){
    	$.post('remove_temp.php',{ id:id },function(html){
//     		console.log(html);
    	})
    }


});
function confirmOutCar(carID){
//     var carOut = $('<div />', {id: 'carOut'});
//     var data = new array();
//     $(carOut).html(html).dialog({
//         title: "ยืนยันรถออกและแนบไฟล์",
//         modal: true,
//         width: 800,
//         height: 'auto',
//         buttons: {
//             'save':function(){
//                 data.push({name:'',value:''});
//                 data.push({name:'',value:''});
//                 data.push({name:'',value:''});
//                 $('#carID').val(carID);
//             },
//             'ปิด': function () {
//                 $.loader();
//                 $.unloader();
//                 $(insert_form).dialog('destroy');
//             },
//         }, close: function () {
//             $.loader();
//             $.unloader();
//             $(insert_form).dialog('destroy');
//         }
//     });
	if(carID != ''){
		if(confirm('ยืนยันรถออก')){
			$('#carID').val(carID);
			$('#formCarOut').submit();
		}
	}
}


</script>
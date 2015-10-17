<button class="btn btn-success btn-facebook" onclick="showForm('','add');">
	<i class="fa fa-user-plus"> </i> เพิ่มรถ
</button>
<div class="ibox-content" id="add_form" style="display: none;">
    <form method="post" action="show/car/car_action.php" class="form-horizontal" id="form_add_user" novalidate="novalidate">
        <div class="form-group">
        	<label class="col-sm-2 control-label">ทะเบียนรถ</label>
            <div class="col-sm-10">
            	<div class="col-md-2"><input type="text" class="form-control" placeholder="ทะเบียน" id="carCode" name="carCode" required="" aria-required="true"></div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <div class="form-group">
        	<label class="col-sm-2 control-label">รายละเอียดรถ</label>
            <div class="col-sm-10">
            	<div class="col-md-12"><input type="text" class="form-control" placeholder="รายละเอียดรถ" name="carDetail" id="carDetail" required="" aria-required="true"></div>
            	<div id="regist_username_error" style="margin-left:15px"></div>
            </div>
        </div>
        <div class="hr-line-dashed"></div>

        <input type="hidden" name="carID" id="carID" value=""/>
        <input type="hidden" name="method" id="method" value="add"/>

        <div class="form-group">
            <div class="col-sm-4 col-sm-offset-2">
                <button class="btn btn-primary" type="submit">บันทึก</button>
                <button class="btn btn-danger" onclick="return showForm('','cancel');">ยกเลิก</button>
            </div>
        </div>
    </form>
</div>
<div class="ibox-title" id="dataCar">
    <table id="table-user" class="table table-striped table-bordered table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>ทะเบียน</th>
                <th>รายละเอียด</th>
                <th style="width:150px"></th>
            </tr>
        </thead>
        <tbody>
        <?php
            $db->select('*')->from('car_list')->where(array('status'=>0));
            $sql = $db->get();
            $row_count = 0;
            foreach($sql->result_array() as $arr){ $row_count++;
        ?>
            <tr>
                <td><?php echo $row_count; ?></td>
                <td id="code<?php echo $arr['car_id']; ?>"><?php echo $arr['car_code']; ?></td>
                <td id="detail<?php echo $arr['car_id']; ?>"><?php echo $arr['car_detail']; ?></td>
                <td>
                    <button class="btn btn-sm btn-warning" onclick="showForm(<?php echo $arr['car_id']; ?>,'edit')">แก้ไข</button>
                    <!--<a href="?page=user_action&action=del&id=<?php echo $arr['user_id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('ยืนนัยการลบข้อมูล')">ลบ</a>-->
                </td>
            </tr>
        <?php
            }
        ?>
        </tbody>
    </table>
</div>
<script>
function showForm(id,method){
    $('#add_form').show();
    if(method == 'edit'){
        $('#method').val('edit');
        $('#carCode').val($('#code'+id).html());
        $('#carDetail').val($('#detail'+id).html());
        $('#carID').val(id);
    }else if(method == 'add'){
        $('#method').val('add');
        $('#carCode').val('');
        $('#carDetail').val('');
    }else{
        $('#carCode').val('');
        $('#carDetail').val('');
        $('#add_form').hide();
        return false;
    }
}
</script>
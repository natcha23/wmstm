<?php
$db2 = DB();
$start = '0';
$num_show = '10';
$page = '1';
if(isset($_POST['p']))
{
	if($_POST['p']>1)
	{
		$page = $_POST['p'];
		$start = ($page-1)*$num_show;
	}
}
$sqlZone = $db->get_where('location_zone',array('status'=>'0')); // select zone
if(isset($_POST['groupZone'])&& $_POST['groupZone'] !='' ){
    $groupZone = $_POST['groupZone'];
    $db->where('zone_id',$groupZone);
    $db2->where('zone_id',$groupZone);
}
// select zone to show list

//------------------ selet location Row
$db->order_by("row_name", "asc");
$db->where('status','0');
$sqlAll = $db->get('location_row');
$numAll = $sqlAll->num_rows();
//echo $db->last_query();

//-------------------------------- Select location row have limit
$db2->order_by("row_name", "asc");
$db2->where('status','0');
$sqlRow = $db2->get('location_row',$num_show,$start);
$num_rows = $sqlRow->num_rows();
//echo $db->last_query();
//------------------------- settng pagination ---------
$args = array();
$args['page'] = $page;
$args['perPage'] = $num_show;
$args['title'] = 'รายการ';
$args['rows'] = $numAll;
$args['func'] = 'row.paginationChange';
$fpage = pagination($args);
$n = $start+1;
//--------------------------   --------------------
?>
<div>

    <!-- Form add -->
    <div class="ibox-title" id="formAdd">
        <input type="hidden" id="thisPage" value="set_row"/>
        <h2>ตั้งค่าแถว</h2>
        <input class="" type="button" value="สร้างแถว" onclick="$.toggle('#rowForm');"/>
        <div id="rowForm" class="" style="border:solid 2px; border-color: #e7eaec; padding: 20px; margin-top:5px; display:none;">
            <h2 id="titleHead">เพิ่มแถว</h2>
            <form class="form-horizontal" action="show/location_setting/manage.php" method="post">
                <div class="form-group ">
                    <label for="inputEmail3" class="col-sm-2 control-label">โซน :</label>
                    <div class="col-sm-3">
                        <select class="form-control m-b formInput" name="inputZone" id="inputZone" required>
                            <option value="">-- เลือก --</option>
                            <?php
                            foreach($sqlZone->result_array() as $rsZone){
                                echo "<option value='$rsZone[zone_id]'>$rsZone[zone_name]</option>";
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="form-group ">
                    <label for="inputEmail3" class="col-sm-2 control-label">ชื่อแถว :</label>
                    <div class="col-sm-3">
                        <input type="text" class="form-control formInput" id="nameRow" class="chkInput" name="nameRow" placeholder="ชื่อแถว" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="inputEmail3" class="col-sm-2 control-label">หมายเหตุ :</label>
                    <div class="col-sm-2">
                      <textarea type="text" class="form-control formInput" id="note" class="chkInput" name="note" placeholder="หมายเหตุ" cols="20" rows="3"></textarea>
                    </div>
                </div>

                <input type="hidden" id="actionType" name="actionType" value="addRow"/>
                <input type="hidden" id="rowID" name="rowID" value=""/>

                <input class="btn btn-outline btn-primary" type="submit" value="บันทึกข้อมูล"/>
                <button type="button" class="btn btn-outline btn-warning" onclick="row.cancel();">ยกเลิก</button>
            </form>
        </div>
        </form>
    </div>
    <!-- List  -->
    <?php

    ?>
    <div class="ibox-title">
        <div style="float:left;">
            เลือกโซน :
            <select id="showZone"  onchange="row.viewGroup(this.value);">
                <option value=""> ทั้งหมด </option>
                <?php
                foreach($sqlZone->result_array() as $rsZone){
                    echo "<option value='$rsZone[zone_id]'>$rsZone[zone_name]</option>";
                }
                ?>
            </select>
        </div>
        <div class="paginationClass" id="paging1" rel="<?=$page?>"><?=$fpage['pagination']?></div>
        <table class="table table-striped table-bordered table-hover dataTables-example dataTable dtr-inline">
            <thead>
                <tr>
                    <th>#</th>
                    <th>โซน</th>
                    <th>ชื่อแถว</th>
                    <th>หมายเหตุ</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="detailZone">
                <?php
                if($num_rows > 0){
                    foreach($sqlRow->result_array() as $rs){
                ?>
                        <tr id="tr<?=$n?>">
                            <td><?=$n?></td>
                            <td id="zoneID<?=$rs['row_id']?>" rel="<?=$rs['zone_id']?>"><?=zoneGetName($rs['zone_id'])?></td>
                            <td id="name<?=$rs['row_id']?>"><?=$rs['row_name']?></td>
                            <td id="note<?=$rs['row_id']?>"><?=$rs['note']?></td>
                            <td>
                                <button type="button" class="btn btn-outline btn-warning btn-sm" onclick="row.update('<?=$rs['row_id']?>');">แก้ไข</button>
                            </td>
                        </tr>
                <?php
                        $n++;
                    }
                }
                ?>
            </tbody>
        </table>
        <div class="paginationClass"><?=$fpage['pagination']?></div>
    </div>
</div>

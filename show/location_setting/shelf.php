<?php
    $rowID = '';
    $xMax = '';
    $yMax = '';
    $zoneID = '';
    if(isset($_POST['rowID']) && !empty($_POST['rowID']) && isset($_POST['zoneID']) && !empty($_POST['zoneID'])){
        $rowID = $_POST['rowID'];
        $zoneID = $_POST['zoneID'];
        // select num  x , y column from location_zone
        $sqlColumn = $db->get_where('location_zone',array('status'=>'0','zone_id'=>$zoneID));
        $rsColumn = $sqlColumn->row_array();
        $xMax = $rsColumn['num_column'];
        $yMax = $rsColumn['num_row'];
    }
    // select to show list choice
    $sqlZone = $db->get_where('location_zone',array('status'=>'0'));
    $sqlRow = $db->get_where('location_row',array('status' => '0'));
    $sqlShelfStatus = $db->get_where('location_shelf_status',array('status' => '0'));
?>
 <input type="hidden" id="thisPage" value="set_shelf"/>
 <!---------------------------------[  form update  ]----------------------------------->
 <div class="modal inmodal" id="testForm" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content animated fadeIn">
            <input id="shelfEditID" type="hidden" name="shelfEditID" value=""/>
            <input id="action" type="hidden" name="actionType" value="shelfUpdate"/>
            <div class="modal-body">
                ชื่อ :
                <input name="inputShelfName" class="form-control" id="inputShelfName" value=""/> </br>
                หมายเหตุ :
                <textarea type="text" class="form-control formInput" id="note" name="note" placeholder="หมายเหตุ" cols="20" rows="3"></textarea>
                <div>
                    <input type="radio" id="inputShelfStatus1" name="inputShelfStatus" value="1" > ทั่วไป
                    <input type="radio" id="inputShelfStatus2" name="inputShelfStatus" value="2" > เต็ม
                    <input type="radio" id="inputShelfStatus3" name="inputShelfStatus" value="3" > ไม่ควรวาง
                    <input type="radio" id="inputShelfStatus4" name="inputShelfStatus" value="4" > วางไม่ได้
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" data-dismiss="modal" onclick="shelf.update();">Save changes</button>
            </div>
        </div>
    </div>
</div>
 <!-------------------------------------    FORM    ------------------------------------------>

<div>
    <!--------------------------------------      Choice Zone     ------------------------------->
    <div class="row  border-bottom ibox-title" >
        <div class="" style="float:left;">
            เลือกโซน :
            <select id="showZone" class="input-sm" onchange="shelf.getRow(this.value);">
                <option value=""> -- เลือกโซน -- </option>
                <?php
                foreach($sqlZone->result_array() as $rsZone){
                    $checked = '';
                    if($zoneID == $rsZone['zone_id']){ $checked = 'checked';}
                    echo "<option value='$rsZone[zone_id]' $checked>$rsZone[zone_name]</option>";
                }?>
            </select>
        </div>
        <div id="" class="" style="float:left; padding-left: 20px;">
            <select id="showRow" class="input-sm"   onchange="shelf.getShelf(this.value);" hidden>
                <option value=""> -- เลือกแถว -- </option>
                <?php
                    foreach($sqlRow->result_array() as $rsRow){
                        $checked = 'hidden';
                        if($rowID == $rsRow['row_id']){ $checked = 'checked';}?>
                        <option class="groupZone zone<?=$rsRow['zone_id']?>" value="<?=$rsRow['row_id']?>" <?=$checked?> ><?=$rsRow['row_name']?></option>
                <?php
                    }?>
            </select>
        </div>
    </div>
    <!--------------------------------------      Choice Zone     ------------------------------->

    <!--------------------------------------      setting shelf-status checkbox      ------------------------------->
    <did class="wrapper wrapper-content">
        <div  id="editShelfStatus" class="ibox-title">
            แก้ไขสถานะบ้าน :
            <select id="updateStatus" class="input-sm" onchange="shelf.getRow(this.value);">
                <option value=""> -- เลือกสถานะ -- </option>
                <?php
                foreach($sqlShelfStatus->result_array() as $rsStatus){
                    echo "<option value='$rsStatus[shelf_status_id]' >$rsStatus[shelf_status_name]</option>";
                }?>
            </select>
            <button type="button" onclick="shelf.updateStatusShelf()">แก้ไขสถานะ</button>
        </div>
        <!--------------------------------------      Shelf Table     ------------------------------->
        <div  id="detailShelf" class="ibox-title">
            <table id="tableShelf" class="table table-bordered">
                <?php
                    if(!empty($rowID) && !empty($xMax) && !empty($yMax)){
                        for($x=$xMax ; $x>=1 ; $x-- ){?>
                            <tr id="x<?=$x?>" rel="<?=$x?>">
                            <?php
                            for($y=1;$y<=$yMax;$y++){
                                    //select shelf
                                    $where = array(
                                        'status' => '0',
                                        'row_id' => $rowID,
                                        'shelf_column' => $x,
                                        'shelf_row' => $y
                                    );
                                    $width = 100/$yMax;
                                    $sqlShelf = $db->get_where('location_shelf',$where);
                                    $rsShelf = $sqlShelf->row_array();
                                    $tdClass = '';
                                    if($rsShelf['shelf_status'] != ''){
                                        $tdClass = "shelf-status".$rsShelf['shelf_status'];
                                    }
                                    ?>
                                    <td id="y<?=$y?>" class="td-hover <?=$tdClass?>" title="<?=$rsShelf['note']?>" style="height:100px; width:<?=$width?>%; position:relative;" >
                                        <div>
                                            <div><?=$rsShelf['shelf_name']?></div>
                                            <div><?='('.$x.':'.$y.')'?></div>
                                        </div>
                                        <div style="bottom:10px; left:5px; position:absolute;">
                                            <button class="btn btn-warning btn-circle" type="button" data-toggle="modal" data-target="#testForm"
                                                    onclick="shelf.showFormUpdate('<?=$rsShelf['shelf_id']?>','<?=$rsShelf['shelf_name']?>','<?=$rsShelf['note']?>','<?=$rsShelf['shelf_status']?>');">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                        </div>
                                        <span style="float:right; bottom:10px; right:5px; position:absolute;">
                                            <input type="checkbox" name="inputCheckList[]" value="<?=$rsShelf['shelf_id']?>" />
                                        </span>
                                    </td>
                                    <?php
                                    //echo $rsShelf['shelf_status'];
                            }
                            echo '</tr>';
                        }
                    }
                ?>
            </table>
        </div>
    </did>
    <!--------------------------------------      Shelf Table     ------------------------------->
</div>
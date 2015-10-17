<?PHP
// print_r($_SESSION);
$path='show/outbound/';
$page = '';

if(isset($_GET['page'])){
    $page = $_GET['page'];
}
//check session
if($page == ''){
    $page = 'home';
}



$isBooking = preg_match('/^[booking_]/', $page);
$isOutbound = preg_match('/^[outbound_]/', $page);

if($isBooking) {
// 	if($isOutbound) {
// 		$_GET['finddate'] = "2015-08-27";
// 	}
	//$_GET['finddate'] = (isset($_GET['finddate']))? $_GET['finddate']:"2015-09-02";

}



///////////////////////////////////////////////////////////////////////////////////////////////////
if($page == '' || $page == 'home' ){
    include_once("home.php");//home
}else if($page == 'login' || $page == 'logout'){
    include_once("login.php");
}else if($page == 'test_country'){
    include_once("test/list_country.php");
}
//////////////////////////////  ** PRODUCT  ** /////////////////////////////
else if($page == 'product'){
    include_once('show/product/product.php');
}else if($page == 'detail_product_in'){
    include_once('show/product/detail_product_in.php');
}else if($page == 'detail_product_out'){
    include_once('show/product/detail_product_out.php');
}else if($page == 'detail_product_location'){
    include_once('show/product/detail_product_location.php');
}else if($page == 'product_import'){
    include_once('show/product/product_import.php');
}else if($page == 'stock_card'){
    include_once('show/product/stock_card.php');
}
//////////////////////////////  ** SUPPLIER  ** /////////////////////////////
else if($page == 'supplier_import'){
    include_once('show/supplier/supplier_import.php');
}

////////////////////////// Outbound ////////////////////////
else if($page == 'outbound_items'){
    include_once('show/outbound/outbound_items.php');
}else if($page == 'outbound_list'){
    include_once('show/outbound/outbound_list.php');
}else if($page == 'outbound_process'){
    include_once('show/outbound/outbound_process.php');
}else if($page == 'outbound_allitems'){
    include_once('show/outbound/outbound_allitems.php');
}else if($page == 'outbound_check'){
    include_once('show/outbound/outbound_check.php');
}else if($page == 'outbound_rtcheck'){
    include_once('show/outbound/outbound_rtcheck.php');
}else if($page == 'confirm_car_out'){
    include_once('show/outbound/confirm_car_out.php');
}else if($page == 'outbound_import'){
   	include_once('show/outbound/outbound_import.php');
}else if($page == 'outbound_import_byrt'){
   	include_once('show/outbound/outbound_import_byrt.php');
}else if($page == 'test'){
	include_once ('show/outbound/testrb.php');
}else if($page == 'make_data'){
	include_once ('show/outbound/make_data.php');
}

////////////////////////// IBP ////////////////////////
else if($page == 'ibp_import_in_date'){
    include_once('show/inbound/ibp_import_in_date.php');
}

/////////////////////////////  inbound   ///////////////////////
else if($page == 'inbound_items2'){
    include_once('show/inbound/inbound_items2.php');
}else if($page == 'inbound_items'){
    include_once('show/inbound/inbound_items.php');
}else if($page == 'inbound_items_show'){
    include_once('show/inbound/inbound_items_show.php');
}else if($page == 'inbound_po'){
    include_once('show/inbound/inbound_po.php');
}else if($page == 'inbound_start'){
    include_once('show/inbound/inbound_start.php');
}else if($page == 'inbound_success'){
    include_once ('show/inbound/inbound_success.php');
}else if($page == 'inbound_get_product'){
    include_once ('show/inbound/inbound_get_product.php');
}else if($page == 'inbound_import'){
    include_once ('show/inbound/inbound_import.php');
}else if($page == 'inbound_import_bypo'){
    include_once ('show/inbound/inbound_import_bypo.php');
}

 ///////////////////////////////  Pallet  ///////////////////////////////
else if($page == 'inbound_pallet_item'){
    include_once ('show/inbound/inbound_pallet_item.php');
}else if($page == 'pallet_manage'){
    include_once ('show/inbound/pallet_manage.php');
}

///////////////////////////////////  SETTING  ///////////////////////////
else if($page == 'user'){
    include_once('show/user/index.php');
}else if($page == 'user_edit'){
    include_once('show/user/user_edit.php');
}else if($page == 'user_action'){
    include_once('show/user/user_action.php');
}else if($page == 'car'){
    include_once('show/car/car.php');
}else if($page == 'setting_product'){
    include_once('show/product/setting_product.php');
}else if($page == 'setting_zone'){
    include_once('show/zone/zone.php');
}
/////////////////////////////  category   ///////////////////////
else if($page == 'catagory'){
    include_once('show/inventory/catagory_index.php');
}else if($page == 'category_edit'){
    include_once('show/inventory/category_edit.php');
}else if($page == 'category_action'){
    include_once('show/inventory/category_action.php');
}else if($page == 'category_sub_index'){
    include_once('show/inventory/category_sub_index.php');
}
/////////////////////////////  address   ///////////////////////
else if($page == 'address'){
    include_once('show/inventory/address_index.php');
}else if($page == 'address_edit'){
    include_once('show/inventory/address_edit.php');
}else if($page == 'address_action'){
    include_once('show/inventory/address_action.php');
}

/////////////////////////////  report   ///////////////////////
else if($page == 'outbound_report'){
	include_once('show/outbound/outbound_report.php');
}else if($page == 'report_missing'){
	include_once('show/outbound/report_missing.php');
}else if($page == 'report_supplier'){
	include_once('show/report/report_supplier.php');
}else if($page == 'report_outbound'){
	include_once('show/report/report_outbound.php');
}else if($page == 'report_aging'){
	include_once('show/report/report_aging.php');
}else if($page == 'report_transport'){
	include_once('show/report/report_transport.php');
}else if($page == 'report_product_receive'){
	include_once('show/report/report_product_receive.php');
}else if($page == 'report_location'){
	include_once('show/report/report_location.php');
}else if($page == 'report_supplier_perform'){
	include_once('show/report/report_supplier_perform.php');
}else if($page == 'report_inbound'){
	include_once('show/report/report_inbound.php');
}else if($page == 'report_status_product'){
	include_once('show/report/report_status_product.php');
}else if($page == 'report_product_trans'){
	include_once('show/report/report_product_trans.php');
}

else if($page == 'report_status_product_total'){
	include_once('show/report/report_status_product_totalsum.php');
}else if($page == 'report_status_product_rt'){
	include_once('show/report/report_status_product_rt.php');
}


/////////////////////////////  booking   ///////////////////////
else if($page == 'booking_inbound'){
	include_once('show/booking/booking_inbound.php');
}else if($page == 'booking_list'){
	include_once('show/booking/booking_list.php');
}else if($page == 'booking_confirm'){
	include_once('show/booking/booking_confirm.php');
}else if($page == 'report_booking_confirm'){
	include_once('show/booking/report_booking_confirm.php');
}

/////////////////////////////  404   ///////////////////////
else{
    include_once ('404.php');
}

?>
<script>

$(function() {
	$('.dataTables_length').parent().removeClass('col-sm-6').addClass('col-sm-4');
	$('.dataTables_filter').parent().removeClass('col-sm-6').addClass('col-sm-8');
	$('.dataTables_filter').addClass('pull-right');
});
</script>

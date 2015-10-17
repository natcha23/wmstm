<?php
if(!isset($_REQUEST['action'])){
	echo '
	<script>
		window.location.href = "?page=catagory"
	</script>
	';
	exit();
}

if($_GET['action']=='del'){
	$now = date("Y-m-d H:i:s");
	mysql_query("UPDATE tb_cat SET status=1,dateupdate='".$now. "' WHERE cat_id='". $_GET['id'] ."' ");
	echo '
	<script>
		window.location.href = "?page=catagory"
	</script>
	';
}
?>
<?php
$user_id = $_SESSION['userID'];
if(!isset($_REQUEST['action'])){
	echo '
	<script>
		window.location.href = "?page=address"
	</script>
	';
	exit();
}

if($_GET['action']=='del'){
	$now = date("Y-m-d H:i:s");
	mysql_query("UPDATE tb_address SET status=1,user_id='".$user_id."',dateupdate='".$now. "' WHERE add_id='". $_GET['id'] ."' ");
	echo '
	<script>
		window.location.href = "?page=address"
	</script>
	';
}
?>
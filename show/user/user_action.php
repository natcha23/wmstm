<?php
if(!isset($_REQUEST['action'])){
	echo '
	<script>
		window.location.href = "?page=user"
	</script>
	';
	exit();
}

if($_GET['action']=='del'){
	mysql_query("UPDATE user SET status=1 WHERE user_id='". $_GET['id'] ."' ");
	echo '
	<script>
		window.location.href = "?page=user"
	</script>
	';
}
?>
<?php
print_r($_POST);
if(!isset($_POST['username'])){
	exit();
}

$_SESSION['username'] = $_POST['username'];
$_SESSION['password'] = $_POST['password'];

?>
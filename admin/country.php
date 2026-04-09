<?php
ob_start();
session_start();
if(!isset($_SESSION['user'])) {
	header('location: login.php');
	exit;
}
header('location: shipping-cost.php');
exit;
?>

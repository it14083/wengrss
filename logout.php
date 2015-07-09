<?php
	include('functions.php');

	session_start();

	if(isset($_COOKIE['wengrss'])) {
		$arr = split(" ", $_COOKIE['wengrss']);
		$mysqli = db_connect();
		delete_cookie($mysqli, $_SESSION['uid'], $arr[1]);
	}

	session_destroy();
	header('Location: index.php');
?>

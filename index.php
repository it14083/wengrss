<html>
<head>
<title>wengrss</title>
</head>
<body>
<?php
	
	require_once("functions.php");

	if(!file_exists("db.ini")) {
		header('Location: setup.php');
		exit();
	}

	if(isset($_POST['name']) && isset($_POST['pw'])) {
		if(!$mysqli = db_connect())
			return;

		if(login_user($mysqli, $_POST['name'], $_POST['pw'])) {
			session_start();
			$_SESSION['uid'] = $_POST['name'];
			load_settings($mysqli,$_SESSION['uid']);
			header('Location: Feedreader.php');
			exit(); 
		}
	
		$mysqli->close();
		
	}
?>
<form action="index.php" method="POST">
	<table>
	<tr>
	<td>Name</td>
	<td><input type="text" name="name"></td>
	</tr>
	<tr>
	<td>Passwort</td>
	<td><input type="password" name="pw"></td>
	</tr>
	<tr>
	<td><input type="submit" value="Login"</td>
	</tr>
	</table>
	<br>
	<a href="register.php">Registrieren</a>
</form>

</body>
</html>

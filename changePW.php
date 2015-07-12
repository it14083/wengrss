<html>
<head>
	<title>Change Password</title>
	<link rel="stylesheet" href="css/style.css">
	<link rel="shortcut icon" href="./res/favicon.ico">
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
</head>
<body>
<?php
	require_once("functions.php");

	session_start();

	if(isset($_POST['oldpw']) && isset($_POST['pw']) && isset($_POST['pw2'])) {
		
		// pruefen ob die Passwoerter nicht leer sind
		if($_POST['pw'] != "" && $_POST['pw2'] != "") {
			$mysqli = db_connect();

			if($mysqli->connect_errno) {
				$error = "Failed to connect to MySQL: " . $mysqli->connect_error;

			// pruefen ob das richtige bestehende Passwort eingegeben wurde
			} else if(!check_password($mysqli,$_SESSION['uid'],$_POST['oldpw'])) {
				$error = "Wrong Password";

			// pruefen ob die beiden neu eingegeben Passwoerter uebereinstimmen
			} else if($_POST['pw'] != $_POST['pw2']) {
				$error = "New passwords dont match";
				$mysqli->close();

			// Passwort aendern
			} else if(change_password($mysqli,$_SESSION['uid'],$_POST['pw'])) {
				$mysqli->close();
				echo "<div class=container>";
				echo "<div class=success>Password changed</div></div>";
				header('Refresh: 2;url=Feedreader.php');
				exit();
			} else {
				$error = "Change failed";
				$mysqli->close();
			}

		} else {
			$error = "Empty fields";
		}
	}
?>

	<!-- Passwort Formular -->
	<div class="container">
		<form action="changePW.php" method="POST">
			<p><input type="password" name="oldpw" placeholder="Old Password">
			<p><input type="password" name="pw" placeholder="New Password">
			<p><input type="password" name="pw2" placeholder="Retype New Password">
			<p><input type="submit" value="Change"><input type="button" value="Go Back" onClick="location.href='Feedreader.php'">
		</form>
	</div>

<?php
	// Fehler ausgeben
	if(isset($error)) {
		echo "<div class=container>";
		echo "<div class=error>" . $error . "</div>";
		echo "</div>";
		unset($error);
	}
?>	

</body>
</html>

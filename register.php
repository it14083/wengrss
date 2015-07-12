<html>
<head>
	<title>New Account</title>
	<link rel="stylesheet" href="css/style.css">
	<link rel="shortcut icon" href="./res/favicon.ico">
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
</head>
<body>
<?php
	require_once("functions.php");

	if(isset($_POST['name']) && isset($_POST['email']) && isset($_POST['pw']) && isset($_POST['pw2'])) {

		//pruefen ob alles ausgefuellt wurde
		if($_POST['name'] != "" && $_POST['email'] != "" && $_POST['pw'] != "") {
			$mysqli = db_connect();

			if($mysqli->connect_errno) {
				$error = "Failed to connect to MySQL: " . $mysqli->connect_error;

			// pruefen ob der Benutzer bereits vergeben ist
			} else if(name_taken($mysqli, $_POST['name'])) {
				$error = $_POST['name'] . " already taken";
				$mysqli->close();

			// pruefen ob das eine valide Email ist
			} else if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
				$error = "Invalid Email";
				$mysqli->close();

			// pruefen ob 2mal das gleiche Passwort eingegeben wurde
			} else if($_POST['pw'] != $_POST['pw2']) {
				$error = "Passwords dont match";
				$mysqli->close();

			// Benutzer erstellen
			} else if(create_user($mysqli,$_POST['name'],$_POST['email'],$_POST['pw'])) {
				$mysqli->close();
				echo "<div class=container>";
				echo "<div class=success>Account created</div></div>";
				header('Refresh: 2;url=index.php');
				exit();
			} else {
				$error = "Registration failed";
				$mysqli->close();
			}

		} else {
			$error = "Empty fields";
		}
	}
?>
	<!-- Registrationg Formular -->
	<div class="container">
		<form action="register.php" method="POST">
			<p><input type="text" name="name" placeholder="Username">
			<p><input type="text" name="email" placeholder="Email">
			<p><input type="password" name="pw" placeholder="Password">
			<p><input type="password" name="pw2" placeholder="Retype Password">
			<p><input type="submit" value="Register"></p>
		</form>
	</div>

<?php
	//Fehlermeldung
	if(isset($error)) {
		echo "<div class=container>";
		echo "<div class=error>" . $error . "</div>";
		echo "</div>";
		unset($error);
	}
?>	

</body>
</html>

<html>
<head>
	<title>New Account</title>
	<link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php
	require_once("functions.php");

	if(isset($_POST['name']) && isset($_POST['email']) &&isset($_POST['pw'])) {
		if($_POST['name'] != "" && $_POST['email'] != "" && $_POST['pw'] != "") {
			if(!$mysqli = db_connect()) {
				return;
			}

			if(name_taken($mysqli, $_POST['name'])) {
				echo $_POST['name'] . " bereits vergeben";
			} else if(create_user($mysqli,$_POST['name'],$_POST['email'],$_POST['pw'])) {
				echo $_POST['name'] . " erstellt";
				$mysqli->close();
				header('Location: index.php');
				exit();
			} else {
				echo "erstellen fehlgeschlagen";
				$mysqli->close();
			}

		}
	}
?>
<div class="container">
	<div class="login">
		<h1>Register</h1>

		<form action="register.php" method="POST">
			<input type="text" name="name" placeholder="Username">
			<input type="text" name="email" placeholder="Email">
			<input type="password" name="pw" placeholder="Password">
			<p><input type="submit" value="Register"></p>
		</form>
	</div>
</div>

</body>
</html>

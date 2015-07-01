<html>
<head>
<title>Neuer Account</title>
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
				exit;
			}

			if(create_user($mysqli,$_POST['name'],$_POST['email'],$_POST['pw'])) {
				echo $_POST['name'] . " erstellt";
				add_folder($mysqli,$_POST['name'],"Default");
				add_folder($mysqli,$_POST['name'],"Favoriten");
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
<form action="register.php" method="POST">
	<table>
	<tr>
	<td>Name</td>
	<td><input type="text" name="name"></td>
	</tr>
	<tr>
	<td>Email</td>
	<td><input type="text" name="email"></td>
	</tr>
	<tr>
	<td>Passwort</td>
	<td><input type="password" name="pw"></td>
	</tr>
	<tr>
	<td><input type="submit" value="Erstellen"</td>
	</tr>
	</table>
</form>

</body>
</html>

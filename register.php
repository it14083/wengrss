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
			$mysqli = db_connect();

			if($mysqli->connect_errno) {
				$error = "Failed to connect to MySQL: " . $mysqli->connect_error;
			} else if(name_taken($mysqli, $_POST['name'])) {
				$error = $_POST['name'] . " already taken";
				$mysqli->close();
			} else if(create_user($mysqli,$_POST['name'],$_POST['email'],$_POST['pw'])) {
				$mysqli->close();
				header('Location: index.php');
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

<?php
	if(isset($error)) {
		echo "<div class=container>";
		echo "<div class=login>" . $error . "</div>";
		echo "</div>";
	}
?>	

</body>
</html>

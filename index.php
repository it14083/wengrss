<html>
<head>
<title>wengrss</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
   <?php
	   
	   require_once("functions.php");

	   if(!file_exists("db.ini")) {
		   header('Location: setup.php');
		   exit();
	   }

	   if(isset($_POST['login']) && isset($_POST['password'])) {
		   $mysqli = db_connect();

			if($mysqli->connect_errno) {
				$login_failed = "Failed to connect to MySQL: " . $mysqli->connect_error;
				$mysqli->close();
		   } else if(login_user($mysqli, $_POST['login'], $_POST['password'])) {
			   session_start();
			   $_SESSION['uid'] = $_POST['login'];
			   load_settings($mysqli,$_SESSION['uid']);
		   		$mysqli->close();
			   header('Location: Feedreader.php');
			   exit(); 
		   } else {
		   		$login_failed = "Wrong Password or Username";
				$mysqli->close();
			}
	   
		   
	   }
?>
<div class="container">
   <div class="login">
   		<h1>Login</h1>
	  <form action="index.php" method="POST">
		  <input type="text" name="login" placeholder="Username">
		  <input type="password" name="password" placeholder="Password">
		  <p><input type="submit" value="Login"><input type="button" value="Register" onClick="location.href='register.php'"></p>
	  </form>
   </div>
</div>

<?php
	if(isset($login_failed)) {
		echo "<div class=container>";
			echo "<div class=login>" . $login_failed . "</div>";
		echo "</div>";
	}
?>

</body>
</html>

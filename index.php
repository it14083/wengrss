<html>
<head>
<title>Login</title>
<link rel="stylesheet" href="css/style.css">
<link rel="shortcut icon" href="./res/favicon.ico">
<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
</head>
<body>
   <?php
	   
	   require_once("functions.php");

	   if(!file_exists("db.ini")) {
		   header('Location: setup.php');
		   exit();
	   }

	$mysqli = db_connect();
	if(isset($_COOKIE['wengrss'])) {
		$arr = split(" ", $_COOKIE['wengrss']);
		$name = $arr[0];
		$cookie = $arr[1];
	 	if(login_with_cookie($mysqli,$name,$cookie)) {
			session_start();
			$_SESSION['uid'] = $name;
			load_settings($mysqli,$name);
			$mysqli->close();
			header('Location: Feedreader.php');
			exit();
		}
	}
	
	if(isset($_POST['login']) && isset($_POST['password'])) {
		if($mysqli->connect_errno) {
			$login_failed = "Failed to connect to MySQL: " . $mysqli->connect_error;
			$mysqli->close();
	   } else if(login_user($mysqli, $_POST['login'], $_POST['password'])) {
		   session_start();
		   $_SESSION['uid'] = $_POST['login'];
			if(isset($_POST['cookie'])) {
				set_cookie($mysqli,$_SESSION['uid']);
			}

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
	  <form action="index.php" method="POST">
		  <p><input type="text" name="login" placeholder="Username">
		  <p><input type="password" name="password" placeholder="Password">
		  <p><input type="checkbox" name="cookie">Remember me
		  <p><input type="submit" value="Login"><input type="button" value="Register" onClick="location.href='register.php'">
	  </form>
   </div>

	<?php
		if(isset($login_failed)) {
			echo "<div class=container>";
			echo "<div class=error>". $login_failed . "</div>";
			echo "</div>";
			unset($login_failed);
		} 
	?>
</body>
</html>

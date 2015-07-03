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
		   if(!$mysqli = db_connect())
			   return;

		   if(login_user($mysqli, $_POST['login'], $_POST['password'])) {
			   session_start();
			   $_SESSION['uid'] = $_POST['login'];
			   load_settings($mysqli,$_SESSION['uid']);
			   header('Location: Feedreader.php');
			   exit(); 
		   }
	   
		   $mysqli->close();
		   
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

</body>
</html>

<html>
<head>
<title>Setup Database</title>
</head>
<body>
<?php
	if(isset($_POST['user']) && isset($_POST['db']) && isset($_POST['pw'])) {
		if($_POST['user'] != "" && $_POST['db'] != "" && $_POST['pw'] != "") {

         $user = $_POST['user'];
         $db = $_POST['db'];
         $pw = $_POST['pw'];

         echo $user . $pw . $db;
         $mysqli = new mysqli("localhost",$user,$pw,$db);

         $query = "CREATE TABLE `feeds` (
                     `id` int(32) NOT NULL AUTO_INCREMENT,
                     `owner` varchar(16) NOT NULL,
                     `url` varchar(64) NOT NULL,
                     `folder` varchar(16) NOT NULL,
                     `lastupdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                     PRIMARY KEY (`id`) 
                  )  ENGINE=InnoDB DEFAULT CHARSET=utf8";
         if($stmt = $mysqli->prepare($query)) {
            $stmt->execute();
         }


         $query = "CREATE TABLE `feed_entries` (
                     `id` int(32) NOT NULL AUTO_INCREMENT,
                     `title` varchar(16) NOT NULL,
                     `url` varchar(64) NOT NULL,
                     `feedid` int(32) NOT NULL,
                     `description` varchar(512) NOT NULL,
                     `content` varchar(8000) NOT NULL,
                     `date` date NOT NULL,
                     `marked_read` tinyint(1) NOT NULL,
                     PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
         if($stmt = $mysqli->prepare($query)) {
            $stmt->execute();
         }


         $query = "CREATE TABLE `folders` (
                     `owner` varchar(16) NOT NULL,
                     `name` varchar(16) NOT NULL,
                     `collapsed` tinyint(1) NOT NULL
                   ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
         if($stmt = $mysqli->prepare($query)) {
            $stmt->execute();
         }


         $query = "CREATE TABLE `users` (
                     `name` varchar(16) NOT NULL,
                     `email` varchar(32) NOT NULL,
                     `password` varchar(64) NOT NULL,
                     `salt` varchar(32) NOT NULL
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
         if($stmt = $mysqli->prepare($query)) {
            $stmt->execute();
         }


			$mysqli->close();
		}
	}
?>
<form action="setup.php" method="POST">
	<table>
	<tr>
	<td>SQL User</td>
	<td><input type="text" name="user"></td>
	</tr>
	<tr>
	<td>SQL DB</td>
	<td><input type="text" name="db"></td>
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

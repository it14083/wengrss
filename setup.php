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

         $mysqli = new mysqli("localhost",$user,$pw,$db);

         $query = "CREATE TABLE `feeds` (
                     `id` int(32) NOT NULL AUTO_INCREMENT,
                     `owner` varchar(32) NOT NULL,
					 `title` varchar(512) NOT NULL,
                     `url` varchar(1024) NOT NULL,
                     `folder` varchar(32) NOT NULL,
                     `lastupdated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                     PRIMARY KEY (`id`) 
                  )  ENGINE=InnoDB DEFAULT CHARSET=utf8";
         if($stmt = $mysqli->prepare($query)) {
            $stmt->execute();
         }


		$query = "CREATE TABLE `feed_entries` (
                     `id` int(32) NOT NULL AUTO_INCREMENT,
                     `title` varchar(256) NOT NULL,
                     `url` varchar(1024) NOT NULL,
                     `feedid` int(32) NOT NULL,
                     `description` varchar(1024) NOT NULL,
                     `content` varchar(8000) NOT NULL,
					 `owner` varchar(32) NOT NULL,
					 `folder` varchar(32) NOT NULL,
                     `date` datetime NOT NULL,
                     `marked_read` tinyint(1) NOT NULL,
					 `marked_fav` tinyint(1) NOT NULL,
                     PRIMARY KEY (`id`)
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
         if($stmt = $mysqli->prepare($query)) {
            $stmt->execute();
         }


         $query = "CREATE TABLE `folders` (
                     `owner` varchar(32) NOT NULL,
                     `name` varchar(32) NOT NULL,
                     `collapsed` tinyint(1) NOT NULL
                   ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
         if($stmt = $mysqli->prepare($query)) {
            $stmt->execute();
         }


         $query = "CREATE TABLE `users` (
                     `name` varchar(32) NOT NULL,
                     `email` varchar(128) NOT NULL,
                     `password` varchar(64) NOT NULL,
                     `salt` varchar(32) NOT NULL
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
         if($stmt = $mysqli->prepare($query)) {
            $stmt->execute();
         }


			$mysqli->close();
		}

		$file = 'db.ini';
		$handle = fopen($file, 'w') or die('Cannot open file: '. $file);
		fwrite($handle, "db_user = " . $user . "\n");
		fwrite($handle, "db = " . $db . "\n");
		fwrite($handle, "db_pw = " . $pw);
		fclose($handle);

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

<?php
	/*
	$_Post ID: 
		1: add_feed
		2: setRead
		3: getRead
		4: Folder Session
		5: Update Feeds
		6: Feed Session
		7: setFavorite
	
	*/
	if(isset($_POST['json'])){
		session_start();
		$daten = json_decode($_POST['json']);
		$id = $daten[0];
		$mysqli = db_connect();
		$return = 0;
		
		switch ($id){
			case 1:
				$folder = $daten[2];
				if($folder != Null){
					add_folder($mysqli,$_SESSION['uid'],$folder);
				}
				else{
					$folder = "Default";
				}
				$url = $daten[1];
				if (filter_var($url, FILTER_VALIDATE_URL)) {
					$return = 1;
					add_feed($mysqli, $_SESSION['uid'], $url, $folder);
					getFeed_entries($url, $_SESSION['uid'], $folder);
				}
				break;
			
			case 2:
				$feed_id = $daten[1];
				setRead($feed_id);
				break;
				
			case 3:
				$feed_id = $daten[1];
				$return = getRead($feed_id);
				break;
			
			case 4:
				//Ordner in SESSION packen
				$folder = $daten[1];
				unset($_SESSION['feed']);
				unset($_SESSION['folder']);
				folderSession($folder);
				break;
				
			case 5:
				updateFeeds();
				$return = 1;
				break;
				
			case 6:
				$feed = $daten[1];
				unset($_SESSION['folder']);
				unset($_SESSION['feed']);
				feedSession($feed);
				break;
			
			case 7:
				$idFav = $daten[1];
				setFavorite($idFav);
				break;
			
			case 8:
				$idFav = $daten[1];
				$return = getFavorite($idFav);
				break;
		}
		
		echo $return;
	
	}

	
function getFeed_entries($feed_url, $owner, $folder, $lastdate = 0){
		$mysqli = db_connect();
		$content = file_get_contents($feed_url);
		$xmlElement = new SimpleXMLElement($content);
		$id = get_id($feed_url);

		foreach($xmlElement->channel->item as $entry){
			$date = strftime("%Y-%m-%d %H:%M:%S", strtotime($entry->pubDate));
			if($date > $lastdate) {
				add_feedentry($mysqli,$id,$entry->title,$entry->link,$entry->description,$date, $owner, $folder);
			}
		}
	}
	
	function setFavorite($idFav){
		$mysqli = db_connect();
		$query = "UPDATE feed_entries SET marked_fav='1' WHERE id='$idFav'";
		if($stmt = $mysqli->prepare($query)){
			$stmt->execute();
		}
	}
	
	function getFavorite($idFav){
		$mysqli = db_connect();
		$query = "SELECT marked_fav FROM feed_entries WHERE id='$idFav'";
		if($stmt = $mysqli->prepare($query)){
			$stmt->execute();
			$stmt->bind_result($fav);
			if($stmt->fetch()){
				return $fav;
			}
		}
	}
	
	function setRead($feed_id){
		$mysqli = db_connect();
		$query = "UPDATE feed_entries SET marked_read='1' WHERE id='$feed_id'";
		if($stmt = $mysqli->prepare($query)){
			$stmt->execute();
		}
	}
	
	function getRead($feed_id){
		$mysqli = db_connect();
		$query = "SELECT marked_read FROM feed_entries WHERE id='$feed_id'";
		if($stmt = $mysqli->prepare($query)){
			$stmt->execute();
			$stmt->bind_result($read);
			if($stmt->fetch()){
				return $read;
			}
		}
	}
	
	function get_id($url){
		$mysqli = db_connect();
		$owner = $_SESSION['uid'];
		$query = "SELECT id FROM feeds WHERE url='$url' and owner='$owner'";
		if($stmt = $mysqli->prepare($query)){
			$stmt->execute();
			$stmt->bind_result($id);
			if($stmt->fetch()){
				return $id;
			}
			else{
				return null;
			}
		}
		$mysqli->close();
	}
		
	function db_connect() {

		if(!defined("DB_USER") || !defined("DB") || !defined("DB_PW")) {
			read_db_config();
		}

		$mysqli = new mysqli("localhost",DB_USER,DB_PW,DB);
		if($mysqli->connect_errno) {
			echo $mysqli->connect_errno;
			return false;
		}
		return $mysqli;
	}


	function updateFeeds() {
		$mysqli = db_connect();
		$owner = $_SESSION['uid'];
		$query = "SELECT url,folder,lastupdated FROM feeds where owner='$owner'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$stmt->bind_result($url, $folder, $date);
			while($stmt->fetch()) {
				getFeed_entries($url, $owner, $folder, $date);
			}
		} else {
			echo $mysqli->error;
		}

		$query = "UPDATE feeds SET lastupdated=now() WHERE owner='$owner'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}
	}


	function read_db_config() {
		$file = 'db.ini';
		if($data = parse_ini_file($file)) {
			define("DB_USER", $data['db_user']);
			define("DB", $data['db']);
			define("DB_PW", $data['db_pw']);
		} else {
			echo "db config error";
		}
	}

	function add_feed($mysqli,$owner,$url,$folder) {
		$owner = $mysqli->escape_string($owner);
		$url = $mysqli->escape_string($url);
		$folder = $mysqli->escape_string($folder);
		
		$content = file_get_contents($url);
		$xmlElement = new SimpleXMLElement($content);
		
		
		$title = $xmlElement->channel->title;
		
		$query="INSERT INTO feeds (owner, url, folder, title) VALUES('$owner','$url','$folder', '$title')";
		if($stmt = $mysqli->prepare($query)) {
			if($stmt->execute()) {
				return true;
			}
		}
		return false;
	}

	function delete_feed($mysqli, $feedid) {
		$query = "DELETE FROM feeds WHERE id='$feedid'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}
	}

	function add_feedentry($mysqli,$feedid,$title,$url,$description,$date, $owner, $folder) {
		$title = $mysqli->escape_string($title);
		$url = $mysqli->escape_string($url);
		$description = $mysqli->escape_string($description);
		$date = $mysqli->escape_string($date);
		$owner = $mysqli->escape_string($owner);
		$folder = $mysqli->escape_string($folder);

		$query = "INSERT INTO feed_entries (feedid, title, url, description, date, owner, folder) VALUES('$feedid','$title','$url','$description','$date','$owner','$folder')";
		if($stmt = $mysqli->prepare($query)) {
			if($stmt->execute()) {
				return true;
			}
		}
		echo $mysqli->error;
		return false;
	}

	function add_folder($mysqli,$owner,$folder) {
		$owner = $mysqli->escape_string($owner);
		$folder = $mysqli->escape_string($folder);
		$nameTaken = 0;
		if(ctype_alpha($folder)){
			$query = "SELECT name FROM folders WHERE name='$folder' AND owner='$owner'";
			if($stmt = $mysqli->prepare($query)) {
				$stmt->execute();
				$stmt->bind_result($name_taken);
				if($stmt->fetch()) {
					$nameTaken = 1;
				}
			}
			if($nameTaken == 0){
				$query = "INSERT INTO folders (owner, name) VALUES ('$owner','$folder')";
				if($stmt = $mysqli->prepare($query)) {
					if($stmt->execute()) {
						return true;
					}
				}
				return false;
			}
		}
	}

	function delete_folder($mysqli, $owner, $folder) {
		$query = "DELETE FROM folders WHERE owner='$owner' AND name='$folder'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}
	}
	
	function folderSession($folder){
		//Session Ordner schreiben, um den ausgewählten Ordner auszugeben
		$_SESSION['folder'] = $folder;
	}
	
	function feedSession($feed){
		$_SESSION['feed'] = $feed;
	}


	function name_taken($mysqli, $name) {
		$query = "SELECT name FROM users WHERE name='$name'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$stmt->bind_result($name_taken);
			if($stmt->fetch()) {
				return true;
			}
		} else {
			echo $mysqli->error;
			return true;
		}
			
		return false;
	}

	function create_user($mysqli, $name, $email, $pw) {

		$name = $mysqli->escape_string($name);
		$email = $mysqli->escape_string($email);
		$pw = $mysqli->escape_string($pw);

		$rand = "";
		for($i = 0; $i<128; $i++)
			$rand .= chr(mt_rand(0,255));

		$salt = substr(bin2hex($rand),0,32);
		$pw_hash = encrypt_password($pw, $salt);

		$query = "INSERT INTO users VALUE ('$name','$email','$pw_hash','$salt')";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}

		$query = "INSERT INTO settings (owner) VALUES ('$name')";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}

		return true;

	}

	function login_user($mysqli,$name,$pw) {
		$name = $mysqli->escape_string($name);
		$query = "SELECT password,salt FROM users WHERE name='$name'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$stmt->bind_result($pw_hash,$salt);
			if($stmt->fetch()) {
				if($pw_hash == encrypt_password($pw, $salt)) {
					return true;
				}
			}
		}

		return false;
	}

	function encrypt_password($pw, $salt) {
		return hash('sha256',$pw . $salt);
	}

?>

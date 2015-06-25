<?php
	/*
	$_Post ID: 
		1: add_feed
		2: add_feedentry
		3: add_folder
		
		4: get_feedentry
	
	*/
	if(isset($_POST['json'])){
		
		$daten = json_decode($_POST['json']);
		$id = $daten[0];
		
		$return = 0;
		switch ($id){
			case 1:
				$url = $daten[1];
				if (filter_var($url, FILTER_VALIDATE_URL)) {
					$return = 1;
					$mysqli = db_connect();
					add_feed($mysqli, "Philipp", $url, "Default");
					getFeed_entries($url);
				}
			break;
		}
		
		echo $return;
	}

	function getFeed_entries($feed_url){
		$mysqli = db_connect();
		$content = file_get_contents($feed_url);
		$xmlElement = new SimpleXMLElement($content);
		$id = get_id($feed_url);
		
		foreach($xmlElement->channel->item as $entry){
			add_feedentry($mysqli,$id,$entry->title,$entry->link,$entry->description,"Leer");
		}
	}
	
	function get_id($url){
		$mysqli = db_connect();
		$query = "SELECT id FROM feeds WHERE url='$url' and owner='Philipp'";
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

		$mysqli = new mysqli("localhost","Philipp","12345","Feedreader");
		if($mysqli->connect_errno) {
			echo $mysqli->connect_errno;
			return false;
		}
		return $mysqli;
	}

	function read_db_config() {
		$file = 'db.ini';
		if($data = parse_ini_file($file)) {
			define("DB_USER", $data['db_user']);
			define("DB", $data['db']);
			define("DB", $data['db_pw']);
		} else {
			echo "db config error";
		}
	}

	function add_feed($mysqli,$owner,$url,$folder) {
		$owner = $mysqli->escape_string($owner);
		$url = $mysqli->escape_string($url);
		$folder = $mysqli->escape_string($folder);

		$query="INSERT INTO feeds (owner, url, folder) VALUES('$owner','$url','$folder')";
		if($stmt = $mysqli->prepare($query)) {
			if($stmt->execute()) {
				return true;
			}
		}
		return false;
	}

	function add_feedentry($mysqli,$feedid,$title,$url,$description,$content) {
		$title = $mysqli->escape_string($title);
		$url = $mysqli->escape_string($url);
		$description = $mysqli->escape_string($description);
		$content = $mysqli->escape_string($content);

		$query = "INSERT INTO feed_entries (feedid, title, url, description, content) VALUES('$feedid','$title','$url','$description','$content')";
		if($stmt = $mysqli->prepare($query)) {
			if($stmt->execute()) {
				return true;
			}
		}
		return false;
	}

	function add_folder($mysqli,$owner,$folder) {
		$owner = $mysqli->escape_string($owner);
		$folder = $mysqli->escape_string($folder);

		$query = "INSERT INTO folders (owner, name) VALUES ('$owner','$folder')";
		if($stmt = $mysqli->prepare($query)) {
			if($stmt->execute()) {
				return true;
			}
		}
		return false;
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

			return true;
		}

		return false;
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

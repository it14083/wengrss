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
		8: getFavorite
		9: mark Read
		10: updateSettings
		11: remove 
		12: move to folder
	
	*/
	if(isset($_POST['json'])){
		session_start();
		$daten = json_decode($_POST['json']);
		$id = $daten[0];
		$mysqli = db_connect();
		$return = 0;
		
		switch ($id){
			case 1:
				$url = $daten[1];
				$folder = $daten[2];
				
				if($url == "" && $folder == ""){
					$return = "empty";
				}
				else{
					
					if($folder != Null){
						add_folder($mysqli,$_SESSION['uid'],$folder);
						$return = 1;
					}
					else{
						$folder = "Default";
					}
					
				
					if (filter_var($url, FILTER_VALIDATE_URL)) {
						$return = "url";
						if(check_feed($mysqli, $_SESSION['uid'], $url, $folder)){
							add_feed($mysqli, $_SESSION['uid'], $url, $folder);
							$urls[] = $url;
							$xml = getFeedXML($urls);
							$id = get_id($url);
							getFeed_entries($id, $xml[0], $_SESSION['uid'], $folder);
							$return = "done";
						}
					}
				}
				
				break;
			
			case 2:
				$feed_id = $daten[1];
				$value = getRead($feed_id);
				
				if($value == 0){
					$return = 1;
					$_SESSION['anzRead']++;
				}
				else{
					$return = 0;
					$_SESSION['anzRead']--;

				}
				setRead($feed_id, $return);
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
				$value = getFavorite($idFav);
				if($value == 0){
					$return = 1;
				}
				else{
					$return = 0;
				}
				setFavorite($idFav, $return);
				break;
			
			case 8:
				$idFav = $daten[1];
				$return = getFavorite($idFav);
				break;

			case 9:
				mark_page_read($mysqli);
				break;
			
			case 10:
				$checked = $daten[1];
				$show_images = $daten[2];
				$ttl = 14;
				$anzFeeds = 10;
				if(is_numeric($daten[3])){
					$ttl = $daten[3];
				}
				if(is_numeric($daten[4])){
					$anzFeeds = $daten[4];
				}
				update_settings($mysqli, $_SESSION['uid'], $ttl, $anzFeeds, $checked, $show_images);
				//echo $ttl;
				break;
			
			case 11:
				if(isset($_SESSION['folder'])){
					$folder = $_SESSION['folder'];
					if($folder != "Default" && $folder != "Favoriten" && $folder != "Alle"){
						//Feeds noch in Default verschieben
						delete_folder($mysqli, $_SESSION['uid'], $_SESSION['folder']);
						unset($_SESSION['folder']);
						$return = "folder";
					}
					else{
						$return = "Default";
					}
				}
				elseif(isset($_SESSION['feed'])){
					delete_feed($mysqli, $_SESSION['feed']);
					unset($_SESSION['feed']);
					$return = "feed";
				}
				break;
				
			case 12:
				$id = $daten[1];
				$folder = $daten[2];
				move_to_folder($mysqli, $id, $folder);
				break;
				
		}
		$mysqli->close();
		echo $return;
	
	}

	function getFeed_entries($id, $xml_string, $owner, $folder, $lastdate = 0) {
		//$id = get_id($feed_url);
		$youngest = $lastdate;

		$mysqli = db_connect();

		$xml = new DOMDocument("1.0");
		$xml->loadXML($xml_string);

		$atom = $xml->getElementsByTagName("feed");
		$item_tag = "item";
		$summary_tag = "description";
		$date_tag = "pubDate";

		if($atom->length > 0) {
			$item_tag = "entry";
			$summary_tag = "summary";
			$date_tag = "published";
		}

		$items = $xml->getElementsByTagName($item_tag);
		foreach($items as $item) {
			$title = extract_data($item->getElementsByTagName("title"));
			$summary = extract_data($item->getElementsByTagName($summary_tag));
			$link = $item->getElementsByTagName("link");
			if($atom->length > 0) {
				$link = $link->item(0);
				$link = $link->getAttribute("href");
			} else {
				$link = extract_data($link);
			}

			$date = extract_data($item->getElementsByTagName($date_tag));
			$date = strftime("%Y-%m-%d %H:%M:%S", strtotime($date));

			if($date > $lastdate) {
				add_feedentry($mysqli,$id,$title,$link,$summary,$date,$owner,$folder);
			}


			if($date > $youngest) {
				$youngest = $date;
			}
		}
		update_lastupdated($mysqli,$id,$youngest);
		$mysqli->close();
	}

	function extract_data($item) {
		$tmp = $item->item(0);
		$tmp = $tmp->firstChild->textContent;
		return $tmp;
	}

	
	function update_lastupdated($mysqli,$id,$date) {
		$query = "UPDATE feeds SET lastupdated='$date' WHERE id='$id'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}
	}

	function mark_page_read($mysqli) {
		$query = build_query_select_feeds();
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$stmt->bind_result($id,$title,$url,$desc,$date);
			$i = 0;
			$to_mark = $_SESSION['articles_per_page'];
			if($_SESSION['show_all'] == 0) {
				$to_mark = $_SESSION['articles_per_page'] - $_SESSION['anzRead'];
			}
			while($stmt->fetch() && $i < $to_mark) {
				setRead($id, 1);
				$i++;
			}
		}

	}

	function update_settings($mysqli, $owner, $ttl, $articles_per_page, $show_all, $show_images) {

		if(is_nan($ttl)) {
			$ttl = 14;
		} else if($ttl < 1) {
			$ttl = 14;
		}

		if(is_nan($articles_per_page)) {
			$articles_per_page = 10;
		} else if($articles_per_page < 1) {
			$articles_per_page = 10;
		}

		$_SESSION['ttl'] = $ttl;
		$_SESSION['articles_per_page'] = $articles_per_page;
		$_SESSION['show_all'] = $show_all;
		$_SESSION['show_images'] = $show_images;

		$query = "UPDATE settings SET time_to_live='$ttl', articles_per_page='$articles_per_page', show_all='$show_all', show_images='$show_images' WHERE owner='$owner'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}
	}

	function move_to_folder($mysqli, $id, $folder) {
		$query = "UPDATE feeds SET folder='$folder' WHERE id='$id'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}
		
		$query = "UPDATE feed_entries SET folder='$folder' WHERE feedid='$id'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}
	}

	function build_query_select_feeds() {
		
		$owner = $_SESSION['uid'];
		$feedid = "";
		$folder = "";
		$read = "AND marked_read='0'";
		$limit = $_SESSION['articles_per_page'];
		
		if(isset($_SESSION['feed'])) {
			$feedid = $_SESSION['feed'];
			$feedid = "AND feedid='$feedid'";
		}
		
		if($_SESSION['show_all'])
			$read = "";
		
		if(isset($_SESSION['folder'])) {
			$folder = $_SESSION['folder'];
			
			if($folder == "Alle"){
				$folder = "";
			}
			elseif($folder == "Favoriten"){
				$folder = "AND marked_fav='1'";
				$read = "";
			}
			else{
				$folder = "AND folder='$folder'";
			}
		}

		

		$query = "SELECT id,title,url,description,date FROM feed_entries WHERE owner='$owner' $feedid $folder $read  ORDER BY date desc Limit $limit";

		return $query;
	}

	function delete_feed($mysqli, $id) {
		$query = "DELETE FROM feeds WHERE id='$id'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$query = "DELETE FROM feed_entries WHERE feedid='$id'";
			if($stmt = $mysqli->prepare($query)) {
				$stmt->execute();
			}
		}
	}


	function setFavorite($idFav, $value){
		$mysqli = db_connect();
		$query = "UPDATE feed_entries SET marked_fav='$value' WHERE id='$idFav'";
		if($stmt = $mysqli->prepare($query)){
			$stmt->execute();
		}
		$mysqli->close();
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
		$mysqli->close();
	}
	
	function setRead($feed_id, $value){
		$mysqli = db_connect();
		$query = "UPDATE feed_entries SET marked_read='$value' WHERE id='$feed_id'";
		if($stmt = $mysqli->prepare($query)){
			$stmt->execute();
		}
		$mysqli->close();
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
		$mysqli->close();
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

		$ttl = $_SESSION['ttl'];
		$oldest = strftime("%Y-%m-%d %H:%M:%S", strtotime("-$ttl day"));
		$query = "DELETE FROM feed_entries WHERE owner='$owner' AND date<'$oldest' AND marked_fav='0'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}

		$query = "SELECT url,folder,lastupdated FROM feeds where owner='$owner'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$stmt->bind_result($url, $folder, $date);
			while($stmt->fetch()) {
				//getFeed_entries($url, $owner, $folder, $date);
				$urls[] = $url;
				$id[] = get_id($url);
				$folders[] = $folder;
				$dates[] = $date;
			}
		} else {
			echo $mysqli->error;
		}

		$content = getFeedXML($urls);
		$count = count($content);
		for($i=0; $i<$count;$i++) {
			getFeed_entries($id[$i], $content[$i],$owner,$folders[$i],$dates[$i]);
		}
		
		$mysqli->close();
		
	}


	function getFeedXML($urls) {
		$mh = curl_multi_init();

		foreach($urls as $url) {
			$ch_new = curl_init();
			curl_setopt($ch_new,CURLOPT_URL,$url);
			curl_setopt($ch_new,CURLOPT_HEADER,0);
			curl_setopt($ch_new,CURLOPT_RETURNTRANSFER,1);
			$chs[] = $ch_new;
			curl_multi_add_handle($mh,$ch_new);
		}

		$running=null;
		do{
			curl_multi_exec($mh,$running);
		} while($running > 0);

		foreach($chs as $ch) {
			$content[] = curl_multi_getcontent($ch);
		}

		return $content;
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
		
		$xml = new DOMDocument("1.0");
		$xml->load($url);

		$feed = $xml->getElementsByTagName("title");
		$title = extract_data($feed);
		
		$query="INSERT INTO feeds (owner, url, folder, title) VALUES('$owner','$url','$folder', '$title')";
		if($stmt = $mysqli->prepare($query)) {
			if($stmt->execute()) {
				return true;
			}
		}
		return false;
	}
	
	function check_feed($mysqli, $owner, $url, $folder){
		$query = "SELECT url FROM feeds WHERE url='$url' AND owner='$owner'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$stmt->bind_result($url_taken);
			if($stmt->fetch()) {
				return false;
			}
		}
		return true;	
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

		$query = "SELECT id FROM feeds WHERE owner='$owner' AND folder='$folder'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$stmt->bind_result($id);
			while($stmt->fetch()) {
				$id_arr[] = $id;
			}

			foreach($id_arr as $id) {
				move_to_folder($mysqli,$id,"Default");
			}
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
		
		add_folder($mysqli, $name, "Alle");
		add_folder($mysqli, $name, "Default");
		add_folder($mysqli, $name, "Favoriten");

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

	function load_settings($mysqli,$name) {
		$query = "SELECT time_to_live,articles_per_page,show_all,show_images FROM settings WHERE owner='$name'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$stmt->bind_result($ttl,$app,$show_all,$show_images);
			if($stmt->fetch()) {
				$_SESSION['ttl'] = $ttl;
				$_SESSION['articles_per_page'] = $app;
				$_SESSION['show_all'] = $show_all;
				$_SESSION['show_images'] = $show_images;
			}
		} else {
			echo $mysqli->error;
		}
	}

	function encrypt_password($pw, $salt) {
		return hash('sha256',$pw . $salt);
	}

	function check_password($mysqli,$name,$pw) {
		$query = "SELECT password,salt FROM users WHERE name='$name'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$stmt->bind_result($pw_hash,$salt);
			if($stmt->fetch()) {
				if($pw_hash == encrypt_password($pw,$salt)) {
					return true;
				}
			}
		}
		return false;
	}

	function change_password($mysqli,$name,$pw) {
		$pw = $mysqli->escape_string($pw);

		$rand = "";
		for($i = 0; $i<128; $i++)
			$rand .= chr(mt_rand(0,255));
			
		$salt = substr(bin2hex($rand),0,32);

		$pw_hash = encrypt_password($pw, $salt);
		$query = "UPDATE users SET password='$pw_hash',salt='$salt' WHERE name='$name'";

		if($stmt = $mysqli->prepare($query)) {
			return $stmt->execute();
		}
		return false;
	}

?>

<?php
	/*

	switch um die post requests zu verarbeiten

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
		13: Set folder index
	
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
			
			case 13:
				$folder = $daten[1];
				$id = $daten[2];
				setFolderID($mysqli, $folder, $id, $_SESSION['uid']);
				break;
				
		}
		$mysqli->close();
		echo $return;
	
	}


	/*
	traegt den uebergebenen feed in die Datenbank ein
	@param int $id id des feeds in der datenbank
	@param string $xml_string der runtergeladene feed
	@param string $owner benutzer zu dem der feed gehoert
	@param string $folder ordner in dem der feed steht
	@param datum(Y-m-d H:M:S) $lastdate datum des juengsten Artikels aus dem Feed
	*/
	function getFeed_entries($id, $xml_string, $owner, $folder, $lastdate = 0) {
		$youngest = $lastdate;

		$mysqli = db_connect();

		$xml = new DOMDocument("1.0");
		$xml->loadXML($xml_string);

		$atom = $xml->getElementsByTagName("feed");
		$item_tag = "item";
		$summary_tag = "description";
		$date_tag = "pubDate";

		// falls der Feed in atom ist -> die tags anpassen
		if($atom->length > 0) {
			$item_tag = "entry";
			$summary_tag = "summary";
			$date_tag = "published";

			$atom_content = $xml->getElementsByTagName("content");
			if($atom_content->length > 0) {
				$summary_tag = "content";
			}
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

			// nur neue Artikel eintragen
			if($date > $lastdate) {
				add_feedentry($mysqli,$id,$title,$link,$summary,$date,$owner,$folder);
			}

			// nach dem juengsten Artikel suchen
			if($date > $youngest) {
				$youngest = $date;
			}
		}
		update_lastupdated($mysqli,$id,$youngest);
		$mysqli->close();
	}

	/*
	gibt den Text aus dem ersten Kindelement vom xml item zurueck
	@param xml_item $item
	@return string text aus dem ersten Kindelement
	*/
	function extract_data($item) {
		$tmp = $item->item(0);
		$tmp = $tmp->firstChild->textContent;
		return $tmp;
	}

	/*
	setzt das Datum des juengsten Artikels fuer den Feed
	@param mysqli $mysqli
	@param int $id id des Feeds
	@param datum(Y-m-d H:M:S) $date neues Datum
	*/
	function update_lastupdated($mysqli,$id,$date) {
		$query = "UPDATE feeds SET lastupdated='$date' WHERE id='$id'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}
	}

	/*
	markiert die angezeigten Feeds als gelesen
	@param mysqli $mysqli
	*/
	function mark_page_read($mysqli) {
		$query = build_query_select_feeds();
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$stmt->bind_result($id,$title,$url,$desc,$date);

			// wirklich nur die ungelesen Artikel markieren die auch angezeigt werden
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

	/*
	schreibt die Settings in die Datenbank
	@param mysqli $mysqli
	@param string $owner Benutzer zu dem die Settings gehoeren
	@param int $ttl Zeit in Tagen die die Artikel in der Datenbank bleiben
	@param int $articles_per_page Anzahl der Artikel die angezeigt werden
	@param bool $show_all alle Artikel oder nur ungelesene anzeigen
	@param bool $show_images sollen Bilder in Artikeln angezeigt werden
	*/
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

	/*
	verschiebt einen Feed in einen Ordner
	@param mysqli $mysqli
	@param int $id id des Feeds
	@param string $folder Ordner in den der Feed geschoben wird
	*/
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

	/*
	baut abhaengig von den Settings einen query der die Artikel abruft
	die angezeigt werden sollen
	@return string
	*/
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

	/*
	loescht den Feed mit id und seine Artikel aus der Datenbank
	@param mysqli $mysqli
	@param int id Feed der geloescht wird
	*/
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

	/*
	markiert eine Feed als Favorit/nicht-Favorit
	@param int $idFav id des Artikel
	@param bool $value Favorit
	*/
	function setFavorite($idFav, $value){
		$mysqli = db_connect();
		$query = "UPDATE feed_entries SET marked_fav='$value' WHERE id='$idFav'";
		if($stmt = $mysqli->prepare($query)){
			$stmt->execute();
		}
		$mysqli->close();
	}
	
	/*
	gibt zurueck ob ein Artikel als Favorit markiert ist
	@param int $idFav id des Artikels
	@return bool
	*/
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
	
	/*
	markiert einen Artikel als gelesen/ungelesen
	@param int $feed_id id des Artikels
	@param bool $value gelesen
	*/
	function setRead($feed_id, $value){
		$mysqli = db_connect();
		$query = "UPDATE feed_entries SET marked_read='$value' WHERE id='$feed_id'";
		if($stmt = $mysqli->prepare($query)){
			$stmt->execute();
		}
		$mysqli->close();
	}
	
	/*
	gibt zurueck ob ein Artikel als gelesen markiert ist
	@param int @feed_id id des Feeds
	@return bool
	*/
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
	
	/*
	findet zu einer url die id in der Datenbank
	@param string $url url des Feeds
	@return int
	*/
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
	
	/*
	gibt eine Verbindung zur Datenbank zurueck
	@return mysqli
	*/
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

	/*
	loescht alte Artikel aus der Datenbank und holt sich die neuen Artikel
	*/
	function updateFeeds() {
		$mysqli = db_connect();
		$owner = $_SESSION['uid'];

		// loescht Artikel die laenger als $ttl in der Datenbank sind
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

	/*
	laedt den Inhalt aus $urls und gibt ihn zurueck
	@param string[] $urls Array mit den urls die geladen werden sollen
	@return string[]
	*/
	function getFeedXML($urls) {
		$mh = curl_multi_init();

		foreach($urls as $url) {
			$ch_new = curl_init();
			curl_setopt($ch_new,CURLOPT_URL,$url);
			curl_setopt($ch_new,CURLOPT_HEADER,0);
			curl_setopt($ch_new,CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch_new, CURLOPT_SSL_VERIFYPEER, false);
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


	/*
	liest die Datenbank-Konfiguration aus ./db.ini
	*/
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

	/*
	fuegt einen neuen Feed in die Datenbank ein
	@param mysqli $mysqli
	@param string @owner Benutzer zu dem der Feed gehoert
	@param string $url url des Feeds
	@param strign $folder Ordner in den der Feed eingefuegt wird
	*/
	function add_feed($mysqli,$owner,$url,$folder) {
		$owner = $mysqli->escape_string($owner);
		$url = $mysqli->escape_string($url);
		$folder = $mysqli->escape_string($folder);
		
		$urls[] = $url;
		$xml_content = getFeedXML($urls);
		$xml = new DOMDocument("1.0");
		$xml->loadXML($xml_content[0]);

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
	
	/*
	kontrolliert ob der Feed schon existiert
	@param mysqli $mysqli
	@param string $owner Benutzer zu dem der Feed gehoert
	@param string $url url des Feeds
	@param string $folder Ordner in dem der Feed ist
	*/
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

	/*
	fuegt einen Artikel in die Datenbank ein
	@param mysqli $mysqli
	@param int $feedid id des Feeds zu dem der Artikel gehoert
	@param string $tile Titel des Feeds
	@param string $description Content des Artikels
	@param datum(Y-m-d H:M:S) $date Datum des Artikels
	@param string $owner Benutzer zu dem der Feed gehoert
	@param string $folder Ordner aus dem der Feed ist
	*/
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

	/*
	fuegt einen Ordner in die Datenbank ein
	@param mysqli $mysqli
	@param string $owner Benutzer zu dem der Ordner gehoert
	@param string $folder Name des Ordners
	*/
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
				$query = "SELECT name FROM folders WHERE owner='$owner'";
				$anzFeeds = 0;
				if($stmt = $mysqli->prepare($query)) {
					$stmt->execute();
					$stmt->bind_result($name_taken);
					while($stmt->fetch()) {
						$anzFeeds++;
					}
				}
				$query = "INSERT INTO folders (owner, name, id) VALUES ('$owner','$folder','$anzFeeds')";
				if($stmt = $mysqli->prepare($query)) {
					if($stmt->execute()) {
						return true;
					}
				}
				return false;
			}
		}
	}

	/*
	loescht einen Ordner aus der Datenbank und schiebt die Feeds aus dem Ordner
	nach Default
	@param mysqli $mysqli
	@param string $owner
	@param string $folder
	*/
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

	/*
	setzt den Ordner dessen Feeds angezeigt werden sollen
	@param string $folder Ordner dessen Feeds angezeigt werden sollen
	*/
	function folderSession($folder){
		//Session Ordner schreiben, um den ausgewählten Ordner auszugeben
		$_SESSION['folder'] = $folder;
	}
	
	/*
	setzt den Feed dessen Artikel angezeigt werden sollen
	@param string $feed Feed dessen Artikel angezeigt werden sollen
	*/
	function feedSession($feed){
		$_SESSION['feed'] = $feed;
	}

	/*
	kontrolliert ob ein Benutzername schon vergeben ist
	@param mysqli $mysqli
	@param string $name
	*/
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

	/*
	erstellt einen neuen Benutzer und legt Default Settings an
	das passwort wird mit einem salt versehen und mit sha256 gehashed
	@param mysqli $mysqli
	@param string $name
	@param string $email
	@param stirng $pw
	*/
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
		
		add_folder($mysqli, $name, "Default");

		return true;

	}

	/*
	versucht einen Benutzer einzuloggen
	@param mysqli $mysqli
	@param string $name
	@param string $pw
	@return true on success
	*/
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

	/*
	laedt die Benutzer-Settings aus der Datenbank
	@param mysqli $mysqli
	@param string $name
	*/
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

	/*
	password hash mit salt
	@param string $pw
	@param string $salt
	*/
	function encrypt_password($pw, $salt) {
		return hash('sha256',$pw . $salt);
	}

	/*
	prueft ob das Passwort zum Benutzer passt
	@param mysqli $mysqli
	@param string $name
	@param string $pw
	@return true wenns passt
	*/
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

	/*
	aendert das Benutzer-Passwort
	@param mysqli $mysqli
	@param string $name
	@param string $pw
	@return true wenns geaendert wurde
	*/
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

	/*
	setzt ein Cookie mit Name und einer id und speichert die id in der Datenbank
	@param mysqli $mysqli
	@param string $owner
	*/
	function set_cookie($mysqli, $owner) {
		$nummer = rand();
		setcookie("wengrss",$owner . " " . $nummer, time()+60*60*24*14);
		$query = "INSERT INTO cookies (name, nummer) VALUES('$owner','$nummer')";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}
	}

	/*
	loescht ein Cookie aus der Datenbank
	@param mysqli $mysqli
	@param string $owner
	@param int $cookie id des cookies
	*/
	function delete_cookie($mysqli, $owner, $cookie) {
		$query = "DELETE FROM cookies WHERE nummer='$cookie' AND name='$owner'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		} else {
			$mysqli->error;
		}
	}

	/*
	versucht einen login mit dem Benutzer und der id des Cookies
	@param mysqli $mysqli
	@param string $owner Name aus dem Cookie
	@param int $cookie id aus dem Cookie
	@return true wenns klappt
	*/
	function login_with_cookie($mysqli,$owner,$cookie) {
		$query = "SELECT * FROM cookies WHERE name='$owner' AND nummer='$cookie'";
		$success = false;
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
			$success = $stmt->fetch();
		} else {
			echo $mysqli->error;
		}

		$new_mysqli = db_connect();
		if($success) {
			delete_cookie($new_mysqli,$owner,$cookie);
			set_cookie($new_mysqli, $owner);
		}
		$new_mysqli->close();
		return $success;
	}
	
	/*
	setzt die Reihenfolge fuer die Ordneranzeige
	@param mysqli $mysqli
	@param string $folder
	@param string $id
	@param $owner
	*/
	function setFolderID($mysqli, $folder, $id, $owner){
		$query = "UPDATE folders SET id='$id' WHERE name='$folder' AND owner='$owner'";
		if($stmt = $mysqli->prepare($query)) {
			$stmt->execute();
		}
	}

?>

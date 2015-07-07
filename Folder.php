
<?php
	require_once 'functions.php';
	//Ausgabe der Ordner, Owner muss noch hinzugefügt werden		
				
	$mysqli = db_connect();
	if(session_status() == PHP_SESSION_NONE){
			session_start();
	}
	$owner = $_SESSION['uid'];
	$query = "SELECT name FROM folders WHERE owner='$owner'";
	if($stmt = $mysqli->prepare($query)){
		$stmt->execute();
		$stmt->bind_result($folder);
		while($stmt->fetch()){
			echo "<button type='button' class='list-group-item' id='$folder'>".$folder."</button>";
			if($folder != "Favoriten" && $folder != "Alle"){
				echo "<div id='URL-$folder'>";
					echo"<p>";
						$mysqlURL = db_connect();
						$queryFeeds = "SELECT title, id FROM feeds WHERE folder='$folder' and owner='$owner'";
						if($stmtURL = $mysqlURL->prepare($queryFeeds)){
							$stmtURL->execute();
							$stmtURL->bind_result($title, $id);
						
							while($stmtURL->fetch()){
								echo "<button type='button' class='list-group-item' name= 'FeedButton' id='$id'>".$title."</button>";
								?>
								<script>
									$("#menue #<?=$id?>").click(function(){
										derFeed = $("#URL-<?=$folder?> #<?=$id?>").attr("id");
										var feld = new Array("6", derFeed);
										data = JSON.stringify(feld);
										var request = new XMLHttpRequest();
										request.open('post', 'functions.php', true);
										request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
										request.send('json='+data);
										request.onreadystatechange = function() {
											if (request.readyState==4 && request.status==200){
												$("#main").load("getFeed.php");;
											}
										}
					
									});
								</script>
								<?php
						
							//echo $title;
							//echo $id;
							}
						}
					echo "</p>";
				echo "</div>";
				$mysqlURL->close();
			}
			?>
			<script>
				$("#Folder #URL-<?=$folder?>").ready(function(){
					$("#Folder #URL-<?=$folder?>").hide();
				
					$( "#Folder #<?=$folder?>" ).click(function() {
						$("#Folder #URL-<?=$folder?>").slideToggle();
							derOrdner = $("#Folder #<?=$folder?>").attr("id");
							var feld = new Array("4", derOrdner);
							data = JSON.stringify(feld);
							var request = new XMLHttpRequest();
							request.open('post', 'functions.php', true);
							request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							request.send('json='+data);
							request.onreadystatechange = function() {
								if (request.readyState==4 && request.status==200){
									$("#main").load("getFeed.php");;
								}
							}
							
					});
					
				});

			
			</script>
			<?php
		}
	}
	$mysqli->close();
?>
	

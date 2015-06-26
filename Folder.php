
<?php
	require_once 'functions.php';
	//Ausgabe der Ordner, Owner muss noch hinzugefügt werden		
				
	$mysqli = db_connect();
	$query = "SELECT name FROM folders";
	if($stmt = $mysqli->prepare($query)){
		$stmt->execute();
		$stmt->bind_result($folder);
		while($stmt->fetch()){
			echo "<button type='button' class='list-group-item' id='$folder'>".$folder."</button>";
			echo "<div id='URL-$folder'>";
				echo"<p>";
					$mysqlURL = db_connect();
					$queryFeeds = "SELECT url FROM feeds WHERE folder='$folder'";
					if($stmtURL = $mysqlURL->prepare($queryFeeds)){
						$stmtURL->execute();
						$stmtURL->bind_result($url);
						
						while($stmtURL->fetch()){
							echo $url;
						}
					}
				echo "</p>";
			echo "</div>";
			?>
			<script>
				$("#Folder #URL-<?=$folder?>").ready(function(){
					$("#Folder #URL-<?=$folder?>").hide();
				
					$( "#Folder #<?=$folder?>" ).click(function() {
						$("#Folder #URL-<?=$folder?>").slideToggle();
					});
				});
			
			</script>
			<?php
		}
	}
?>
	
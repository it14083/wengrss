
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
					$queryFeeds = "SELECT title FROM feeds WHERE folder='$folder'";
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
							derOrdner = $("#Folder #<?=$folder?>").attr("id");
							var feld = new Array("4", derOrdner);
							data = JSON.stringify(feld);
							var request = new XMLHttpRequest();
							request.open('post', 'functions.php', true);
							request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							request.send('json='+data);
							request.onreadystatechange = function() {
								if (request.readyState==4 && request.status==200){
									//alert(request.responseText);
									$("#document.body #main").load("getFeed.php");;
								}
							}
							
					});
				});

			
			</script>
			<?php
		}
	}
?>
	
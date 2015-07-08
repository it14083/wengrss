
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
			echo "<button type='button' class='list-group-item folder' id='$folder'>".$folder."</button>";
			if($folder != "Favoriten" && $folder != "Alle"){
				echo "<div id='URL-$folder' class='drag'>";
				?>
				<script>
				$(function(){
					$( "#Folder #URL-<?=$folder?>").css('border','3px solid black');
					$( "#Folder #URL-<?=$folder?>").droppable({
						activeClass: "ui-state-default",
						hoverClass: "ui-state-hover",
						accept: ":not(.ui-sortable-helper)",
						drop: function( event, ui ) {
							var id = ui.draggable.detach().prop('id');
							var folder = "<?=$folder?>";
							
							var feld = new Array("12", id, folder);
							data = JSON.stringify(feld);
							var request = new XMLHttpRequest();
							request.open('post', 'functions.php', true);
							request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							request.send('json='+data);
							request.onreadystatechange = function() {
								if (request.readyState==4 && request.status==200){
									$("#menue #Folder").load("Folder.php");
								}
							}
						
					}});
				});
				</script>
				<?php
					echo"<p>";
						$mysqlURL = db_connect();
						$queryFeeds = "SELECT title, id FROM feeds WHERE folder='$folder' and owner='$owner'";
						if($stmtURL = $mysqlURL->prepare($queryFeeds)){
							$stmtURL->execute();
							$stmtURL->bind_result($title, $id);
						
							while($stmtURL->fetch()){
								echo "<button type='button' class='list-group-item feed' name= 'FeedButton' id='$id'>".$title."</button>";
								?>
								<script>
								/*	$("#menue #<?=$id?>").ready(function() {
										//$(".list-group-item").draggable();
										$(this).css('border','3px solid black')
									});
								*/
									$(function(){
										$("#menue .list-group-item.feed").button().draggable({cancel:false, appendTo: ".drag"});		
				
									});
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
	

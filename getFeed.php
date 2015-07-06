<html>
	<head>	
		<meta charset="utf-8">
	</head>
<body>

<?php
	
	require_once 'functions.php';
	
$feed_url= "https://news.google.de/news?pz=1&cf=all&ned=de&hl=de&output=rss";
	$url =  "http://www.spiegel.de/schlagzeilen/tops/index.rss";
	
	$derIndex = 0;
	
	printFeeds(10, 0);
	//getFeed($url);
	//getFeed($feed_url);
	
	//$mysqli = db_connect();
	//add_feed($mysqli, "Philipp", $feed_url, "Default");
	//add_feed($mysqli, "Philipp", $url, "Default");
	

	
	function printFeeds($limit, $read){
		if(session_status() == PHP_SESSION_NONE){
			session_start();
		}
		$owner = $_SESSION['uid'];
		$mysqli = db_connect();

		$query = build_query_select_feeds();

		if($stmt = $mysqli->prepare($query)){
			$stmt->execute();
			$stmt->bind_result($id, $title, $url, $description, $date);
			$color = 0;
			while($stmt->fetch()){
				if($color == 0){
					$color = 1;
				}
				else{
					$color = 0;
				}
				echo "<div id='Ausgabe$color'>";
				echo "<div id='Ausgabe' class='Ausgabe".$id."'>\n";
					echo "<a href='".$url."' title='".$title."' target='_blank'>" .$title."</a>";
					echo"<div id='Buttons'>";
						echo"<button type='button' id='delete' class='btn btn-default' aria-label='Left Align'>";
							echo"<span class='glyphicon glyphicon-ok' aria-hidden='true'></span>";
						echo"</button>";
						echo"<button type='button' id='favorite' class='btn btn-default'  aria-label='Left Align'>";
							echo"<span class='glyphicon glyphicon-star' aria-hidden='true'></span>";
						echo"</button>";
						?>
				
						<script>
						
						$( ".Ausgabe<?=$id?> #delete" ).ready(function() {
							//Überprüfen, ob es als gelesen markiert ist. Falls ja, Farbe ändern
							var feld = new Array("3", <?=$id?>);
							data = JSON.stringify(feld);
							var request = new XMLHttpRequest();
							request.open('post', 'functions.php', true);
							request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							request.send('json='+data);
							request.onreadystatechange = function() {
								if (request.readyState==4 && request.status==200){
									if(request.responseText == 1){
										$( ".Ausgabe<?=$id?> #delete").css('color','rgb(255, 127, 36)');
									}
								}
							}
						});
						$( ".Ausgabe<?=$id?> #delete" ).click(function() {
							//als gelesen markieren und dann Farbe ändern
							//$( ".Ausgabe<?=$id?>" ).remove(".Ausgabe<?=$id?>");
							var feld = new Array("2", <?=$id?>);
							data = JSON.stringify(feld);
							var request = new XMLHttpRequest();
							request.open('post', 'functions.php', true);
							request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							request.send('json='+data);
							request.onreadystatechange = function() {
								if (request.readyState==4 && request.status==200){
									if(request.responseText == 1){
										$( ".Ausgabe<?=$id?> #delete").css('color','rgb(255, 127, 36)');
									}
									else{
										$( ".Ausgabe<?=$id?> #delete").css('color','rgb(0, 0, 0)');
									}
								}
							}
						});
						$( ".Ausgabe<?=$id?> #favorite" ).ready(function() {
							//Überprüfen, ob es als gelesen markiert ist. Falls ja, Farbe ändern
							var feld = new Array("8", <?=$id?>);
							data = JSON.stringify(feld);
							var request = new XMLHttpRequest();
							request.open('post', 'functions.php', true);
							request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							request.send('json='+data);
							request.onreadystatechange = function() {
								if (request.readyState==4 && request.status==200){
									//alert(request.responseText);
									if(request.responseText == 1){
										
										$( ".Ausgabe<?=$id?> #favorite").css('color','rgb(255, 127, 36)');
									}
								}
							}
						});
						
						$( ".Ausgabe<?=$id?> #favorite" ).click(function() {
							//Farbe des Sterns ändern
							var feld = new Array("7", <?=$id?>);
							data = JSON.stringify(feld);
							var request = new XMLHttpRequest();
							request.open('post', 'functions.php', true);
							request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							request.send('json='+data);
							request.onreadystatechange = function() {
								if (request.readyState==4 && request.status==200){
									//alert("ok");
									if(request.responseText == 1){
										$( ".Ausgabe<?=$id?> #favorite"  ).css('color','rgb(255, 127, 36)');
									}
									else{
										$( ".Ausgabe<?=$id?> #favorite"  ).css('color','rgb(0, 0, 0)');
									}
								}
							}
							
					
						});
						</script>
						<?php
					echo"</div>";
	
					echo "<div id='Artikel'>";					
						echo $description ."<br>";
					echo "</div>";
					echo "<br>";
				echo "</div>";
				echo "</div>";
			}
		}
	}
	function getFeed($feed_url){
	
		$content = file_get_contents($feed_url);
		$xmlElement = new SimpleXMLElement($content);
	
		//$anzahlAusgabe = 10;
		$derIndex = 0;
		foreach($xmlElement->channel->item as $entry){
			echo "<div id='Ausgabe' class='Ausgabe".$derIndex."'>\n";
				echo "<a href='$entry->link' title='$entry->title'>" .$entry->title ."</a>";
				echo"<div id='Buttons'>";
					echo"<button type='button' id='delete' class='btn btn-default' aria-label='Left Align'>";
						echo"<span class='glyphicon glyphicon-ok' aria-hidden='true'></span>";
					echo"</button>";
					echo"<button type='button' id='favorite' class='btn btn-default'  aria-label='Left Align'>";
						echo"<span class='glyphicon glyphicon-star' aria-hidden='true'></span>";
					echo"</button>";

				?>
				<script>
				
				$( ".Ausgabe<?=$derIndex?> #delete" ).click(function() {
				//$( ".Ausgabe<?=$derIndex?> #1" ).click(function() {
					$( ".Ausgabe<?=$derIndex?>" ).remove(".Ausgabe<?=$derIndex?>");
					alert("ok");
					
				});
				
				$( ".Ausgabe<?=$derIndex?> #favorite" ).click(function() {
					
					$( this ).css('color','rgb(255, 127, 36)');
					
				});
				</script>
				<?php
				$derIndex++;
				echo"</div>";
	
	
				echo "<div id='Artikel'>";
					
					echo $entry->description ."<br>";
					//echo $xmlElement->channel->item."<hr>";
					//$anzahlAusgabe--;
				echo "</div>";
			
				echo "<br>";
			echo "</div>";
				/*if($anzahlAusgabe == 0){
					break;
				}
			*/
		
		}
		
	}
?>
</body>
</html>

<?php
	//header("Content-Type: text/html; charset=utf-8");
	include 'functions.php';
	
$feed_url= "https://news.google.de/news?pz=1&cf=all&ned=de&hl=de&output=rss";
	//$url =  "http://www.spiegel.de/schlagzeilen/tops/index.rss";
	
	$derIndex = 0;
	printFeeds(10, 0);
	//getFeed($url);
	//getFeed($feed_url);
	
	//$mysqli = db_connect();
	//add_feed($mysqli, "Philipp", $feed_url, "Default");
	//add_feed($mysqli, "Philipp", $url, "Default");
	
	function printFeeds($limit, $read){
		$mysqli = db_connect();
		$query = "SELECT id, title, url, description FROM feed_entries Limit $limit";
		if($stmt = $mysqli->prepare($query)){
			$stmt->execute();
			$stmt->bind_result($id, $title, $url, $description);
			while($stmt->fetch()){
				echo $title;
				echo "</br>";
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
	
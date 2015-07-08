<html>
	<head>	
	
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<title>Feedreader</title>
		<style>
			#header{ color: black; text-align:center; padding:5px; height:10%;}
			#menue { position:absolute; top:2%;line-height:30px; background-color:#F6F6F6; height:98%; width:20%; float:left; padding:5px; overflow-y:scroll; overflow-x:hidden;}
			#main {position:absolute; height:75%; width:79%; float:left; padding:10px; left:20%; top:20%;overflow-y:scroll;}
			#Navbar {position:absolute; height:5%; width:80%; float:left: padding:10px; left:20%; top:13%; }
			#Artikel{ width:75%; margin-bottom:5px; }
			#Ausgabe0{width: 100%; background-color:#F2F2F2;}
			#Ausgabe1{width: 100%;}
			#Ausgabe{margin-left:10%; }
			#Buttons{width:20%; position:absolute; left:80%;}
			#NavButtons{width:20%; position:absolute; left:76%; }
			#gelb{background-color:#ff7f24;}
			#Eingabe{width:90%; margin-left:4%;}
			#Folder{width:90%; margin-top:2%; margin-left:4%;}
			#EingabeButtons{position:absolute; width:100%; margin-top:2%;}
			#Add{width:87%; margin-left:9%;}
			#ttLive, #anzFeeds, #checkRead{position:absolute; left:65%; width:20%; margin-left:2%; margin-bottom:5%;}
			li{margin-bottom:5%; margin-top: 5%; margin-left:5%;}
			#saveSettings, #remove, #changePW{width:80%; margin-left:5%; margin-top:2%;}
			#Fehler, #Success{position: absolute; top:12%; margin-left:28%; width:50%; height:8%; text-align:center;}
			time{position:absolute; right:12%;}
			.folder{margin-top: 4%; font-weight: bold;}
			
			
			<!--.button{background-image:url("Klick.jpg"); margin-left:5px; background-repeat:no-repeat; margin: 0 2em; padding: .2em .5em; background-position: .5em center; padding-left: 3em; background:none transparent;}-->
			
		</style>
		
		<script src="//code.jquery.com/jquery-1.10.2.js"></script>
		<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
		
		<script>
			$(function(){
				//$(".list-group-item.feed").css('border','3px solid black');
				      
				$(".list-group-item.feed").button().draggable({cancel:false, appendTo: ".drag", helper: "clone", revert:"invalid"});
				$( ".drag" ).css('border','3px solid black');
				$( ".drag").droppable({
					activeClass: "ui-state-default",
					hoverClass: "ui-state-hover",
					accept: ":not(.ui-sortable-helper)",
					drop: function( event, ui ) {
						
						//alert(ui.draggable.detach().attr("id"));
						$(".list-group-item.feed").remove();
						ui.draggable.detach().appendTo(this);
					
						//$( "<li></li>" ).text( ui.draggable.text() ).appendTo( this );
					}});
			});
		</script>
			
	</head>

	<?php
		session_start();
		if(!isset($_SESSION['uid'])){
			header('Location: index.php');
		} else {
	?>
	<body>
		<?php
			include 'functions.php';
			//$_SESSION['folder'] =  "Bla";
			unset($_SESSION['folder']);
			unset($_SESSION['feed']);
			unset($_SESSION['anzRead']);
			$_SESSION['anzRead'] = 0;
			//updateFeeds();
			
		?>
		<div id="wrapper">
		
			<div id="header">
				<h1> Feedreader </h1>
			</div>
			
			<div id="menue">
				<span class='glyphicon glyphicon-plus' aria-hidden='true'></span><input id="Eingabe"></input></br>
				<span class='glyphicon glyphicon-folder-open' aria-hidden='true'></span><input id="Folder"> </input></br>
				<div id="EingabeButtons">
					<button type='button' id="Add" class='btn btn-default' aria-label='Left Align'>
							<span class='glyphicon glyphicon-plus' aria-hidden='true'></span> Add Content
					</button>
					
					<script>
						$( "#EingabeButtons #Add" ).click(function() {
							
							
							var url = document.getElementById("Eingabe").value;
							var folder = document.getElementById("Folder").value;
							//alert(document.getElementById("Eingabe").value);
							
							var feld = new Array("1", url, folder);
							data = JSON.stringify(feld);
							var request = new XMLHttpRequest();
							request.open('post', 'functions.php', true);
							request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							request.send('json='+data);
							request.onreadystatechange = function() {
								if (request.readyState==4 && request.status==200){
									document.getElementById("Eingabe").value = "";
									document.getElementById("Folder").value = "";
									$( "#wrapper #Fehler" ).empty();
									$( "#wrapper #Success" ).empty();
									if(request.responseText == "done" || request.responseText == 1){
										$( "#wrapper #Success" ).text("Successful!");
										$( "#wrapper #Success" ).show();
										$( "#wrapper #Success" ).delay(3000).fadeOut('slow');
									}
									else{
										if(request.responseText == 0){
											$( "#wrapper #Fehler" ).text("Sorry, but that's no URL");
										}
										else if(request.responseText == "url"){
											$( "#wrapper #Fehler" ).text("URL alreadys exists");
										}
										else if(request.responseText == "empty"){
											$( "#wrapper #Fehler" ).text("adfafs");
										}
										$( "#wrapper #Fehler" ).show();
										$( "#wrapper #Fehler" ).delay(3000).fadeOut("slow");
									}
									$("#main").load("getFeed.php");
									$("#menue #Folder").load("Folder.php");
								}
							}
							
						});
					</script>
				</div></br></br>
				<div id="Folder" class="list-group">
					<?php
					//Ausgabe der Ordner, Owner muss noch hinzugefügt werden
					include("Folder.php")
					?>
				</div>
			</div>
			<div class="alert alert-danger" role="alert" id="Fehler"></div>
			<div class="alert alert-success" role="alert" id="Success"></div>
			<script>
				$( "#wrapper #Fehler" ).ready(function() {
					$( "#wrapper #Fehler" ).hide();
				});
				$( "#wrapper #Success" ).ready(function() {
					$( "#wrapper #Success" ).hide();
				});
			</script>
			<div id="Navbar">
				<div id="NavButtons">
					<button type='button' id="allRead" title='Mark all displayed articles as read' class='btn btn-default' aria-label='Left Align'>
						<span class='glyphicon glyphicon-ok' aria-hidden='true'></span>
					</button>
					
					<script>
						$( "#NavButtons #allRead" ).click(function() {
							var feld = new Array("9");
							data = JSON.stringify(feld);
							var request = new XMLHttpRequest();
							request.open('post', 'functions.php', true);
							request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
							request.send('json='+data);
							request.onreadystatechange = function() {
								if (request.readyState==4 && request.status==200){
									$("#main").load("getFeed.php");
								}
							}

						});
					</script>
					<button type='button' id="refresh" title='Update all feeds' class='btn btn-default' aria-label='Left Align' onclick='refresh();'>
						<span class=' glyphicon glyphicon-refresh' aria-hidden='true'></span>
					</button>
					
						<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" title='Settings' data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
							<span class="glyphicon glyphicon-cog"></span>
						</button>
						<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
							<li>Unread only <input type="checkbox" id="checkRead"></input></li>
							<li>Show Feeds <input type="text" id="anzFeeds" value="<?=$_SESSION['articles_per_page'] ?>"> </input></li>
							<li>Time to live <input type="text" id="ttLive" value="<?=$_SESSION['ttl'] ?>"> </input></li>
							<li><button type='button' id="saveSettings" class='btn btn-default' aria-label='Left Align'>Save Settings</button></li>
							<li><button type='button' id='remove' class='btn btn-default' aria-label='Left Align'>Remove </button></li>
							<li><button type='button' id='changePW' class='btn btn-default' aria-label='Left Align' onClick="location.href='changePW.php'">Change PW</button></li>
						</ul>

					<button type='button' id='logout' title='Logout' class='btn btn-default' aria-label='Left Align' onClick="location.href='logout.php'">
						<span class='glyphicon glyphicon-log-out' aria-hidden='true'></span>
					</button>

				
					<script>
					$( "#NavButtons #checkRead" ).ready(function() {
						<?php
							if($_SESSION['show_all'] == 1){
								?>
								document.getElementById("checkRead").checked=false;
								<?php
							}
							else{
								?>
								document.getElementById("checkRead").checked=true;
								<?php
							}
						?>
						
					});
					$( "#NavButtons #saveSettings" ).click(function() {
						var checked = document.getElementById("checkRead").checked;
						if(checked){
							checked = 0;
						}
						else{
							checked = 1;
						}
						var ttl = document.getElementById("ttLive").value;
						var anzFeeds = document.getElementById("anzFeeds").value;
						
						var feld = new Array("10", checked, ttl, anzFeeds);
						data = JSON.stringify(feld);
						var request = new XMLHttpRequest();
						request.open('post', 'functions.php', true);
						request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
						request.send('json='+data);
						request.onreadystatechange = function() {
							if (request.readyState==4 && request.status==200){
								//alert(request.responseText);
								$( "#wrapper #Success" ).empty();
								$( "#wrapper #Success" ).text("Saved Settings!");
								$( "#wrapper #Success" ).show();
								$( "#wrapper #Success" ).delay(3000).fadeOut('slow');
								$("#main").load("getFeed.php");
							}
						}
						
					});
					$( "#NavButtons #refresh" ).click(function() {
						var feld = new Array("5");
						data = JSON.stringify(feld);
						var request = new XMLHttpRequest();
						request.open('post', 'functions.php', true);
						request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
						request.send('json='+data);
						request.onreadystatechange = function() {
							if (request.readyState==4 && request.status==200){
								$( "#wrapper #Success" ).empty();
								$( "#wrapper #Success" ).text("Successfully refreshed feeds!");
								$( "#wrapper #Success" ).show();
								$( "#wrapper #Success" ).delay(3000).fadeOut('slow');
								$("#main").load("getFeed.php");
							}
						}
					});
					$( "#NavButtons #remove" ).click(function() {
						var feld = new Array("11");
						data = JSON.stringify(feld);
						var request = new XMLHttpRequest();
						request.open('post', 'functions.php', true);
						request.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
						request.send('json='+data);
						request.onreadystatechange = function() {
							if (request.readyState==4 && request.status==200){
								$( "#wrapper #Fehler" ).empty();
								$( "#wrapper #Success" ).empty();
								if(request.responseText == 0){
									$( "#wrapper #Fehler" ).text("Nothing selected to delete!");
									$( "#wrapper #Fehler" ).show();
									$( "#wrapper #Fehler" ).delay(3000).fadeOut('slow');
								}
								else if(request.responseText == "Default"){
									$( "#wrapper #Fehler" ).text("You need this folder!");
									$( "#wrapper #Fehler" ).show();
									$( "#wrapper #Fehler" ).delay(3000).fadeOut('slow');
								}
								else if(request.responseText == "folder"){
									$( "#wrapper #Success" ).text("Successfully deleted this folder!");
									$( "#wrapper #Success" ).show();
									$( "#wrapper #Success" ).delay(3000).fadeOut('slow');
								}
								else if(request.responseText == "feed"){
									$( "#wrapper #Success" ).text("Successfully deleted this feed!");
									$( "#wrapper #Success" ).show();
									$( "#wrapper #Success" ).delay(3000).fadeOut('slow');
								}
									$("#main").load("getFeed.php");
									$("#menue #Folder").load("Folder.php");
							}
						}
					});
					</script>
				</div>
			</div>
			<div id="main">
				<?php
					include("getFeed.php");
				?>	
			</div>
	
		</div>
	</body>
	<?php
		}
	?>

</html>

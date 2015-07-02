<html>
	<head>	
	
		<meta charset="utf-8">
		<title>Feedreader</title>
		<style>
			#header{ color: black; text-align:center; padding:5px; height:10%;}
			#menue { position:absolute; top:2%;line-height:30px; background-color:#F6F6F6; height:98%; width:20%; float:left; padding:5px; overflow-y:scroll; overflow-x:hidden;}
			#main {position:absolute; height:75%; width:75%; float:left; padding:10px; left:23%; top:20%;overflow-y:scroll;}
			#Navbar {position:absolute; height:5%; width:80%; float:left: padding:10px; left:20%; top:13%; }
			#Artikel{ width:75%; margin-bottom:5px; }
			#Ausgabe{width: 100%;}
			#Buttons{width:20%; position:absolute; left:80%;}
			#NavButtons{width:20%; position:absolute; left:76%; }
			#gelb{background-color:#ff7f24;}
			#Eingabe{width:90%; margin-left:4%;}
			#Folder{width:90%; margin-top:2%; margin-left:4%;}
			#EingabeButtons{position:absolute; width:100%; margin-top:2%;}
			#Add{width:87%; margin-left:9%;}
			#ttLive, #anzFeeds{position:absolute; left:65%; width:20%; margin-left:2%; margin-bottom:5%;}
			li{margin-bottom:5%; margin-top: 5%; margin-left:5%;}
			#saveSettings{width:80%; margin-left:5%; margin-top:10%;}
			
			
			
			<!--.button{background-image:url("Klick.jpg"); margin-left:5px; background-repeat:no-repeat; margin: 0 2em; padding: .2em .5em; background-position: .5em center; padding-left: 3em; background:none transparent;}-->
			
		</style>
		<script src="https://code.jquery.com/jquery-1.10.2.js"></script>
		
		<!-- Latest compiled and minified CSS -->
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
		<!-- Latest compiled and minified JavaScript -->
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
	</head>

	<?php
		session_start();
		if(isset($_SESSION['uid'])){
	?>
	<body>
		<?php
			include 'functions.php';
			//$_SESSION['folder'] =  "Bla";
			unset($_SESSION['folder']);
			unset($_SESSION['feed']);
			updateFeeds();
			
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
			<div id="Navbar">
				<div id="NavButtons">
					<button type='button' id="allRead" class='btn btn-default' aria-label='Left Align'>
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
					<button type='button' id="refresh" class='btn btn-default' aria-label='Left Align' onclick='refresh();'>
						<span class=' glyphicon glyphicon-refresh' aria-hidden='true'></span>
					</button>
					
						<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
							<span class="glyphicon glyphicon-cog"></span>
						</button>
						<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
							<li>Unread only <input type="checkbox" id="checkRead"></input></li>
							<li>Show Feeds <input type="text" id="anzFeeds" value="<?=$_SESSION['articles_per_page'] ?>"> </input></li>
							<li>Time to live <input type="text" id="ttLive" value="<?=$_SESSION['ttl'] ?>"> </input></li>
							<li><button type='button' id="saveSettings" class='btn btn-default' aria-label='Left Align'>Save Settings</button></li>
						</ul>
				
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
								$("#main").load("getFeed.php");
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

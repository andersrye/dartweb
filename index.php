<?php
$boardmon_status = intval(shell_exec("ps aux | grep -c '[b]oardmon'"));
$dartbot_status = intval(shell_exec("ps aux | grep -c '[d]artbot'"));
$printout = shell_exec("cat /home/pi/dartbot/print-output.txt");
$ip = shell_exec("ifconfig | grep -v 'wlan0:' | grep -A 1 'wlan0' | tail -1 | cut -d ':' -f 2 | cut -d ' ' -f 1");
?>
<html>
<head>
	<title>MAD darts</title>
	<script type="text/javascript" src="jquery-2.1.0.min.js"></script>
	<script type="text/javascript" src="jquery-ui-1.10.4.custom.min.js"></script>
	<script type="text/javascript" src="dartweb.js"></script>
	<link rel="stylesheet" type="text/css" href="dartweb.css">
	<link rel="stylesheet" type="text/css" href="css/flick/jquery-ui-1.10.4.custom.min.css">
	<!-- <link rel="stylesheet" type="text/css" href="theme-brown.css"> -->

	<script type="text/javascript">

var socket = new WebSocket("ws://<?php print trim($ip); ?>:8080/dartbot");
//var socket = new WebSocket("ws://192.168.10.130:8080/dartbot");

var getGid = getUrlVars()["gid"];
var world = null;
window.addEventListener("keyup", handleKeyboard, false);

function handleKeyboard(e) {
	if(e.keyCode == "65") {
		addThrow();
		return;
	}
	if((e.keyCode == "39" || e.keyCode == "13" || e.keyCode == "32") && typeof(getGid) != 'undefined') {
		nextPlayer(getGid, null);
	}
}

socket.onmessage = function(event) {
	if(event.data == "{}"){
		document.getElementById("world").innerHTML = "No games";
		return;
	}
	if(JSON.parse(event.data) == null) {
		requestData();
		world = null;
		return;
	}
	if(world == null) {
		world = JSON.parse(event.data);
	} else {
		world = merge(world, JSON.parse(event.data));
	}
	insertInto("world", "");

	drawWorld(world);
	console.log(world);
}

socket.onopen = function(event) {
	requestData();
	setTimeout(function() {if(world == null) requestData();}, 1500);
	setTimeout(function() {if(world == null) requestData();}, 3000);
}


</script>
</head>
<body>
	<div id="world" class="world">something is wrong, check the console</div>
	Players: <input id="playerbox" type="text" name="fname"/> <input type="checkbox" id="shuffle"> Shuffle order? <button onclick='newGame()'>Start new game</button> <a href="/remote">Remote</a> <a href="/archive.php">Archive</a>
	<div id="message" class="message"><div id="message-text" class="message-text"></div></div>

	<pre>
		Board monitor: <?php
		switch ($boardmon_status) {
			case 0:
			echo "<span style='background-color: #FF0000'>OFFLINE</span>";
			break;
			case 1:
			echo "<span style='background-color: #00FF00'>ONLINE</span>";
			break;
			default:
			echo "???";
			break;
		}
		?>
		|  Dartbot: <?php
		switch ($dartbot_status) {
			case 0:
			echo "<span style='background-color: #FF0000'>OFFLINE</span>";
			break;
			case 1:
			echo "<span style='background-color: #00FF00'>ONLINE</span>";
			break;
			default:
			echo "???";
			break;
		}
		?>
	</pre>

</body>
</head>

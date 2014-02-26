<?php
$boardmon_status = intval(shell_exec("ps aux | grep -c '[b]oardmon'"));
$dartbot_status = intval(shell_exec("ps aux | grep -c '[d]artbot'"));
$printout = shell_exec("cat /home/pi/dartbot/print-output.txt");
$ip = shell_exec("ifconfig | grep -v 'wlan0:' | grep -A 1 'wlan0' | tail -1 | cut -d ':' -f 2 | cut -d ' ' -f 1");
?>
<script src="jquery-2.1.0.min.js"></script>
<script type="text/javascript">
//var socket = new WebSocket("ws://<?php print trim($ip); ?>:8080/dartbot");
var socket = new WebSocket("ws://localhost:8080/dartbot");

function div(id, className, content) {
	return "<div id=\"" + id + "\" class=\"" + className + "\">" + content + "</div>";
}

function insertInto(id, content) {
	if (content == "") {
		document.getElementById(id).innerHTML = content;
	} else {
		document.getElementById(id).innerHTML += content;
	}
}

function formatThrows(thrws) {
	temp = "";
	for (var t in thrws) {
		mlt = ""
		if (thrws[t].multiplier == 2)  mlt = "D"; else if (thrws[t].multiplier == 3) mlt = "T"
		temp += div("_", "cthrow", mlt + thrws[t].score);
	}
	for (var i = 0; i<3-thrws.length;i++) temp += div("_", "cthrow2", "");
	return temp;
}

function totalScore(thrws) {
	temp = 0;
	for (var t in thrws) {
		temp += parseInt(thrws[t].score)*parseInt(thrws[t].multiplier);
	}
	return temp;
}

function formatTime(timestamp) {
	return new Date(timestamp*1000).toLocaleString()
}

function longestPlayerHistory(game) {
	var l = 0;
	for (var p in game.players) {
		var t = game.players[p].history.length
		if(t > l) l = t;
	}
	if (l == 0) return 1;
	return l+1;
}

socket.onmessage = function(event) {
	console.log("starting")
	if(event.data == "{}"){
		document.getElementById("world").innerHTML = "No games";
		return;
	}
	var world = JSON.parse(event.data);
	console.log(world);
	insertInto("world", "");
	for (var gid in world) {
		var game = world[gid]
		insertInto("world", div(gid, "game", ""));

		insertInto(gid, div(gid+"-gid", "game-id", "Game: " + gid + ", Board: " + game.boards + ", Time: " + formatTime(game.timestamp)))

		insertInto(gid, div(gid+"-currenttotal", "currenttotal", totalScore(game.currentthrows)))

		insertInto(gid, div(gid+"-currentplayer", "cplayer", "Player:<br/>" + div("_", "cplayerplayer", game.currentplayer)))

		insertInto(gid, div(gid+"-currentthrows", "cthrows", "Throws:<br/>" + formatThrows(game.currentthrows)))
		//insertInto(gid+"-currentthrows", );

		//insertInto(gid, div(gid+"-board", "board", "boards: " + game.boards));

		//insertInto(gid, div(gid+"-timestamp", "time", "time: " + formatTime(game.timestamp)));
		var players = "";

		insertInto(gid, div(gid+"-players", "players", ""));
		insertInto(gid+"-players", div(gid+"-player"+p, "player", "plr") + div("_", "score", "Score"));
		if (longestPlayerHistory(game) > 9) 
			document.getElementById(gid).setAttribute("style",String("width: " + (longestPlayerHistory(game)*52+134) + "px")) 
		else
			document.getElementById(gid).setAttribute("style",String("width: 602px"))  
		for (var i=1;i<Math.max(longestPlayerHistory(game)+1, 10);i++) {
			insertInto(gid+"-players", div("_", "throws", "R " + i));
		}
		insertInto(gid+"-players", "<div class=\"clear\"></div>");

		for (var p in game.players) {
			var player = game.players[p];
			var score = parseInt(player.score);
			var currclass = ""
			if (game.currentplayer == p) { 
				score -= totalScore(game.currentthrows);
				currclass = " current";
			};
			insertInto(gid+"-players", div(gid+"-player"+p, 'player'.concat(currclass), p) + div("_", "score".concat(currclass), score));
			var currscore = 0
			for (var r in player.history) {
				var class2 = ""
				if (currscore+totalScore(player.history[r]) == 301) {
					class2=" win";
				} else if (currscore+totalScore(player.history[r]) > 301) {
					class2=" bust";
				} else {
					currscore += totalScore(player.history[r]);
				}
				insertInto(gid+"-players", div("_", "throws".concat(currclass+class2), totalScore(player.history[r]) + "<br>"));
			}
			//insertInto(gid+"-players", "<div class=\"clear\"></div>");

			if (game.currentplayer == p) {
				var class3 = "";
				if (score == 0) {
					class3=" win";
					document.getElementById(gid+"-currenttotal").className += " win"; 
				} else if (score < 0) {
					class3=" bust";
					document.getElementById(gid+"-currenttotal").className += " bust";
				}
				insertInto(gid+"-players", div("_", "throws currentthrows current".concat(class3), totalScore(game.currentthrows) + "<br>"));
			}
			insertInto(gid, "<div class=\"clear\"></div>");


		}
		console.log(gid);
		insertInto(gid, "<button class='left' onclick='nextPlayer(\"" + gid + "\")'>Next player</button>");
		insertInto(gid, "<button class='right' onclick='endGame(\"" + gid + "\")'>End game</button>");
		insertInto(gid, "<button class='right' onclick='deleteGame(\"" + gid + "\")'>Delete game</button>");
		insertInto(gid, "<button class='right' onclick='addThrow()'>Random throw</button>");

		insertInto(gid, "<div class=\"clear\"> </div>");




	}
}

function randInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1) + min);
}

socket.onopen = function(event) {
	socket.send('{"command" : "request"}');
}

function nextPlayer(gid) {
	socket.send("{\"command\" : \"next\", \"gid\" : \"" + gid + "\", \"payload\" : {\"timestamp\" : 1391449631516}}")
}

function deleteGame(gid) {
	if (confirm("Are you sure you want to delete the game? (" + gid + ")")) {
	  socket.send("{\"command\" : \"delete\", \"gid\" : \"" + gid + "\"}")
	}
}

function endGame(gid) {
	if (confirm("Are you sure you want to upload and end the game? (" + gid + ")")) {
	  socket.send("{\"command\" : \"end\", \"gid\" : \"" + gid + "\"}")
	}
}

function newGame() {
	players = document.getElementById("playerbox").value.split(" ");
	var temp = "";
	for(var p in players) {
		temp += "\"" + players[p] + "\"";
		if(p < players.length-1) temp += ",";
	}
	var timestamp = Math.floor(new Date().getTime()/1000); 
	socket.send("{\"command\" : \"start\", \"gid\" : \"gid" + timestamp + "\", \"payload\" : {\"timestamp\" : " + timestamp + ", \"bid\" : \"bid1\", \"rules\" : \"301\", \"players\" : [" + temp + "]}}")
}

function addThrow() {
	var mpl = 1
	var r = randInt(0, 100)
	if (r > 60) mpl = 2;
	if (r > 85) mpl = 3;
	var timestamp = Math.floor(new Date().getTime()/1000); 
	socket.send("{\"command\" : \"throw\", \"bid\" : \"bid1\", \"payload\" : {\"timestamp\" : \"" + timestamp + "\", \"score\" : " + randInt(0, 20) + ", \"multiplier\" : " + mpl + "}}")
}
</script>

<style>
	.world {
		/*width: 660px;*/
		font-family: "Trebuchet MS", Helvetica, sans-serif;
	}
	.game {
		margin-bottom: 20px;
		background-color: #eee;
		border: 2px solid #ccc;
		/*float:left;*/
	}
	.game-id {
		background-color: #99b;
	}
	.currenttotal {
		background-color: #ddd;
		float: right;
		font-size: 60pt;
		width: 130px;
		height: 100px;
		text-align: center;
	}
	.player {
		clear: both;
		background-color: #ddd;
		width: 40px;
		font-weight: bold;
	}
	.throws {
		width: 50px;
		display: inline-block;
	}
	.score {
		width: 90px;
	}
	.player, .throws, .score{
		/*display: inline-block;*/
		float: left;
		border: 2px solid #ccc;
		margin: -1px;
		height: 30px;
		text-align: center;
		opacity: 0.7;
	}
	.cplayer {
		float: left;
		background-color: #ddd;
		width: 125px;
		height: 90px;
		padding: 5px;
		/*text-align: center;*/
	}
	.cplayerplayer {

		font-size: 38pt;
	}
	.cthrows {
		float: left;
		margin-left: 7px;
		padding: 5px;
		height: 90px;
		background-color: #ddd; 

	}
	.cthrow, .cthrow2 {
		margin-right: 10px;
		margin-left: 10px;
		float: left;
		font-size: 30pt;
		width: 80px;
		height: 55px;
		text-align: center;
		vertical-align: middle;
		border: 2px solid #444;
	}
	.cthrow2 {
		border: 2px dotted #888;

	}
	.players {
		/*width: 656px;
		white-space: nowrap;
		overflow: auto;*/
	}
	.current {
		background-color: #bbc;
		opacity: 1.0;
	
	}
	.currentthrows {
		background-color: #aac;
		float: left;

	}
	.bust {
		background-color: #e67;
	}
	.win {
		background-color: #4b4;
	}
	.left {
		float: left;
	}
	.right {
		float: right;
	}
	.clear {
		clear: both;
	}
}

</style>
<!--
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
Dartbot: <?php
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
	?><br />
<?php
//echo $printout;
?>
</pre>
-->

<div id="world" class="world">something is wrong, check the console</div>
Players: <input id="playerbox" type="text" name="fname"/> <button onclick='newGame()'>Start new game</button>

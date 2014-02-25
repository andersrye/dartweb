<?php
$boardmon_status = intval(shell_exec("ps aux | grep -c '[b]oardmon'"));
$dartbot_status = intval(shell_exec("ps aux | grep -c '[d]artbot'"));
$printout = shell_exec("cat /home/pi/dartbot/print-output.txt");
$ip = shell_exec("ifconfig | grep -v 'wlan0:' | grep -A 1 'wlan0' | tail -1 | cut -d ':' -f 2 | cut -d ' ' -f 1");
?>
<script src="jquery-2.1.0.min.js"></script>
<script type="text/javascript">
var socket = new WebSocket("ws://<?php print trim($ip); ?>:8080/dartbot");
//var socket = new WebSocket("ws://localhost:8080/dartbot");

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
		temp += div("_", "throw", "Dart " + t + " - " + thrws[t].score + "x" + thrws[t].multiplier + " (" + parseInt(thrws[t].score)*parseInt(thrws[t].multiplier) + ")");
	}
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
socket.onmessage = function(event) {
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

		insertInto(gid, div(gid+"-currentplayer", "cplayer", "Current player: " + game.currentplayer))

		insertInto(gid, div(gid+"-currentthrows", "cthrows", formatThrows(game.currentthrows)))
		//insertInto(gid+"-currentthrows", );

		//insertInto(gid, div(gid+"-board", "board", "boards: " + game.boards));

		//insertInto(gid, div(gid+"-timestamp", "time", "time: " + formatTime(game.timestamp)));
		var players = "";

		insertInto(gid, div(gid+"-players", "players", ""));
		insertInto(gid+"-players", div(gid+"-player"+p, "player", "plr") + div("_", "score", "Score"));
		for (var i=1;i<11;i++) {
			insertInto(gid+"-players", div("_", "throws", "R " + i));
		}
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
				console.log(currscore+totalScore(player.history[r]));
				if (currscore+totalScore(player.history[r]) == 301) 
				{
					currclass=" win";
				}
				else if (currscore+totalScore(player.history[r]) > 301) 
				{
					currclass=" bust";
				}
				else
				{
					currscore += totalScore(player.history[r]);
				}
				insertInto(gid+"-players", div("_", "throws".concat(currclass), totalScore(player.history[r]) + "<br>"));
			}
			if (game.currentplayer == p) {
				insertInto(gid+"-players", div("_", "throws currentthrows", totalScore(game.currentthrows) + "<br>"));
			}
			insertInto(gid, "<div class=\"clear\"> </div>");


		}
		console.log(gid);
		insertInto(gid, "<button onclick='nextPlayer(\"" + gid + "\")'>Next player</button>");
		insertInto(gid, "<button onclick='deleteGame(\"" + gid + "\")'>Delete game</button>");
		insertInto(gid, "<div class=\"clear\"> </div>");




	}
}

socket.onopen = function(event) {
	socket.send('{"command" : "request"}');
}

function nextPlayer(gid) {
	socket.send("{\"command\" : \"next\", \"gid\" : \"" + gid + "\", \"payload\" : {\"timestamp\" : \"1391449631516\"}}")
}

function deleteGame(gid) {
	if (confirm("Are you sure you want to delete the game? (" + gid + ")")) {
	  socket.send("{\"command\" : \"delete\", \"gid\" : \"" + gid + "\"}")
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
	socket.send("{\"command\" : \"start\", \"gid\" : \"gid" + timestamp + "\", \"payload\" : {\"timestamp\" : \"" + timestamp + "\", \"bid\" : \"bid1\", \"rules\" : \"301\", \"players\" : [" + temp + "]}}")

}
</script>

<style>
	.world {
		width: 660px;
		font-family: "Trebuchet MS", Helvetica, sans-serif;
	}
	.game {
		margin-bottom: 20px;
		background-color: #eee;
		border: 2px solid #ccc;
		//float:left;
	}
	.game-id {
		background-color: #99b;
	}
	.currenttotal {
		background-color: #ddd;
		float: right;
		font-size: 60pt;
		width: 130px;
		text-align: center;
	}
	.player {
		clear: both;
		background-color: #ddd;
		float: left;
		width: 40px;
	}
	.throws {
		float: left;
		width: 50px;
	}
	.score {
		float: left;
		width: 90px;
	}
	.player, .throws, .score{
		border: 2px solid #ccc;
		margin: -1px;
		height: 30px;
		text-align: center;
	}
	.current {
		background-color: #bbc;
	}
	.currentthrows {
		background-color: #aac;

	}
	.bust {
		background-color: red;
	}
	.win {
		background-color: green;
	}
	.clear {
		clear: both;
	}
}

</style>

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
<br />
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


<div id="world" class="world">something is wrong, check the console</div>
Players: <input id="playerbox" type="text" name="fname"/> <button onclick='newGame()'>Start new game</button>

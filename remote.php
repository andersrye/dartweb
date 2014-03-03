<?php
	$ip = shell_exec("ifconfig | grep -v 'wlan0:' | grep -A 1 'wlan0' | tail -1 | cut -d ':' -f 2 | cut -d ' ' -f 1");
?>

<script>
var socket = new WebSocket("ws://<?php print trim($ip); ?>:8080/dartbot");

function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}
var gid = getUrlVars()["gid"];
var bid = getUrlVars()["bid"];

function nextPlayer() {
	if(typeof(gid) != 'undefined') {
		console.log("sending");
		socket.send("{\"command\" : \"next\", \"gid\" : \"" + gid + "\", \"payload\" : {\"timestamp\" : 1391449631516}}")
	} else {
		if(typeof(bid) == 'undefined') bid = "bid1";
		socket.send("{\"command\" : \"next\", \"bid\" : \"" + bid + "\", \"payload\" : {\"timestamp\" : 1391449631516}}")
	}
}


</script>

<style>
.remoteButton {
	height: 100%;
	width: 100%;
	font-size: 100%;
}
</style>

<button class="remoteButton" onclick='nextPlayer()'>NEXT</button>

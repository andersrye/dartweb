<?php
	$ip = shell_exec("ifconfig | grep -v 'wlan0:' | grep -A 1 'wlan0' | tail -1 | cut -d ':' -f 2 | cut -d ' ' -f 1");
?>
<script type="text/javascript" src="jquery-2.1.0.min.js"></script>
<script type="text/javascript" src="dartweb.js"></script>

<script>
var socket = new WebSocket("ws://<?php print trim($ip); ?>:8080/dartbot");
//var socket = new WebSocket("ws://localhost:8080/dartbot");

socket.onopen = function(event) {
	socket.send('{"command" : "request", "game" : "archive"}');
}

socket.onmessage = function(event) {
	var list = JSON.parse(event.data).sort().reverse();
	for (var l in list) {
		console.log(list[l]);
		var gid = list[l].split('.')[0];
		insertInto("list", "<a href='/?gid=" + gid + "'>" + gid + "</a><br/>");
	}
}
</script>

<style>
</style>
<div id="list"></div>

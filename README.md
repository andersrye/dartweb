#dartweb

Web-client for dartbot

## Running a client with a local dartbot

* Rename index.php to index.html
* Change the websocket ip to "localhost":
		
		//var socket = new WebSocket("ws://<?php print trim($ip); ?>:8080/dartbot");
		var socket = new WebSocket("ws://localhost:8080/dartbot");
* Open index.html in a browser.
* Ignore the broken PHP.
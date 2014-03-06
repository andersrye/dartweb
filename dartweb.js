function getUrlVars() {
    var vars = {};
    var parts = window.location.href.replace(/[?&]+([^=&]+)=([^&]*)/gi, function(m,key,value) {
        vars[key] = value;
    });
    return vars;
}

function shuffle(o){
    for(var j, x, i = o.length; i; j = Math.floor(Math.random() * i), x = o[--i], o[i] = o[j], o[j] = x);
    return o;
};

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

function removeNull(array) {
	var temp = new Array();
	for (e in array) {
		if (array[e] != null) {
			temp.push(array[e]);
		}
	}
	return temp;
}

var merge = function() {
    var destination = {},
        sources = [].slice.call( arguments, 0 );
    sources.forEach(function( source ) {
        var prop;
        for ( prop in source ) {
            if ( prop in destination && Array.isArray( destination[ prop ] ) ) {
                // Concat Arrays
                if(source[prop] == null) {
                	destination[prop] = [];
                } else {
                	destination[ prop ] = removeNull(destination[ prop ].concat( source[ prop ] ));
                }
                
            } else if ( prop in destination && typeof destination[ prop ] === "object" ) {
                // Merge Objects
                destination[ prop ] = merge( destination[ prop ], source[ prop ] );
                
            } else {
                // Set new values
                destination[ prop ] = source[ prop ];
                
            }
        }
    });
    return destination;
};

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

//STATS:

function countScore(history, score) {
	var total = 0;
	for(var r in history) {
		for (var t in history[r]) {
			if(history[r][t].score == score) total += 1;
		}
	}
	return total;
}

function countMult(history, mult) {
	var total = 0;
	for(var r in history) {
		for (var t in history[r]) {
			if(history[r][t].multiplier == mult) total += 1;
		}
	}
	return total;
}

function bestFinish(players) {
	var temp = ["", 0];
	for(var p in players) {
		if(players[p].score == 0) {
			var fnsh = totalScore(players[p].history[players[p].history.length-1]);
			if (fnsh > temp[1]) {
				temp[0] = p;
				temp[1] = fnsh;
			}
		}
	}
	return temp;
}
function bestRound(players) {
	var temp = ["", 0];
	for(var p in players) {
		for(var round in players[p].history) {
			var rsc = totalScore(players[p].history[round])
			if ( rsc > temp[1]) {
				temp[0] = p;
				temp[1] = rsc;
			}
		}
	}
	return temp;
}

function finalStandings(game) {
	var temp = new Array();
	var rest = new Array();
	for (var p in game.players) {
		var pos = game.players[p].position
		if(pos != null) {
			temp[pos-1] = [p,0];
		} else {
			rest.push([p,game.players[p].score])
		}
	}
	return temp.concat(rest.sort(function(a, b){return a[1]-b[1];})); 
}

function formatStats(game) {
	var temp = "Results: <br/>";
	standings = finalStandings(game);
	for (var i = 0; i<standings.length; i++) {
		temp += (i+1) + ". " + standings[i][0] + "<br/>";
	}
	bestround = bestRound(game.players);
	bestfinish = bestFinish(game.players);
	temp += "<br/>Best round: " +  bestround[0] + " (" + bestround[1] + ")"
	temp += "<br/>Best finish: " +  bestfinish[0] + " (" + bestfinish[1] + ")"
	return temp
}

function drawWorld(world) {
	console.log(world)
	for (var gid in world) {
		if (typeof(getGid) != 'undefined' && getGid != gid) continue;
		var game = world[gid];
		insertInto("world", div(gid, "game", ""));

		insertInto(gid, div(gid+"-gid", "game-id", "&nbsp; Game: <a href='?gid=" + gid + "'>" + gid + "</a>, Board: " + game.boards + ", Time: " + formatTime(game.timestamp)))

		if(game.currentplayer != null) {
			insertInto(gid, div(gid+"-gameinfo", "gameinfo", ""));
			insertInto(gid+"-gameinfo", div(gid+"-currentplayer", "cplayer", "Player:<br/>" + div("_", "cplayerplayer", game.currentplayer)))
			insertInto(gid+"-gameinfo", div(gid+"-currentthrows", "cthrows", "Throws:<br/>" + formatThrows(game.currentthrows)))
			insertInto(gid+"-gameinfo", div(gid+"-remaining", "remaining", div("_", "remainingtitle", "Remaining: ") + div(gid+"-remainingscore", "remainingscore", game.players[game.currentplayer].score - totalScore(game.currentthrows))))
		}
		insertInto(gid, div(gid+"-players", "players", ""));
		insertInto(gid, div(gid+"-scores", "scores", ""));
		insertInto(gid+"-players", div(gid+"-player"+p, "player", "plr") + div("_", "score", "Score") + "<div class=\"clear\"></div>");

		for (var i=1;i<Math.max(longestPlayerHistory(game)+(game.currentplayer != null && game.players[game.currentplayer].history.length+1 == longestPlayerHistory(game) ? 1 : 0), 10);i++) {
			insertInto(gid+"-scores", div("_", "throws", "R " + i));
		}
		insertInto(gid+"-scores", "<div class=\"clear\"></div>");

		for (var i in game.playerorder) {
			var p = game.playerorder[i]
			var player = game.players[p];
			console.log(player);
			var score = parseInt(player.score);
			var currclass = ""
			if (game.currentplayer == p) { 
				score -= totalScore(game.currentthrows);
				currclass = " current";
			};
			insertInto(gid+"-players", div(gid+"-player"+p, 'player'.concat(currclass), "<a href='#"+gid+"' onclick='nextPlayer(\"" + gid + "\", \"" + p + "\")'>" + p + "</a>") + div("_", "score".concat(currclass), score));
			var currscore = 0
			insertInto(gid+"-scores", div(gid+"-history-"+p, "history", ""));
			if(player.history.length == 0 && p != game.currentplayer) {
				insertInto(gid+"-history-"+p, div("_", "filler", ""));
			}
			for (var r in player.history) {
				var class2 = ""
				if (currscore+totalScore(player.history[r]) == 301) {
					class2=" win";
				} else if (currscore+totalScore(player.history[r]) > 301) {
					class2=" bust";
				} else {
					currscore += totalScore(player.history[r]);
				}
				insertInto(gid+"-history-"+p, div("_", "throws".concat(currclass+class2), totalScore(player.history[r]) + "<br>"));
			}
			insertInto(gid+"-scores", "<div class=\"clear\"></div>");
			

			if (game.currentplayer == p) {
				var class3 = "";
				if (score == 0) {
					class3=" win";
					document.getElementById(gid+"-remaining").className += " win"; 
					document.getElementById(gid+"-remainingscore").innerHTML = "WIN"; 
				} else if (score < 0) {
					class3=" bust";
					document.getElementById(gid+"-remaining").className += " bust";
					document.getElementById(gid+"-remainingscore").innerHTML = "BUST"; 
				}
				insertInto(gid+"-history-"+p, div(gid+"-currentthrow", "throws currentthrows current".concat(class3), totalScore(game.currentthrows) + "<br>"));
				
			}
			insertInto(gid, "<div class=\"clear\"></div>");


		}
		insertInto(gid, "<button class='right' onclick='endGame(\"" + gid + "\")'>End game</button>");
		insertInto(gid, "<button class='right' onclick='deleteGame(\"" + gid + "\")'>Delete game</button>");
		if(game.currentplayer != null) {
			insertInto(gid, "<button class='left' onclick='nextPlayer(\"" + gid + "\", null)'>Next player</button>");
			insertInto(gid, "<button class='right' onclick='addThrow()'>Random throw</button>");
			insertInto(gid, "<button class='right' onclick='undo()'>Undo</button>");
		}
		insertInto(gid, "<div class=\"clear\"> </div>");
		if(game.currentplayer == null && typeof(getGid) != 'undefined') {
			insertInto("world", div("stats", "stats", "<pre>" + formatStats(game) + "</pre>"))
		}
		console.log(gid+"-scores");
	}
	var scoreelements = document.getElementsByClassName("scores");
	for (var e in  scoreelements) {
		scoreelements[e].scrollLeft = scoreelements[e].scrollWidth;	
	}
}

function randInt(min, max) {
  return Math.floor(Math.random() * (max - min + 1) + min);
}

function requestData() {
	var gameid = "all";
	if(typeof(getGid) != 'undefined') {
		gameid = getGid;
	}
	socket.send('{"command" : "request", "game" : "' + gameid + '", "update" : "diff"}');
}

function nextPlayer(gid, plr) {
	if (plr != null ) plr = "\"" + plr + "\"";
	socket.send('{"command" : "next", "gid" : "' + gid + '", "payload" : {"timestamp" : 1391449631516, "player" : ' + plr + ' }}')	
}

function deleteGame(gid) {
	if (confirm("Are you sure you want to delete the game? (" + gid + ")")) {
	  socket.send('{"command" : "delete", "gid" : "' + gid + '"}')
	}
}

function endGame(gid) {
	if (confirm("Are you sure you want to upload and end the game? (" + gid + ")")) {
	  socket.send('{"command" : "end", "gid" : "' + gid + '"}')
	}
}

function undo() {
	  socket.send('{"command" : "undo"}')
}

function newGame() {
	players = document.getElementById("playerbox").value.split(" ");
	if(document.getElementById("shuffle").checked == true) {
		players = shuffle(players);
	}
	var temp = "";
	for(var p in players) {
		temp += "\"" + players[p] + "\"";
		if(p < players.length-1) temp += ",";
	}
	var timestamp = Math.floor(new Date().getTime()/1000); 
	socket.send('{"command" : "start", "gid" : "gid' + timestamp + '", "payload" : {"timestamp" : ' + timestamp + ', "bid" : "bid1", "rules" : "301", "players" : [' + temp + ']}}')
}

function addThrow() {
	var mpl = 1
	var r = randInt(0, 100)
	if (r > 60) mpl = 2;
	if (r > 85) mpl = 3;
	var timestamp = Math.floor(new Date().getTime()/1000); 
	socket.send('{"command" : "throw", "bid" : "bid1", "payload" : {"timestamp" : ' + timestamp + ', "score" : ' + randInt(0, 20) + ', "multiplier" : ' + mpl + '}}')
}

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>WebSocket LED Controls</title>
	<link rel="stylesheet" type="text/css" href="//fonts.googleapis.com/css?family=Open+Sans" />
	<style>
		
		body {
			font-family: "Open Sans";
			font-size: 24px;
			font-style: normal;
			font-variant: normal;
			font-weight: 500;
			line-height: 26.4px;
		}
		
		#slidecontainer {
			width: 100%;
		}

		.slider {
			display: inline-block;
			writing-mode: bt-lr;
			-webkit-appearance: slider-vertical;
			width: 16.666666666%;
			height: 500px;
			background: #d3d3d3;
			outline: none;
			opacity: 0.7;
			-webkit-transition: .2s;
			transition: opacity .2s;
			margin: 0px 0px 50px 0px;
		}

		.slider:hover {
			opacity: 1;
		}

		.slider::-webkit-slider-thumb {
			-webkit-appearance: none;
			appearance: none;
			width: 100px;
			height: 100px;
			background: #4CAF50;
			cursor: pointer;
			background-image: url("/images/slider-btn.png");
		}

		.slider::-moz-range-thumb {
			width: 25px;
			height: 25px;
			background: #4CAF50;
			cursor: pointer;
		}
		
		button#show_hide {
			font-size: 50px;
			padding: 25px;
			width: 100%;
		}
		
		h1, h2, button#show_hide {
			text-align: center;
			font-size: 45px;
		}
		
		div#output {
			font-family: Consolas, Andale Mono, Lucida Console, Lucida Sans Typewriter, Monaco, Courier New, monospace;
			font-size: 40px;
		}
		
		label {
			display: inline-block;
			width: 16.666666666%;
			text-align: center;
			margin-bottom: 10px;
		}
		
		#speakers {
			text-align: center;
			margin-bottom: 20px;
		}
		#speakers > * {
			display: inline-block;
		}
		#speakers > div:first-child {
			height: 60px;
			vertical-align: middle;
		}
		
		.onoffswitch {
			position: relative; width: 120px;
			-webkit-user-select:none; -moz-user-select:none; -ms-user-select: none;
		}
		.onoffswitch-checkbox {
			display: none;
		}
		.onoffswitch-label {
			display: block; overflow: hidden; cursor: pointer;
			border: 2px solid #999999; border-radius: 20px;
			
			width: auto;
    		text-align: left;
    		margin-bottom: auto;
		}
		.onoffswitch-inner {
			display: block; width: 200%; margin-left: -100%;
			transition: margin 0.3s ease-in 0s;
		}
		.onoffswitch-inner:before, .onoffswitch-inner:after {
			display: block; float: left; width: 50%; height: 40px; padding: 0; line-height: 40px;
			font-size: 28px; color: white; font-family: Trebuchet, Arial, sans-serif; font-weight: bold;
			box-sizing: border-box;
		}
		.onoffswitch-inner:before {
			content: "ON";
			padding-left: 10px;
			background-color: #51C234; color: #FFFFFF;
		}
		.onoffswitch-inner:after {
			content: "OFF";
			padding-right: 10px;
			background-color: #EEEEEE; color: #999999;
			text-align: right;
		}
		.onoffswitch-switch {
			display: block; width: 30px; margin: 5px;
			background: #FFFFFF;
			position: absolute; top: 0; bottom: 0;
			right: 76px;
			border: 2px solid #999999; border-radius: 20px;
			transition: all 0.3s ease-in 0s; 
		}
		.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-inner {
			margin-left: 0;
		}
		.onoffswitch-checkbox:checked + .onoffswitch-label .onoffswitch-switch {
			right: 0px; 
		}
	</style>
</head>
<body>
	<h1 id="title">LED Controller <span id="errors">(x)</span></h1>

	<div id="speakers">
   		<div>Speakers - </div>
   		<div id="toggle-speakers" class="onoffswitch">
    		<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="toggle-speakers-switch">
    		<label class="onoffswitch-label" for="toggle-speakers-switch">
				<span class="onoffswitch-inner"></span>
				<span class="onoffswitch-switch"></span>
			</label>
		</div>
	</div>
	
	<div>
	<label for="range_1">Top</label><!--
	--><label for="range_2">Keyboard</label><!--
	--><label for="range_3">Shelves</label><!--
	--><label for="range_4">HiFi Left</label><!--
	--><label for="range_5">HiFi Right</label><!--
	--><label for="range_6">Soft</label>
	
	<input type="range" min="0" max="100" value="0" class="slider" id="range_1"><!--
	--><input type="range" min="0" max="100" value="0" class="slider" id="range_2"><!--
	--><input type="range" min="0" max="100" value="0" class="slider" id="range_3"><!--
	--><input type="range" min="0" max="100" value="0" class="slider" id="range_4"><!--
	--><input type="range" min="0" max="100" value="0" class="slider" id="range_5"><!--
	--><input type="range" min="0" max="100" value="0" class="slider" id="range_6">
	</div>
		
<h2>WebSocket Output</h2>
	<button id="show_hide" onClick="show_hide();">Show</button>
	<div id="output" style="display: none;"></div>
</body>
<footer>
	<script src="/js/ReconnectingWebsocket.js"></script>
	<script language="javascript" type="text/javascript">
		var ws_url = "wss://" + window.location.hostname + ":8889";
		var output;
		var errors = 0;
		function init() {
			output = document.getElementById("output");
			init_websocket();
		}
		function init_websocket() {
			websocket = new ReconnectingWebSocket(ws_url, "control");
			websocket.onopen = function(evt) {
				onOpen(evt)
			};
			websocket.onclose = function(evt) {
				onClose(evt)
			};
			websocket.onmessage = function(evt) {
				onMessage(evt)
			};
			websocket.onerror = function(evt) {
				onError(evt)
			};
		}
		function onOpen(evt) {
			document.getElementById("title").style.color = "#00cd00";
			writeToScreen("CONNECTED");
			doSend("Hello");
		}
		function onClose(evt) {
			document.getElementById("title").style.color = "red";
			writeToScreen("DISCONNECTED");
		}
		function onMessage(evt) {
			data = JSON.parse(evt.data);
			if (typeof data == 'object') {
				if (data.key == 'init') {
					document.getElementById("range_1").value = data.val[0];
					document.getElementById("range_2").value = data.val[1];
					document.getElementById("range_3").value = data.val[2];
					document.getElementById("range_4").value = data.val[3];
					document.getElementById("range_5").value = data.val[4];
					document.getElementById("range_6").value = data.val[5];
					document.getElementById("toggle-speakers-switch").checked = data.val[5] == 1 ? true : false;
				} else if (data.key == 'changed') {
					document.getElementById("range_" + (data.val[0] + 1)).value = data.val[1];
				}
			}
			writeToScreen('<span style="color: blue;">RESPONSE: ' + evt.data+'</span>');
			//websocket.close();
		}
		function onError(evt) {
			writeToScreen('<span style="color: red;">ERROR:</span> ' + evt.data);
			document.getElementById("errors").textContent = '(' + ++errors + ')';
			console.log(evt);
		}
		function doSend(message) {
			writeToScreen("SENT: " + JSON.stringify(message));
			websocket.send(JSON.stringify(message));
		}
		function writeToScreen(message) {
			var pre = document.createElement("p");
			pre.style.wordWrap = "break-word";
			pre.innerHTML = message;
			output.insertAdjacentElement('afterbegin', pre);
		}
		window.addEventListener("load", init, false);
		
		var range_1 = document.getElementById("range_1");
		var range_2 = document.getElementById("range_2");
		var range_3 = document.getElementById("range_3");
		var range_4 = document.getElementById("range_4");
		var range_5 = document.getElementById("range_5");
		var range_6 = document.getElementById("range_6");
		
		range_1.oninput = function() {
			doSend({key: 0, val: parseInt(this.value)});
		}
		range_2.oninput = function() {
			doSend({key: 1, val: parseInt(this.value)});
		}
		range_3.oninput = function() {
			doSend({key: 2, val: parseInt(this.value)});
		}
		range_4.oninput = function() {
			doSend({key: 3, val: parseInt(this.value)});
		}
		range_5.oninput = function() {
			doSend({key: 4, val: parseInt(this.value)});
		}
		range_6.oninput = function() {
			doSend({key: 5, val: parseInt(this.value)});
		}
		
		var speakers_6 = document.getElementById("toggle-speakers-switch").onclick = function() {
			if (this.checked) {
				console.log('Switching speakers on');
				doSend({key: 6, val: 1});
			} else {
				console.log('Switching speakers off');
				doSend({key: 6, val: 0});
			}
		}
		
		function show_hide() {
			if (document.getElementById("show_hide").textContent == "Show") {
				document.getElementById("show_hide").textContent = "Hide";
				document.getElementById("output").style.display = "block";
			} else {
				document.getElementById("show_hide").textContent = "Show";
				document.getElementById("output").style.display = "none";
			}
			
		}

	</script>
</footer>
</html>
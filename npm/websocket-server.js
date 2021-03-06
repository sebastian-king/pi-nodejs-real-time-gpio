#!/usr/bin/env node

var WebSocketServer = require('websocket').server;
//var http = require('http');
var Gpio = require('pigpio').Gpio;

var https = require('https');
var fs = require('fs');

var config = fs.readFileSync('/var/www/h/assets/config.sh', 'utf8');

var domain = config.toString().match(/domain="(.+)";/)[1];

var led_strips = [];
var clients = [];
var connectionIDCounter = 0;

led_strips.push(new Gpio(6, {mode: Gpio.OUTPUT})); //top_led_strip
led_strips.push(new Gpio(21, {mode: Gpio.OUTPUT})); //keyboard_led_strip
led_strips.push(new Gpio(20, {mode: Gpio.OUTPUT})); //shelves_led_strip
led_strips.push(new Gpio(19, {mode: Gpio.OUTPUT})); //hifi_left_led_strip
led_strips.push(new Gpio(26, {mode: Gpio.OUTPUT})); //hifi_right_led_strip
led_strips.push(new Gpio(12, {mode: Gpio.OUTPUT})); //soft_top_led_strip

led_strips.push(new Gpio(9, {mode: Gpio.OUTPUT})); //w
led_strips.push(new Gpio(13, {mode: Gpio.OUTPUT})); //r
led_strips.push(new Gpio(11, {mode: Gpio.OUTPUT})); //g
led_strips.push(new Gpio(5, {mode: Gpio.OUTPUT})); //b
speakers = new Gpio(22, {mode: Gpio.OUTPUT});

for (i = 0; i < led_strips.length; i++) {
	led_strips[i].pwmWrite(0);
}

var server = https.createServer(
	{
	      key: fs.readFileSync( '/root/.acme.sh/' + domain + '/' + domain + '.key' ),
	      cert: fs.readFileSync( '/root/.acme.sh/' + domain + '/fullchain.cer' )
	},
	function(request, response) {
	    console.log((new Date()) + ' Received request for ' + request.url);
	    response.writeHead(404);
	    response.end();
	}
);

server.listen(8889, function() {
    console.log((new Date()) + ' Server is listening on port 8889'); // pigpiod uses port 8888
});

wsServer = new WebSocketServer({
    httpServer: server,
    autoAcceptConnections: false
});

function originIsAllowed(origin) {
  return true;
}

function updateClients(key, val, connection) {
	clients.forEach(function(client) {
		if (client != connection) {
			client.send(JSON.stringify({key: 'changed', val: [key, val]}));
		}
	});
}

wsServer.on('request', function(request) {
	if (!originIsAllowed(request.origin)) {
		request.reject();
		console.log((new Date()) + ' Connection from origin ' + request.origin + ' rejected.');
		return;
	}

	var connection = request.accept("control", request.origin);
	connection.id = connectionIDCounter++;
	clients.push(connection);

	console.log((new Date()) + ' Connection accepted.');

	connection.sendUTF(JSON.stringify({
		key: 'init',
		val: [
			Math.round(led_strips[0].getPwmDutyCycle() / 255 * 100),
			Math.round(led_strips[1].getPwmDutyCycle() / 255 * 100),
			Math.round(led_strips[2].getPwmDutyCycle() / 255 * 100),
			Math.round(led_strips[3].getPwmDutyCycle() / 255 * 100),
			Math.round(led_strips[4].getPwmDutyCycle() / 255 * 100),
			Math.round(led_strips[5].getPwmDutyCycle() / 255 * 100),
			Math.round(led_strips[6].getPwmDutyCycle() / 255 * 100),
			Math.round(led_strips[7].getPwmDutyCycle() / 255 * 100),
			Math.round(led_strips[8].getPwmDutyCycle() / 255 * 100),
			Math.round(led_strips[9].getPwmDutyCycle() / 255 * 100),
			speakers.digitalRead(),
			parseInt(fs.readFileSync('/var/www/h/assets/cooling/temperature_threshold', 'utf8'))
		]
	}));

	connection.on('message', function(message) {
       		if (message.type === 'utf8') {
			var received = JSON.parse(message.utf8Data);

			if (received == "Hello") {
				connection.sendUTF(JSON.stringify("Hiya"));
			}

			//console.log('Received Message ['+request.protocol+']: ' + received);

			if (typeof received == 'object') {
				if (typeof received.key == 'number') {
					if (received.key >= 0 && received.key <= 9) {
						if (typeof received.val == 'number') {
							if (received.val >= 0 & received.val <= 100) {
								var val = Math.round(255 * received.val / 100);
								led_strips[received.key].pwmWrite(val);
							}
						}
					} else if (received.key == 10) {
						speakers.digitalWrite(received.val);
					} else if (received.key == 11) {
						if (received.val >= 40 && received.val <= 80) {
							fs.writeFile('/var/www/h/assets/cooling/temperature_threshold', received.val);
						} else {
							return;
						}
					}
					//clients.forEach(function(client) {
					//	if (client != connection) {
					//		client.send(JSON.stringify({key: 'changed', val: [received.key, received.val]}));
					//	}
					//});
					updateClients(received.key, received.val, connection);
				} else if (typeof received.key == 'object') {
					if (typeof received.val == 'number') {
						for (i in received.key) {
							if (i >= 0 && i <= 9) {
								console.log("setting ", i, " to ", received.val);
								led_strips[i].pwmWrite(received.val);
							}
						}
						updateClients(received.key, received.val, connection);
					}
				}
			}

			//connection.sendUTF(JSON.stringify("MESSAGE: " + received));
			//console.log('Reponse sent: ' + "MESSAGE: " + typeof received);

        	} else if (message.type === 'binary') {
        	    console.log('Received Binary Message of ' + message.binaryData.length + ' bytes');
        	    connection.sendBytes(message.binaryData);
        	}
	});

	connection.on('close', function(reasonCode, description) {
		clients.splice(clients.indexOf(connection), 1);
        	console.log((new Date()) + ' Peer ' + connection.remoteAddress + ' disconnected.');
	});
});

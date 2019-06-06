#!/usr/bin/python

import time
import json
from websocket import create_connection
ws = create_connection("wss://h.helpfulseb.com:8889", subprotocols=["control"]);

on="{\"key\": [0,1,2,3,4,5,6,7,8,9], \"val\": 255}"
off="{\"key\": [0,1,2,3,4,5,6,7,8,9], \"val\": 0}"


while True:
	ws.send(off);
	time.sleep(0.25)
	ws.send(on);
	time.sleep(0.25)


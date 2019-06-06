#!/usr/bin/python

import time
import json
from websocket import create_connection
ws = create_connection("wss://h.helpfulseb.com:8889", subprotocols=["control"]);

on_lights="{\"key\": [0,1,2,3,4,5,6,7,8,9], \"val\": 255}"
off_lights="{\"key\": [0,1,2,3,4,5,6,7,8,9], \"val\": 0}"

on_others="{\"key\": 10, \"val\": 0}"
off_others="{\"key\": 10, \"val\": 1}"

on_red="{\"key\": 2, \"val\": 100}"

#ws.send(off_lights);
#ws.send(off_others);
ws.send(on_red);

time.sleep(2)

ws.send(off_lights);

#ws.send(on_lights);
#ws.send(on_others);

#!/usr/bin/python

import time
import pigpio
import subprocess
import re
import os

pi = pigpio.pi()

while True:
	temp = subprocess.check_output(["/opt/vc/bin/vcgencmd", "measure_temp"]);
	m = re.match("temp=(\d+\.?\d*)'C", temp);
	temp = m.groups()[0]

	with open(os.path.dirname(os.path.realpath(__file__)) + '/temperature_threshold', 'r') as f:
		threshold = f.read()

	print "Temperature is: %s" % (temp)
	print "Temperature threshold is: %s" % (threshold)
	if float(temp) > int(threshold):
		print "Over %s! Cooling!" % (threshold)
		pi.write(27, 1)
		time.sleep(30)
	else:
		print "Nice and cool."
		pi.write(27, 0)
	time.sleep(10)

#!/usr/bin/python

import time
import pigpio
import subprocess
import re

pi = pigpio.pi()

while True:
	temp = subprocess.check_output(["/opt/vc/bin/vcgencmd", "measure_temp"]);
	m = re.match("temp=(\d+\.?\d*)'C", temp);
	temp = m.groups()[0]
	print "Temperature is: %s" % (temp)
	if float(temp) > 40.0:
		print "Over 40! Cooling!"
		pi.write(27, 1)
		time.sleep(30)
	else:
		print "Nice and cool."
		pi.write(27, 0)
	time.sleep(10)

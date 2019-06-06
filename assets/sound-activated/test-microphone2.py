import time
import sys
import pigpio
import Adafruit_MCP3008

CLK  = 23
MISO = 24
MOSI = 25
CS   = 18
mcp = Adafruit_MCP3008.MCP3008(clk=CLK, cs=CS, miso=MISO, mosi=MOSI)

#SPI_PORT   = 0
#SPI_DEVICE = 0
#mcp = Adafruit_MCP3008.MCP3008(spi=SPI.SpiDev(SPI_PORT, SPI_DEVICE))

#pi=pigpio.pi()

#while True:
#	val = mcp.read_adc(7)
#
#	n = val;
#	if n < 0:
#		n = 0
#	if n > 255:
#		n = 255
#
#	for i in range(n):
#		sys.stdout.write('#')
#		sys.stdout.flush()
#	#pi.set_PWM_dutycycle(12, n)
#	print val
#	time.sleep(0.5)

sampleWindow = 50; # sample window width in mS (50 mS = 20Hz)
sample = "";

def millis():
        return int(round(time.time() * 1000))

while True:
	startMillis = millis(); # Start of sample window
	peakToPeak = 0; # peak-to-peak level

	signalMax = 0;
	signalMin = 1024;

	#collect data for 50 mS
	while (millis() - startMillis < sampleWindow):
		sample = mcp.read_adc(7);
		if (sample < 1024): # toss out spurious readings
			if (sample > signalMax):
				signalMax = sample; # save just the max levels
			elif (sample < signalMin):
				signalMin = sample; # save just the min levels

	peakToPeak = signalMax - signalMin; # max - min = peak-peak amplitude
	#volts = peakToPeak / 1024; # convert to volts
	percentage = float(peakToPeak) / 1024;

	print percentage;
	#print (100 - (percentage * 100));
	#print "%f %%\n" % percentage*100;
	#pi.set_PWM_dutycycle(12, 255 * (1 - (percentage)));

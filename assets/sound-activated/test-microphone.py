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

pi=pigpio.pi()

while True:
	val = mcp.read_adc(7)

	n = (val - 450)/2;
	if n < 0:
		n = 0
	if n > 255:
		n = 255

	for i in range(n):
		sys.stdout.write('#')
		sys.stdout.flush()
	pi.set_PWM_dutycycle(12, n)
	print n
	time.sleep(0.01)

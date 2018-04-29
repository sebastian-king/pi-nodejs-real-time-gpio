import time
import sys
import pigpio

pi = pigpio.pi()

pi.set_PWM_dutycycle(12, 255)

#!/usr/bin/env python

"""
This module get the temperature of the cpu and...

"""
import requests
import os
import time
import datetime
import subprocess
import re  
import json
import threading
import sys
import socket


# to add a record : http://192.168.0.147/monitor/getEvent.php?eventFct=add&time="2018-01-16"&host=myHost&text="my text"&type="my type"

datetime_str = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
hostName = socket.gethostname()


ostemp = os.popen('sensors').readline()
temp = (ostemp.replace("temp=", "").replace("'C\n", ""))






myData = {
	'eventFct' : 'add', 
	'time' : datetime_str,
	'host' : hostName,
	'text' : '55.99', 
	'type' : 'temperature'
}


#	log_txt':'detected ! (Raspberry)',\
#			'log_temp' : temp, 'add' : 1}

print (myData)
r = requests.post("http://localhost/monitor/getEvent.php", data=myData)
print (r.content)





"""
#phpServer="http://toto240325.eu.pn/test/"
#phpServer="http://ec2-54-201-28-143.us-west-2.compute.amazonaws.com/loki/"
phpServer="http://52.26.69.146/loki/"

print "Starting (to check the output, do this : "
print "	sudo tail -f ~/logs/pirtest.log"

sys.stdout = open('/home/pi/logs/pirtest.log', 'w+')
sys.stderr = open('/home/pi/logs/pirtest.log', 'w+')

k = 0
requestTimeout = 1000 	# timeout for requests
video_duration = 6		# length of the captured video

# prev time and delay of detector
prev_time_detector=datetime.datetime.now()
# time to wait after the detector has been triggered before next detection is considered valid
delay_detector=datetime.timedelta(seconds=5*60)
# time to sleep before checking the detector again
detector_sleep_time = 0.1

# send info about temp every x secs
prev_time_temp=datetime.datetime.now()
delay_temp=datetime.timedelta(seconds=5*60)

def dweet(rqsString):			
	rqs = requests.get(rqsString)
	#print(rqs)
	#print rqs.status_code
	#print rqs.headers
	#print rqs.content
	print "--> dweeted " + rqsString
	print "    finished on " + time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())

print "after def dweet"
	
def post_file(filename, datetime_str, filetype, PIR_detection, ultrasonic_detection):
	url = phpServer+'upload_file.php'
	size = os.path.getsize(filename)
	print "post_file : datetime str : " + datetime_str + " filename : " + filename + " size: " + str(size) 
	myData = {'upload_time' : datetime_str, 'PIR_detection' : PIR_detection, 'ultrasonic_detection' : ultrasonic_detection}
	data = {'filename': filename, 'data': json.dumps(myData)}
	files = {'file': (filename, open(filename, 'rb'), filetype, {'Expires': '0'})}
	r = requests.post(url, files=files, data=data, timeout=requestTimeout)
	#print r.content
	print "--> post_file " + filename + " " + datetime_str + " finished on " + time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())

print "after def post_file"
	
# this returns true if a given file has been created to simulate the activation of the PIR detector
def simulate_intruder() :
	global k
	#print "start checking simulation intruder " + str(k)
	k = k + 1
	simulate_intruder_filename = "simulate.intruder"
	simulate_intruder_filename = "/home/pi/loki/simulate.intruder"
	result = os.path.exists(simulate_intruder_filename);
	if (result):
		print "!!!!!!!!!!!! removed "+simulate_intruder_filename
		os.remove(simulate_intruder_filename)
		return True
	else:
		return False
	
print "before Camera"
camera = picamera.PiCamera()

GPIO.setwarnings(False)
GPIO.setmode(GPIO.BOARD)
GPIO.setup(11, GPIO.IN)         #Read output from PIR motion sensor
GPIO.setup(3, GPIO.OUT)         #LED output pin


print "before delay"

#make sure the wlan0 check is started immediately
prev_time_wlan0=datetime.datetime.now()-datetime.timedelta(hours=1) 
delay_wlan0=datetime.timedelta(seconds=5*60)

dweetIO = "https://dweet.io/dweet/for/"
myName = "toto123412345"
myKey = "eat_time"
myKey2 = "IP"
tempC = []
cnt=0

lcd.init()
lcd.cls()
#let's keep the screen dark in order not to bother Loki :-)
#lcd.backlight(1)
lcd.backlight(0)
lcd.text("Hello world!")
lcd.locate(0, 3)
lcd.text("toto :-)")
lcd.locate(0, 1)
lcd.text(time.strftime("%d %b %Y", time.localtime()))

wlan0_ip = "no IP yet"
#rqsString = dweetIO+myName+'?'+myKey2+'='+wlan0_ip
#rqs = requests.get(rqsString)
#print "rqsString :", rqsString

tempC = []

now1=datetime.datetime.now()
print "now : " + str(now1)
print "prev_time_wlan0 : " + str(prev_time_wlan0)
print "delay_wlan0: " + str(delay_wlan0)
print "starting"
while True:
	sys.stdout.flush()	
	sys.stderr.flush()	
	try:
		now1=datetime.datetime.now()

		
		#check if wlan0 is OK
		#print "before checking wlan0"
		if (now1 >= prev_time_wlan0 + delay_wlan0) :
			print "checking wlan0"

			i=0
			wlan0_ip = None
			while wlan0_ip is None:
				i = i+1
				ifconfig = subprocess.check_output(['ifconfig'])
				wlan0 = re.search(r'wlan0.*?inet addr:\d+.\d+.\d+.\d+',ifconfig,re.DOTALL)
				if wlan0 is not None:
					wlan0_ip = (re.search(r'(?<=inet addr:)\d+.\d+.\d+.\d+',wlan0.group(0))).group(0)

				if wlan0_ip is None:
					time.sleep(1) 	
					lcd.locate(0, 2)
					lcd.text('no IP! : i='+str(i))
					print('no IP! : i='+str(i))
 

			lcd.locate(0, 2)
			lcd.text(wlan0_ip)
			print "wlan0 IP : " + wlan0_ip + " on " + time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())

			prev_time_wlan0 = now1


		#print "checking detector"
		i=GPIO.input(11)
		if (not simulate_intruder()) and ((now1 < prev_time_detector + delay_detector) or (i==0)) : #When outside delay or output from motion sensor is LOW
			#print "No intruders",i
			GPIO.output(3, 0)  #Turn OFF LED
			time.sleep(0.1)
			PIR_detection = False
			
		else:               #When output from motion sensor is HIGH
			PIR_detection = True
			
		if True :
			ultrasonic_detection = False
		else:
			ultrasonic_detection = True
			
			
		if PIR_detection or ultrasonic_detection:	
			
			time_str=time.strftime("%H:%M:%S", time.localtime())
			datetime_str=time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())

			lcd.locate(0, 4)
			lcd.text("Intruder !")
			lcd.locate(0, 5)
			lcd.text(time_str)

			print time_str
			print "Intruder detected",i
			GPIO.output(3, 1)  #Turn ON LED
			time.sleep(detector_sleep_time)
			
			ostemp = os.popen('vcgencmd measure_temp').readline()
			temp = (ostemp.replace("temp=", "").replace("'C\n", ""))

			# take a snapshot
			camera.hflip = False
			camera.vflip = False
			#camera.resolution = (1024, 768)
			#camera.resolution = (640, 480)
			camera.resolution = (320, 240)
			camera.exposure_mode = 'night'
			camera.capture('pict.jpg')
			
			# capture video stream 
			print "Start camera recording" + time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
			
			camera.resolution = (320, 240)
			camera.start_recording('video.h264')
			time.sleep(video_duration)
			#time.sleep(10)
			#camera.wait_recording(6)
			camera.stop_recording()
			print "End camera recording"+ time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
					
			'''			
			camera.resolution = (640, 480)
			#camera.resolution = (320, 240)

			#quality - Specifies the quality that the encoder should attempt to maintain. 
			#For the 'h264' format, use values between 10 and 40 where 10 is extremely high quality
			#and 40 is extremely low (20-25 is usually a reasonable range for H.264 encoding). 
			camera.start_recording('video.h264', quality=20)
			#time.sleep(video_duration)
			camera.wait_recording(video_duration)
			camera.stop_recording()
			'''
			
			# convert to mp4
			if os.path.exists("video.mp4") : 
				os.remove("video.mp4")
			# nb : subprocess waits for the command to complete
			subprocess.call (["MP4Box -add video.h264 video.mp4"], shell=True)
						
			# post the captured files on the cloud
			t2=threading.Thread(target=post_file, args=('pict.jpg', datetime_str,'image/jpg', PIR_detection, ultrasonic_detection))
			t2.start()

			t3=threading.Thread(target=post_file, args=('video.mp4', datetime_str,'video/mp4',PIR_detection, ultrasonic_detection))
			t3.start()

			myData = {'log_time' : datetime_str, 'log_txt':'detected ! (Raspberry)',\
					  'log_temp' : temp, 'add' : 1}

			#r = requests.post("https://phptest-toto240325.c9users.io/add_record.php", data=myData)
			#r = requests.post("http://preview.yuyqf3ecatw3ik9xs0w8gb0m251m7vif7b03ub9kqfwp14i.box.codeanywhere.com/test/add_record.php", data=myData)
			#print('status and reason : ',r.status_code, r.reason)
			#print('r.text :',r.text[:900] + '...')

			#r = requests.post("http://toto240325.comlu.com/test/add_record.php", data=myData)

			print "Posting detection info on "+datetime_str
			r = requests.post(phpServer+"add_record.php", data=myData, timeout=requestTimeout)
			print('status and reason : ',r.status_code, r.reason)
			print('r.text :',r.text[:900] + '...')

			rqsString = dweetIO+myName+'?'+myKey+'='+time_str+'&'+myKey2+'='+wlan0_ip
			print "Dweeting : " + rqsString
			t4=threading.Thread(target=dweet, args=(rqsString,))
			t4.start()

			prev_time_detector=datetime.datetime.now()


		#CPU temperature
		if (now1 >= prev_time_temp + delay_temp) :
			ostemp = os.popen('vcgencmd measure_temp').readline()
			temp = (ostemp.replace("temp=", "").replace("'C\n", ""))
			time_str=time.strftime("%H:%M:%S", time.localtime())
			datetime_str=time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
			myData = {'temp_time' : datetime_str, 'temp_temp' : temp, 'add' : 1}

			#r = requests.post("http://preview.yuyqf3ecatw3ik9xs0w8gb0m251m7vif7b03ub9kqfwp14i.box.codeanywhere.com/test/add_temp.php", data=myData)
			#print('status and reason : ',r.status_code, r.reason)
			#print('r.text :',r.text[:300] + '...')

			#r = requests.post("http://toto240325.comlu.com/test/add_temp.php", data=myData)
			
			print "Posting temp " + temp + " on " + datetime_str 
			r = requests.post(phpServer+"add_temp.php", data=myData, timeout=requestTimeout)
			#print('status and reason : ',r.status_code, r.reason)
			#print('r.text :',r.text[:300] + '...')

			prev_time_temp = now1
			
	except requests.exceptions.ConnectionError:
		print "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
		print "ConnectionError Exception !"
		print "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
		

	except requests.exceptions.ReadTimeout:
		print "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
		print "ReadTimeout Exception !"
		print "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
		
	except Exception as e:
		print "!!!!!!!! Exception !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
		print type(e)     # the exception instance
		print e.args      # arguments stored in .args
		print e           # __str__ allows args to be printed directly
		print e.__str__()
		#print "Ooooops, exception : ({0}): {1}".format(e.errno, e.strerror)
		#template = "An exception of type {0} occured. Arguments:\n{1!r}"
		#message = template.format(type(e).__name__, e.args)
		#print message
		print "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
	"""


	
	
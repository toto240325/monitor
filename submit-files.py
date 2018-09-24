#!/usr/bin/env python
# this script takes the file found in ~/monitor/avi/tosend and posts them on a webserver (where it will be put in a database)
#  
#
# # to test requests.post : 
#url = 'http://httpbin.org/post'
#files = {'file': ('test.txt', open('test.txt', 'rb'), 'text/text', {'Expires': '0'})}
#r = requests.post(url, files=files)
#print(r.text)



import requests
import os
import time
import datetime
import subprocess
import re  
import json
import threading
import sys
import binascii

phpServer="http://192.168.0.147/monitor/"

"""
print "Starting (to check the output : "
print "tail -f ~/logs/pirtest.log"

sys.stdout = open('/home/pi/logs/pirtest.log', 'w+')
sys.stderr = open('/home/pi/logs/pirtest.log', 'w+')
"""

k = 0
requestTimeout = 1000 	# timeout for requests

def dweet(rqsString):			
	rqs = requests.get(rqsString)
	#print(rqs)
	#print rqs.status_code
	#print rqs.headers
	#print rqs.content
	print ("--> dweeted " + rqsString)
	print ("    finished on " + time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()))

	

def unescape(s):
	s = s.replace("&lt;", "<")
	s = s.replace("&quot;", '"')
	s = s.replace("&gt;", ">")
	# this has to be last:
	s = s.replace("&amp;", "&")
	return s

def post_file(fileName, datetime_str, fileType, PIR_detection, ultrasonic_detection):
	print("==start post_file==================================================================")
	url = phpServer+'upload_file.php'
	fileSize = os.path.getsize(fileName)
	#print("post_file : datetime str : " + datetime_str + " fileName : " + fileName + " size: " + str(fileSize))
	myData = {'fileName' : fileName, 'fileSize' : fileSize, 'fileType' : fileType, 'upload_time' : datetime_str, 'PIR_detection' : PIR_detection, 'ultrasonic_detection' : ultrasonic_detection}
	data = {'filename': fileName, 'data': json.dumps(myData)}
	files = {'file': (fileName, open(fileName, 'rb'),  fileType, {'Expires': '0'})}
	r = requests.post(url, files=files, data=data, timeout=requestTimeout)
	#print ("content " + unescape((r.content).decode("ascii")))
	
	text = r.content[:100000].decode('utf-8')
	print(text)
	#print_r("content : " + (r.content))
	#print ("--> post_file " + fileName + " " + datetime_str + " finished on " + time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()))
	print("==end post_file==================================================================")
	
	"""
	file = open("C:\\Users\\derruer\\mydata\\mytemp\\a.html","w") 
	datetime_str=time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
	file.write("test on "+datetime_str) 
	file.write((r.content).decode("utf-8"))
	file.close() 
	"""
	
print ("after def post_file")
	

tempC = []

print ("python version : " + sys.version)
now1=datetime.datetime.now()
print ("now : " + str(now1))
print ("starting")
#while True:
if (True) :
	sys.stdout.flush()	
	sys.stderr.flush()	
	try:
		time_str=time.strftime("%H:%M:%S", time.localtime())
		datetime_str=time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())

		#ostemp = os.popen('vcgencmd measure_temp').readline()
		#temp = (ostemp.replace("temp=", "").replace("'C\n", ""))
		temp = "99.99"

		# post the captured files on the cloud
		#txt = os.popen('echo %cd%').readline()
		#print ('"current directory : ' + txt)
		#print ('current time ' +  datetime_str)
		

		#t2=threading.Thread(target=post_file, args=('pict.jpg', datetime_str,'image/jpg', False, False))
		#t2.start()

		post_file('C:\\Users\\derruer\\mydata\\projects\\htdocs\\monitor\\pi-client\\video.mp4', datetime_str,'video/mp4',False, False)
		#post_file('C:\\Users\\derruer\\mydata\\projects\\htdocs\\monitor\\pi-client\\test.txt', datetime_str,'video/mp4',False, False)
		#t3=threading.Thread(target=post_file, args=('C:\\Users\\derruer\\mydata\\projects\\htdocs\\monitor\\pi-client\\video.mp4', datetime_str,'video/mp4',False, False))
		#t3.start()

		myData = {'eventFct' : 'add', 'time':'2018-01-22','host' : 'my host', 'text' : temp,'type' : 'uploading file'}

		#http://localhost/monitor/getEvent.php?eventFct=add&time="2018-01-16"&host=myHost&text="my text"&type="my type"

		#r = requests.post("https://phptest-toto240325.c9users.io/add_record.php", data=myData)
		#r = requests.post("http://preview.yuyqf3ecatw3ik9xs0w8gb0m251m7vif7b03ub9kqfwp14i.box.codeanywhere.com/test/add_record.php", data=myData)
		#print('status and reason : ',r.status_code, r.reason)
		#print('r.text :',r.text[:900] + '...')

		#r = requests.post("http://toto240325.comlu.com/test/add_record.php", data=myData)

		print ("Posting detection info on "+datetime_str)
		r = requests.post(phpServer+"getEvent.php", data=myData, timeout=requestTimeout)
		print('status and reason : ',r.status_code, r.reason)
		print('r.text :',r.text[:900] + '...')

		file = open("C:\\Users\\derruer\\mydata\\mytemp\\b.html","w") 
		datetime_str=time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
		file.write("test on "+datetime_str) 
		file.write(r.text)
		file.close() 



		"""
		rqsString = dweetIO+myName+'?'+myKey+'='+time_str+'&'+myKey2+'='+wlan0_ip
		print ("Dweeting : " + rqsString)
		t4=threading.Thread(target=dweet, args=(rqsString,))
		t4.start()
		"""
			
	except requests.exceptions.ConnectionError:
		print ("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!")
		print ("ConnectionError Exception !")
		print ("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!")
		

	except requests.exceptions.ReadTimeout:
		print ("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!")
		print ("ReadTimeout Exception !")
		print ("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!")
	
	"""
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

print ("ending")

	
	
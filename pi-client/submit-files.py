#  !/usr/bin/env python
# this script takes the file found in ~/monitor/avi/tosend and posts them on a webserver (where it will be put in a database)
#
#
# # to test requests.post : 
#url = 'http://httpbin.org/post'
#files = {'file': ('test.txt', open('test.txt', 'rb'), 'text/text', {'Expires': '0'})}
#r = requests.post(url, files=files)
#print(r.text)

import os
import shutil
import socket
import time
import datetime
import re
import subprocess
import re  
import json
import threading
import sys
import requests


def unescape(s):
    s = s.replace("&lt;", "<")
    s = s.replace("&quot;", '"')
    s = s.replace("&gt;", ">")
    # this has to be last:
    s = s.replace("&amp;", "&")
    return s

def dweet(rqsString):			
    rqs = requests.get(rqsString)
    #print(rqs)
    #print rqs.status_code
    #print rqs.headers
    #print rqs.content
    print("--> dweeted " + rqsString)
    print("    finished on " + time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()))


def post_file(fileName, datetime_str, fileType):
    #print("==start post_file==================================================================")
    url = phpServer+'upload_file.php'
    fileSize = os.path.getsize(fileName)
    
    #print("post_file : datetime str : " + datetime_str + " filename : " + filename + " size: " + str(size))
    
    myData = {'fileName' : fileName, 'fileSize' : fileSize, 'fileType' : fileType, 'upload_time' : datetime_str }
    data = {'fileName': fileName, 'data': json.dumps(myData)}
    files = {'file': (fileName, open(fileName, 'rb'),  fileType, {'Expires': '0'})}
    r = requests.post(url, files=files, data=data, timeout=requestTimeout)
   
    
    #myData1 = {'upload_time' : datetime_str, 'PIR_detection' : 0, 'ultrasonic_detection' : 0}
    #data = {'filename': filename, 'data': json.dumps(myData1)}
    #files = {'file': (filename, open(filename, 'rb'), filetype, {'Expires': '0'})}
    #r = requests.post(url, files=files, data=data, timeout=requestTimeout)
    #print("--> post_file " + fileName + " " + datetime_str + " finished on " + time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()))

    resultCode = -1
    if re.search("uploaded", r.text):
        resultCode = 0
    if resultCode == -1:
        print("======> search string : %s" % r.text)

    resultReason = r.reason
    resultText = r.text[:100]
    return(resultCode, resultReason, resultText)


def post_file_old(fileName, datetime_str, fileType, PIR_detection, ultrasonic_detection):
    #print("==start post_file==================================================================")
    url = phpServer+'upload_file.php'
    fileSize = os.path.getsize(fileName)
    #print("post_file : datetime str : " + datetime_str + " fileName : " + fileName + " size: " + str(fileSize))
    myData = {'fileName' : fileName, 'fileSize' : fileSize, 'fileType' : fileType, 'upload_time' : datetime_str, 'PIR_detection' : PIR_detection, 'ultrasonic_detection' : ultrasonic_detection}
    data = {'filename': fileName, 'data': json.dumps(myData)}
    files = {'file': (fileName, open(fileName, 'rb'),  fileType, {'Expires': '0'})}
    r = requests.post(url, files=files, data=data, timeout=requestTimeout)
    #print ("content " + unescape((r.content).decode("ascii")))
    
    text = r.content[:100000].decode('utf-8')
    #print(text)
    #print_r("content : " + (r.content))
    #print ("--> post_file " + fileName + " " + datetime_str + " finished on " + time.strftime("%Y-%m-%d %H:%M:%S", time.localtime()))
    #print("==end post_file==================================================================")
    
    """
    file = open("C:\\Users\\derruer\\mydata\\mytemp\\a.html","w") 
    datetime_str=time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
    file.write("test on "+datetime_str) 
    file.write((r.content).decode("utf-8"))
    file.close() 
    """

def moveFiles(src,dst) :
    files = os.listdir(src)
    files.sort()
    for f in files:
        shutil.move(src+f,dst+f)
        
def movefiles2(src,dst) :
    import glob
    for file in glob.glob(srcr+"*.*"):
        print("moving "+file)
        shutil.move(file, dst)

"""
print "Starting (to check the output : "
print "tail -f ~/logs/pirtest.log"
sys.stdout = open('/home/pi/logs/pirtest.log', 'w+')
sys.stderr = open('/home/pi/logs/pirtest.log', 'w+')
"""

k = 0
requestTimeout = 1000 	# timeout for requests

phpServer = "http://192.168.0.147/monitor/"

myHostname = socket.gethostname()
if myHostname == "L02DI1453375DIT":
    aviFolder = "C:\\Users\\derruer\\mydata\\projects\\htdocs\\monitor\\pi-client\\avi\\"
    tmpFolder = "C:\\Users\\derruer\\mydata\\projects\\htdocs\\monitor\\pi-client\\avi-temp\\"
    archivedFolder = "C:\\Users\\derruer\\mydata\\projects\\htdocs\\monitor\\pi-client\\avi-archived\\"
    currDir = os.popen('echo %cd%').readline()
elif myHostname == "raspberrypi4":
    #aviFolder = "/var/lib/motion/"
    aviFolder = "/home/pi/avi-test/"
    tmpFolder = "/home/pi/avi-tmp/"
    archivedFolder = "/home/pi/avi-archive/"
    currDir = os.popen('pwd').readline()

print('"current directory : ' + currDir)
print ("python version : " + sys.version)
now1=datetime.datetime.now()
time_str = time.strftime("%H:%M:%S", time.localtime())
datetime_str = time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
print(time_str)
print("myHostname : "+myHostname)
print ("now : " + str(now1))
print ("starting")

tempC = []


try:

    #copy all video files in a temp directory so that we are sure none of them is still being written to
    moveFiles(aviFolder,tmpFolder)

    #post each file in the tmp area to the server then move it to the archived area
    files = os.listdir(tmpFolder)
    files.sort()
    for f in files:       
        t = os.path.getmtime(tmpFolder+f)
        dt = datetime.datetime.fromtimestamp(t)
        datetime_str = dt.strftime('%Y-%m-%d %H:%M:%S')

        (resultCode, resultReason, resultText) = post_file(tmpFolder+f, datetime_str, 'video/mp4')
        if (resultCode == 0):
                print("file uploaded : %s (date : %s)" % (f,datetime_str))
        else:
            print("file upload error : %s (code : %d, reason : %s)" % (f,resultCode, resultReason))
        shutil.move(tmpFolder+f,archivedFolder+f)


    '''
    #ostemp = os.popen('vcgencmd measure_temp').readline()
    #temp = (ostemp.replace("temp=", "").replace("'C\n", ""))
    temp = "99.99"

    # post the captured files on the cloud
    #(resultCode, resultReason, resultText) = post_file(aviFolder+"pict.jpg", datetime_str, 'image/jpg')
    (resultCode, resultReason, resultText) = post_file(aviFolder+"video.mp4", datetime_str, 'video/mp4')

    print("resultCode : %3d" % resultCode)
    print("resultReason : " + resultReason)
    print("resultText : " + resultText)


    #t3 = threading.Thread(target=post_file, args=('video.mp4', datetime_str,'video/mp4',False, False))
    #t3.start()

    myData = {'eventFct' : 'add', 'time':'2018-01-22', 'host' : 'my host', 'text' : temp,'type' : 'uploading file'}

    print("Posting detection info on "+datetime_str)
    r = requests.post(phpServer+"getEvent.php", data=myData, timeout=requestTimeout)
    print('status and reason : ', r.status_code, r.reason)
    print('r.text :', r.text[:900] + '...')
    '''

    """
    file = open("C:\\Users\\derruer\\mydata\\mytemp\\b.html","w")
    datetime_str=time.strftime("%Y-%m-%d %H:%M:%S", time.localtime())
    file.write("test on "+datetime_str)
    file.write(r.text)
    file.close()
    """

    """
    rqsString = dweetIO+myName+'?'+myKey+'='+time_str+'&'+myKey2+'='+wlan0_ip
    print ("Dweeting : " + rqsString)
    t4=threading.Thread(target=dweet, args=(rqsString,))
    t4.start()
    """
        
except requests.exceptions.ConnectionError as e:
    print("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!")
    print("ConnectionError Exception !")
    print(type(e))     # the exception instance
    print(e.args)      # arguments stored in .args
    print(e)           # __str__ allows args to be printed directly
    print(e.__str__())
    print("Ooooops, exception : ({0}): {1}".format(e.errno, e.strerror))
    template = "An exception of type {0} occured. Arguments:\n{1!r}"
    message = template.format(type(e).__name__, e.args)
    print(message)
    print ("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!")      

except requests.exceptions.ReadTimeout:
    print("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!")
    print("ReadTimeout Exception !")
    print("!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!")
    
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


    
    
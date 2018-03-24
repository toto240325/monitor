#!/usr/bin/env python
import requests
url = 'http://httpbin.org/post'
files = {'file': ('test.txt', open('test.txt', 'rb'), 'text/text', {'Expires': '0'})}
r = requests.post(url, files=files)
print(r.text)


import urllib.request, json
req = urllib.request.Request('http://127.0.0.1:5000/api/v1/auth/login', json.dumps({'email':'anant@gmail.com','password':'123456'}).encode('utf-8'), {'Content-Type': 'application/json'});
res = urllib.request.urlopen(req)
token = json.loads(res.read())['data']['token']
req2 = urllib.request.Request('http://127.0.0.1:5000/api/v1/admin/orders', headers={'Authorization': 'Bearer ' + token})
try:
  urllib.request.urlopen(req2)
except Exception as e:
  print(e.read().decode())

"""
Test the order creation endpoint end-to-end.
Run from: c:/xampp/htdocs/clothing_project
"""
import sys, json, urllib.request, urllib.error

sys.path.append('c:/xampp/htdocs/clothing_project/urbanwear-fastapi')

BASE = "http://127.0.0.1:5000"

# Step 1: Login
def do_request(method, url, data=None, token=None):
    req = urllib.request.Request(url, method=method)
    req.add_header("Content-Type", "application/json")
    if token:
        req.add_header("Authorization", f"Bearer {token}")
    body = json.dumps(data).encode() if data else None
    try:
        with urllib.request.urlopen(req, body, timeout=10) as resp:
            return json.loads(resp.read()), resp.status
    except urllib.error.HTTPError as e:
        return json.loads(e.read()), e.code

# Login
print("--- LOGIN ---")
resp, code = do_request("POST", f"{BASE}/api/v1/auth/login", {"email": "anant@gmail.com", "password": "123456"})
print(f"Status: {code}")
if not resp.get("success"):
    print("LOGIN FAILED:", resp)
    sys.exit(1)

token = resp.get("data", {}).get("token") or resp.get("token") or ""
print(f"Token: {token[:40]}...")

# Step 2: Create order
print("\n--- CREATE ORDER ---")
payload = {
    "products": [{"productId": "6785e0b1b559f02e6d0e6f90", "quantity": 1, "size": "M", "color": "Black"}],
    "shipping": {"name": "Test User", "mobile": "9999999999", "address": "Test Street", "city": "Mumbai", "state": "MH"},
    "paymentMethod": "COD",
    "totalAmount": 1000
}

resp2, code2 = do_request("POST", f"{BASE}/api/v1/orders", payload, token)
print(f"Status: {code2}")
print("Response:", json.dumps(resp2, indent=2)[:500])

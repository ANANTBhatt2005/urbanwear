import sys
sys.path.append('c:/xampp/htdocs/clothing_project/urbanwear-fastapi')
from database import orders_col
from bson import ObjectId

def parse_order(o):
    o["_id"] = str(o["_id"])
    o["userId"] = str(o.get("userId", ""))
    products = o.get("products") or o.get("items") or []
    for item in products:
        if isinstance(item, dict):
            pid = item.get("productId")
            if isinstance(pid, ObjectId):
                item["productId"] = str(pid)
            elif isinstance(pid, dict) and "_id" in pid:
                pid["_id"] = str(pid["_id"])
    return o

def find_objectid(data, path=""):
    if isinstance(data, dict):
        for k, v in data.items():
            find_objectid(v, f"{path}.{k}")
    elif isinstance(data, list):
        for i, v in enumerate(data):
            find_objectid(v, f"{path}[{i}]")
    elif isinstance(data, ObjectId):
        print(f"Found ObjectId at {path}: {data}")

orders = list(orders_col.find({}))
for i, o in enumerate(orders):
    parsed = parse_order(o)
    find_objectid(parsed, f"Order[{i}]")

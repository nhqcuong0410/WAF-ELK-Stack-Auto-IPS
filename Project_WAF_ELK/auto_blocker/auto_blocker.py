import requests
import time
import os
import subprocess
from datetime import datetime

ES_URL = "http://elasticsearch:9200"
INDEX = "waf-logs-*"
BLOCKED_INDEX = "waf-blocked-ips"
BLACKLIST_FILE = "/etc/nginx/conf.d/blacklist.conf"
WAF_CONTAINER = "project_waf_elk-waf-1"
THRESHOLD = 5
TIME_WINDOW = "5m"
CHECK_INTERVAL = 10
BAN_TTL_HOURS = 24

def wait_for_elasticsearch(timeout=120):
    print(f"[*] Đang chờ Elasticsearch khởi động ({timeout}s)...")
    start_time = time.time()
    while time.time() - start_time < timeout:
        try:
            res = requests.get(ES_URL, timeout=5)
            if res.status_code == 200:
                print("[+] Elasticsearch đã sẵn sàng!")
                return True
        except requests.exceptions.ConnectionError:
            pass
        time.sleep(5)
    print("[-] Lỗi: Elasticsearch không phản hồi!")
    return False

def get_attacker_ips() -> list:
    query = {
        "size": 0,
        "query": {
            "bool": {
                "must": [
                    {"range": {"@timestamp": {"gte": f"now-{TIME_WINDOW}"}}}
                ],
                "must_not": [
                    {"term": {"is_unblocked": True}}
                ]
            }
        },
        "aggs": {
            "attacker_ips": {
                "terms": {
                    "field": "client_ip.keyword",
                    "size": 100,
                    "min_doc_count": THRESHOLD
                }
            }
        }
    }
    try:
        resp = requests.post(f"{ES_URL}/{INDEX}/_search", json=query, timeout=5)
        resp.raise_for_status()
        buckets = resp.json().get("aggregations", {}).get("attacker_ips", {}).get("buckets", [])
        return [b["key"] for b in buckets]
    except Exception as e:
        print(f"[ERROR] Query ES thất bại: {e}")
        return []

def parse_blacklist():
    db = {}
    if not os.path.exists(BLACKLIST_FILE):
        return db
    with open(BLACKLIST_FILE, "r") as f:
        for line in f:
            line = line.strip()
            if line.startswith("deny "):
                parts = line.split()
                ip = parts[1].replace(";", "")
                expire_time = int(time.time()) + (BAN_TTL_HOURS * 3600)
                if len(parts) >= 5 and parts[3] == "expire:":
                    try:
                        expire_time = int(parts[4])
                    except ValueError:
                        pass
                db[ip] = expire_time
    return db

def write_blacklist(db):
    os.makedirs(os.path.dirname(BLACKLIST_FILE), exist_ok=True)
    with open(BLACKLIST_FILE, "w") as f:
        f.write("# Auto-generated\n")
        for ip, expire_time in db.items():
            f.write(f"deny {ip};\t# expire: {expire_time}\n")
    return True

def sync_to_es(db):
    for ip, expire_time in db.items():
        doc = {
            "ip": ip,
            "banned_at": datetime.utcnow().isoformat() + "Z",
            "expire_at": datetime.utcfromtimestamp(expire_time).isoformat() + "Z",
            "status": "blocked"
        }
        try:
            requests.post(f"{ES_URL}/{BLOCKED_INDEX}/_doc/{ip}", json=doc, timeout=2)
        except:
            pass

def remove_from_es(ip):
    try:
        requests.delete(f"{ES_URL}/{BLOCKED_INDEX}/_doc/{ip}", timeout=2)
    except:
        pass

def expire_old_bans(db):
    current_time = int(time.time())
    expired_ips = [ip for ip, exp in db.items() if current_time > exp]
    for ip in expired_ips:
        del db[ip]
        remove_from_es(ip)
        print(f"[*] Đã gỡ chặn IP (Hết hạn): {ip}")
    return db, len(expired_ips) > 0

def add_new_bans(db, attacker_ips):
    current_time = int(time.time())
    changed = False
    for ip in attacker_ips:
        if ip not in db:
            db[ip] = current_time + (BAN_TTL_HOURS * 3600)
            changed = True
            print(f"[!] Phát hiện IP mới cần chặn: {ip}")
        else:
            db[ip] = current_time + (BAN_TTL_HOURS * 3600)
    return db, changed

def reload_nginx():
    try:
        result = subprocess.run(
            ["docker", "exec", WAF_CONTAINER, "nginx", "-s", "reload"],
            capture_output=True, text=True
        )
        if result.returncode == 0:
            print("[+] Nginx reload thành công")
        else:
            print(f"[ERROR] Nginx reload lỗi: {result.stderr}")
    except Exception as e:
        print(f"[ERROR] Nginx reload thất bại: {e}")

def main():
    os.makedirs(os.path.dirname(BLACKLIST_FILE), exist_ok=True)
    if not os.path.exists(BLACKLIST_FILE):
        with open(BLACKLIST_FILE, "w") as f:
            f.write("# Auto-generated blacklist\n")
        reload_nginx()
        print("[+] Đã tạo file blacklist rỗng")

    wait_for_elasticsearch(timeout=120)

    while True:
        print(f"\n[{datetime.now().strftime('%H:%M:%S')}] Đang kiểm tra...")
        
        db = parse_blacklist()
        db, expired_changed = expire_old_bans(db)
        attacker_ips = get_attacker_ips()
        db, new_changed = add_new_bans(db, attacker_ips)

        if expired_changed or new_changed:
            write_blacklist(db)
            sync_to_es(db)
            reload_nginx()
        else:
            print("[✓] Không có thay đổi (Không có IP mới và không có IP hết hạn)")

        time.sleep(CHECK_INTERVAL)

if __name__ == "__main__":
    main()
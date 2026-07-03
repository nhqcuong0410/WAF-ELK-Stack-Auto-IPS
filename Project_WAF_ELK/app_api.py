import os
import requests
import subprocess
import time
from flask import Flask, request, jsonify
from flask_cors import CORS

app = Flask(__name__)
CORS(app)

ELASTICSEARCH_URL = "http://localhost:9201"
WAF_CONTAINER_NAME = "project_waf_elk-waf-1"
PROJECT_DIR = os.path.dirname(os.path.abspath(__file__))

def docker_compose(action: str, service: str):
    result = subprocess.run(
        ["docker", "compose", action, service],
        cwd=PROJECT_DIR,
        capture_output=True, text=True
    )
    if result.returncode != 0:
        print(f"[WARN] docker compose {action} {service}: {result.stderr.strip()}")
    return result.returncode == 0

def es_delete(index: str, query: dict):
    try:
        r = requests.post(
            f"{ELASTICSEARCH_URL}/{index}/_delete_by_query?conflicts=proceed",
            json={"query": query},
            timeout=10
        )
        deleted = r.json().get("deleted", 0)
        print(f"[INFO] ES delete from {index}: {deleted} docs")
        return deleted
    except Exception as e:
        print(f"[WARN] ES delete {index} failed: {e}")
        return 0

@app.route('/api/unblock', methods=['POST'])
def unblock_ip():
    data = request.json
    ip = data.get('ip')
    if not ip:
        return jsonify({"status": "error", "message": "Missing IP"}), 400

    print(f"\n[INFO] Gỡ chặn IP [{ip}]...")
    docker_compose("stop", "auto_blocker")

    r1 = requests.delete(
        f"{ELASTICSEARCH_URL}/waf-blocked-ips/_doc/{ip}",
        timeout=5
    )
    print(f"[INFO] ES delete waf-blocked-ips/_doc/{ip}: {r1.status_code}")

    try:
        update_body = {
            "script": {"source": "ctx._source.is_unblocked = true"},
            "query": {"term": {"client_ip.keyword": ip}}
        }
        requests.post(f"{ELASTICSEARCH_URL}/waf-logs-*/_update_by_query?conflicts=proceed", json=update_body)
        deleted_msg = "0 (Đã giữ lại & dán nhãn ngoại lệ)"
        print(f"[INFO] Đã đánh dấu is_unblocked=true cho IP {ip}")
    except Exception as e:
        print(f"[ERROR] Lỗi update log: {e}")
        deleted_msg = "0"

    r3 = subprocess.run(
        ["docker", "exec", WAF_CONTAINER_NAME, "sh", "-c",
         f"sed -i '/{ip}/d' /etc/nginx/conf.d/blacklist.conf && nginx -s reload"],
        capture_output=True, text=True
    )
    print(f"[INFO] sed+reload: rc={r3.returncode} {r3.stderr.strip()}")

    time.sleep(1)
    docker_compose("start", "auto_blocker")

    return jsonify({
        "status": "success",
        "message": f"Đã gỡ chặn IP [{ip}] và xử lý {deleted_msg} log cũ."
    })

@app.route('/api/clear-all', methods=['POST'])
def clear_all():
    print("\n[INFO] Đang xóa toàn bộ dữ liệu...")
    docker_compose("stop", "auto_blocker")
    
    es_delete("waf-*", {"match_all": {}})
    
    subprocess.run(
        ["docker", "exec", WAF_CONTAINER_NAME, "sh", "-c",
         "echo '# blacklist' > /etc/nginx/conf.d/blacklist.conf && nginx -s reload"],
        capture_output=True
    )
    
    time.sleep(1)
    docker_compose("start", "auto_blocker")
    
    return jsonify({"status": "success", "message": "Đã reset toàn bộ hệ thống về 0!"})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
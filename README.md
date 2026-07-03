# WAF-ELK-Stack-Auto-IPS

Hệ thống Web Application Firewall (WAF) tích hợp ELK Stack với cơ chế **tự động phát hiện và chặn IP tấn công** (Auto IPS). Đây là đồ án tốt nghiệp chuyên ngành An toàn thông tin.

## 🎯 Mục tiêu

Xây dựng một hệ thống bảo vệ ứng dụng web có khả năng:
- Phát hiện các cuộc tấn công phổ biến (SQL Injection, XSS, Directory Traversal, Command Injection, DDoS SYN Flood...)
- Ghi log và trực quan hóa dữ liệu tấn công theo thời gian thực
- Tự động chặn (block) IP nguồn tấn công mà không cần can thiệp thủ công

## 🏗️ Kiến trúc hệ thống

```
Client → Nginx + ModSecivity (WAF) → Ứng dụng web (SV Shop)
                    │
                    ▼
               Logstash → Elasticsearch → Kibana Dashboard
                    │
                    ▼
            auto_blocker.py → blacklist.conf → Reload Nginx → 403 Forbidden
```

**Luồng xử lý:** ModSecurity phát hiện tấn công → Logstash thu thập log → Elasticsearch lưu trữ và index → script `auto_blocker.py` phân tích, ghi IP vào blacklist → Nginx reload cấu hình → chặn IP ở các request tiếp theo.

## 🔧 Công nghệ sử dụng

| Thành phần | Công nghệ |
|---|---|
| Reverse Proxy / WAF | Nginx + ModSecurity |
| Thu thập log | Logstash |
| Lưu trữ & tìm kiếm | Elasticsearch |
| Trực quan hóa | Kibana / Dashboard HTML tùy chỉnh |
| Tự động hóa chặn IP | Python (`auto_blocker.py`) |
| Môi trường triển khai | Docker, Docker Compose |
| Ứng dụng demo (có lỗ hổng) | PHP (SV Shop – chứa XSS, LFI) |

## 📂 Cấu trúc thư mục

```
├── docker-compose.yml
├── nginx/
│   └── default.conf.template
├── modsecurity/
│   └── rules/
├── logstash/
│   └── pipeline/
├── auto_blocker/
│   └── auto_blocker.py
├── dashboard/
│   └── index.html
├── sv-shop/              # Ứng dụng web demo có lỗ hổng
├── scripts/
│   ├── demo_attack.ps1   # Script mô phỏng tấn công
│   └── unblock.ps1       # Script gỡ chặn IP
└── docs/
    └── architecture-diagram.drawio
```

## 🚀 Cách chạy hệ thống

```bash
git clone https://github.com/nhqcuong0410/WAF-ELK-Stack-Auto-IPS.git
cd WAF-ELK-Stack-Auto-IPS
docker-compose up -d
```

Truy cập:
- Dashboard giám sát: `http://localhost:xxxx`
- Kibana: `http://localhost:5601`
- Ứng dụng demo (SV Shop): `http://localhost:xxxx`

## 🧪 Kịch bản kiểm thử

Repo bao gồm script `demo_attack.ps1` mô phỏng các loại tấn công:
- SQL Injection
- Cross-Site Scripting (XSS)
- Directory Traversal
- Command Injection

Sau khi tấn công bị phát hiện, IP nguồn sẽ tự động bị đưa vào `blacklist.conf` và bị chặn (HTTP 403) ở các request sau. Dùng `unblock.ps1` để gỡ chặn thủ công khi cần test lại.

## 📊 Kết quả đạt được

- Phát hiện và chặn tự động IP tấn công trong thời gian thực, không cần thao tác thủ công.
- Trực quan hóa toàn bộ log tấn công qua dashboard Kibana/HTML.
- Giảm thiểu rủi ro từ các lỗ hổng OWASP Top 10 phổ biến.

## 👨‍💻 Tác giả

Đồ án tốt nghiệp – Ngành Công nghệ Thông tin, chuyên ngành An toàn thông tin
Trường Đại học Văn Hiến (Van Hien University)

## 📄 License

Dự án phục vụ mục đích học tập và nghiên cứu.

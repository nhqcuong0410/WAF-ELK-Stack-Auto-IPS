# ============================================================
#   UNBLOCK IP - Reset blacklist & Clear Blocked DB ONLY
# ============================================================

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "   UNBLOCK IP - Removing all blocked IPs" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Stop auto_blocker truoc
Write-Host "[1] Stopping auto_blocker..." -ForegroundColor Yellow
docker-compose stop auto_blocker
Write-Host "    OK - auto_blocker stopped" -ForegroundColor Green
Write-Host ""

# Step 2: Clear blacklist
Write-Host "[2] Clearing blacklist..." -ForegroundColor Yellow
docker exec project_waf_elk-waf-1 sh -c "echo '# blacklist' > /etc/nginx/conf.d/blacklist.conf"
docker exec project_waf_elk-waf-1 nginx -s reload
Write-Host "    OK - Blacklist cleared" -ForegroundColor Green
Write-Host ""

# Step 3: Clear Blocked IPs from Database (Giu nguyen Attack Logs)
Write-Host "[3] Clearing Blocked IPs from Elasticsearch..." -ForegroundColor Yellow
try {
    # CHI xoa bang waf-blocked-ips, KHONG xoa waf-logs-*
    $body = '{"query": {"match_all": {}}}'
    Invoke-RestMethod -Uri "http://localhost:9201/waf-blocked-ips/_delete_by_query" -Method Post -ContentType "application/json" -Body $body -ErrorAction Stop | Out-Null
    Write-Host "    OK - Blocked IP history cleared in DB" -ForegroundColor Green
} catch {
    Write-Host "    Warning: Cannot clear DB (maybe already empty). Error: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

# Step 4: Verify
Write-Host "[4] Verifying website..." -ForegroundColor Yellow
Start-Sleep -Seconds 2
try {
    $res = Invoke-WebRequest "http://localhost" -UseBasicParsing -ErrorAction Stop
    Write-Host "    OK - Website is UP (HTTP $($res.StatusCode))" -ForegroundColor Green
} catch {
    Write-Host "    Response: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "  Website accessible at http://localhost" -ForegroundColor Green
Write-Host "  auto_blocker is STOPPED" -ForegroundColor Yellow
Write-Host "  Blocked IP list is RESET (Historical logs are KEPT)" -ForegroundColor Green
Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""
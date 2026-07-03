# ============================================================
#   DEMO: Multi-Attack Simulation
#   WAF + ELK + Auto IP Blocker
# ============================================================

Write-Host ""
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "   DEMO: WAF/ELK Auto IP Blocker - Multi Attack Simulation" -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

# STEP 1: Reset blacklist + restart auto_blocker để sync trạng thái sạch
Write-Host "[RESET] Clearing blacklist & restarting auto_blocker..." -ForegroundColor Yellow
docker exec project_waf_elk-waf-1 sh -c "printf '# Auto-generated blacklist\n# Total: 0 IP(s) blocked\n\n' > /etc/nginx/conf.d/blacklist.conf"
docker exec project_waf_elk-waf-1 nginx -s reload
docker-compose restart auto_blocker | Out-Null
Write-Host "        OK - Blacklist cleared, auto_blocker restarted" -ForegroundColor Green
Write-Host "        Waiting 15s for auto_blocker + ES to be ready..." -ForegroundColor Gray
Start-Sleep -Seconds 15
Write-Host ""

# STEP 2: Check website normal
Write-Host "[CHECK] Website status before attack..." -ForegroundColor Yellow
try {
    $res = Invoke-WebRequest "http://localhost" -UseBasicParsing -ErrorAction Stop
    Write-Host "        OK - Website is UP (HTTP $($res.StatusCode))" -ForegroundColor Green
} catch {
    Write-Host "        Response: $($_.Exception.Message)" -ForegroundColor Red
}
Write-Host ""

Start-Sleep -Seconds 2

# ============================================================
# ATTACK 1: XSS - Cross Site Scripting
# ============================================================
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host "[ATTACK 1/4] XSS - Cross Site Scripting" -ForegroundColor Red
Write-Host "             Payload: <script>alert(1)</script>" -ForegroundColor DarkRed
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray

for ($i=1; $i -le 5; $i++) {
    Write-Host "  -> Request $i/5: XSS" -ForegroundColor Red
    Invoke-WebRequest "http://localhost/?search=<script>alert(document.cookie)</script>" `
        -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
    Start-Sleep -Milliseconds 400
}
Write-Host "  [DONE] XSS attack sent" -ForegroundColor DarkRed
Write-Host ""

Start-Sleep -Seconds 1

# ============================================================
# ATTACK 2: SQL Injection
# ============================================================
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host "[ATTACK 2/4] SQLi - SQL Injection" -ForegroundColor Red
Write-Host "             Payload: 1' OR '1'='1'--" -ForegroundColor DarkRed
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray

for ($i=1; $i -le 5; $i++) {
    Write-Host "  -> Request $i/5: SQLi" -ForegroundColor Red
    Invoke-WebRequest "http://localhost/?search=1' OR '1'='1'--" `
        -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
    Start-Sleep -Milliseconds 400
}
Write-Host "  [DONE] SQLi attack sent" -ForegroundColor DarkRed
Write-Host ""

Start-Sleep -Seconds 1

# ============================================================
# ATTACK 3: LFI - Local File Inclusion
# ============================================================
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host "[ATTACK 3/4] LFI - Local File Inclusion" -ForegroundColor Red
Write-Host "             Payload: ../../../../etc/passwd" -ForegroundColor DarkRed
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray

for ($i=1; $i -le 5; $i++) {
    Write-Host "  -> Request $i/5: LFI" -ForegroundColor Red
    Invoke-WebRequest "http://localhost/?page=../../../../etc/passwd" `
        -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
    Start-Sleep -Milliseconds 400
}
Write-Host "  [DONE] LFI attack sent" -ForegroundColor DarkRed
Write-Host ""

Start-Sleep -Seconds 1

# ============================================================
# ATTACK 4: Path Traversal
# ============================================================
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host "[ATTACK 4/4] Path Traversal" -ForegroundColor Red
Write-Host "             Payload: ../../../windows/system32/drivers/etc/hosts" -ForegroundColor DarkRed
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray

for ($i=1; $i -le 5; $i++) {
    Write-Host "  -> Request $i/5: Path Traversal" -ForegroundColor Red
    Invoke-WebRequest "http://localhost/?file=../../../windows/system32/drivers/etc/hosts" `
        -UseBasicParsing -ErrorAction SilentlyContinue | Out-Null
    Start-Sleep -Milliseconds 400
}
Write-Host "  [DONE] Path Traversal attack sent" -ForegroundColor DarkRed
Write-Host ""

# ============================================================
# WAIT FOR AUTO BLOCKER
# ============================================================
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host "  Total: 20 attack requests sent (4 types x 5 requests)" -ForegroundColor Cyan
Write-Host "  Waiting for Auto Blocker to detect and block IP..." -ForegroundColor Cyan
Write-Host "============================================================" -ForegroundColor Cyan
Write-Host ""

$blocked = $false
for ($wait=1; $wait -le 12; $wait++) {
    Start-Sleep -Seconds 10
    Write-Host "  Checking $wait/12 (${wait}0s elapsed)..." -ForegroundColor Gray

    $blacklist = docker exec project_waf_elk-waf-1 cat /etc/nginx/conf.d/blacklist.conf 2>$null
    if ($blacklist -match "deny") {
        Write-Host ""
        Write-Host "============================================================" -ForegroundColor Green
        Write-Host "  *** IP HAS BEEN AUTOMATICALLY BLOCKED! ***" -ForegroundColor Green
        Write-Host ""
        Write-Host "  Blacklist content:" -ForegroundColor Green
        Write-Host "  $blacklist" -ForegroundColor Yellow
        Write-Host "============================================================" -ForegroundColor Green
        $blocked = $true
        break
    }
}

if (-not $blocked) {
    Write-Host "  WARNING: IP not blocked yet. Check auto_blocker logs." -ForegroundColor Red
    Write-Host "  Run: docker-compose logs --tail=10 auto_blocker" -ForegroundColor Yellow
}

Write-Host ""
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host "  VERIFY RESULTS:" -ForegroundColor Cyan
Write-Host "  1. Browser  -> http://localhost       (should show 403)" -ForegroundColor White
Write-Host "  2. Kibana   -> http://localhost:5601  (dashboard)" -ForegroundColor White
Write-Host "  3. Unblock  -> run .\unblock.ps1" -ForegroundColor White
Write-Host "------------------------------------------------------------" -ForegroundColor DarkGray
Write-Host ""
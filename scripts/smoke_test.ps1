param(
    [string]$BaseUrl = 'http://localhost:8000'
)

function Check-Path($path) {
    try {
        $res = Invoke-WebRequest -Uri "$BaseUrl$path" -UseBasicParsing -TimeoutSec 10 -ErrorAction Stop
        if ($res.StatusCode -eq 200) {
            Write-Host "[OK] $path returned 200"
            return $true
        } else {
            Write-Error "[FAIL] $path returned $($res.StatusCode)"
            return $false
        }
    } catch {
        Write-Error "[FAIL] $path error: $_"
        return $false
    }
}

Write-Host "Running smoke tests against $BaseUrl"
$ok = $true
$ok = (Check-Path '/login') -and $ok
$ok = (Check-Path '/dashboard') -and $ok

if (-not $ok) { exit 1 } else { exit 0 }

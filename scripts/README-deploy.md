Deployment scripts
==================

Files:
- `deploy.sh` — Bash deploy script for Linux servers.
- `deploy.ps1` — PowerShell deploy script for Windows servers.
- `smoke_test.sh` — Simple HTTP smoke test (Bash).
- `smoke_test.ps1` — Simple HTTP smoke test (PowerShell).

Usage (deploy):

1. Review and commit your branch, push to remote.
2. On the target server (staging/production), run:

```bash
# Bash example
cd /path/to/repo
./scripts/deploy.sh -e staging -b staging -r origin --seed

# PowerShell example
.
.\scripts\deploy.ps1 -Environment staging -Branch staging -Remote origin -RunSeed:$false
```

The scripts will pause and prompt you to run an Oracle backup manually before applying migrations.

Smoke tests (quick post-deploy check):

Bash:
```bash
./scripts/smoke_test.sh http://localhost:8000
```

PowerShell:
```powershell
.\scripts\smoke_test.ps1 -BaseUrl http://localhost:8000
```

Both smoke tests check that `/login` and `/dashboard` return HTTP 200. Customize as needed.

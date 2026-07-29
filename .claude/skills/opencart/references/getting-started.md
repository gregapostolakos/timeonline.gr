# Getting Started — Step by Step

## Prerequisites

- GitHub account in the organization
- Docker Desktop installed and running
- Git installed
- GitHub CLI installed (`brew install gh && gh auth login`) — needed for `setup-secrets.sh`

---

### A1 — SSH Key on the server

If the cPanel account doesn't have an SSH key yet, in **cPanel → Terminal**:

```bash
ssh-keygen -t rsa -b 4096 -f ~/.ssh/id_rsa -N ""
cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

Also authorize in cPanel UI: **Security → SSH Access → Manage SSH Keys → Authorize**.

Copy the private key to your Mac:
```bash
scp -P SSH_PORT cpaneluser@xxx.xxx.xxx.xxx:~/.ssh/id_rsa ~/.ssh/mykey
chmod 600 ~/.ssh/mykey
```

### A2 — Clone the new repo locally

```bash
git clone git@github.com:YOUR-ORG/client-site.git
cd client-site
```

### A3 — Configure `.env`

Edit `.env` with all production credentials:

```bash
# Docker
COMPOSE_PROJECT_NAME=clientsite       # no spaces, no dashes, no dots
NGINX_PORT=8000
PMA_PORT=8080
DB_NAME=opencart
DB_USER=opencart
DB_PASS=secret

# Production
SSH_USER=cpaneluser
SSH_HOST=xxx.xxx.xxx.xxx
SSH_PORT=10001
SSH_KEY=~/.ssh/mykey
PROD_DB_NAME=cpaneluser_opencart
PROD_DB_USER=cpaneluser_dbuser
PROD_DB_PASS=secretpass
PROD_URL=https://www.example.gr
PROD_PATH=/home/cpaneluser/public_html

# GitHub (from Step 1.1)
WORKFLOW_PAT=ghp_xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx

# Optional
SLACK_WEBHOOK=
```

### A4 — Push secrets to GitHub

One command:

```bash
chmod +x ./scripts/setup-secrets.sh
./scripts/setup-secrets.sh
```

This reads `.env` and pushes:
- **Secrets:** `SSH_KEY` (file content), `SSH_HOST`, `SSH_USER`, `REMOTE_PATH`, `DB_NAME`, `DB_USER`, `DB_PASS`, `WORKFLOW_PAT`
- **Variables:** `SITE_URL`, `SSH_PORT`, `PHP_VERSION`, `SLACK_WEBHOOK`

### A5 — Sync production files

Two options:

**Option 1 — Via GitHub Action (recommended):**
1. Actions → **"Sync Files from Production"** → Run workflow
2. Branch: `main` (or `develop` if you want to review first)
3. Wait — first run can take a few minutes

**Option 2 — Manual rsync locally:**
Skip and just clone production directly. Not preferred.

### A6 — Start Docker

```bash
docker compose up -d
```

Wait ~30 seconds for MariaDB.

### A7 — Pull production DB

```bash
./scripts/db-pull.sh
```

Does everything: dump production DB → import locally → URL replace → create storage dirs.

### A8 — Optional: Pull production images

```bash
./scripts/image-pull.sh
```

Excludes `image/cache/`. Large download — only if you need images locally.

### A9 — Fix `config.php` for Docker

Edit `config.php`:
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// HTTP
define('HTTP_SERVER', 'http://localhost:8000/');

// HTTPS
define('HTTPS_SERVER', 'http://localhost:8000/');

// DIR
define('DIR_APPLICATION', '/var/www/html/catalog/');
define('DIR_SYSTEM', '/var/www/html/system/');
define('DIR_IMAGE', '/var/www/html/image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/theme/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'db');
define('DB_USERNAME', 'opencart');
define('DB_PASSWORD', 'secret');
define('DB_DATABASE', 'opencart');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');
```

`admin/config.php`
```php
<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
// HTTP
define('HTTP_SERVER', 'http://localhost:8000/admin/');
define('HTTP_CATALOG', 'http://localhost:8000/');

// HTTPS
define('HTTPS_SERVER', 'http://localhost:8000/admin/');
define('HTTPS_CATALOG', 'http://localhost:8000/');

// DIR
define('DIR_APPLICATION', '/var/www/html/admin/');
define('DIR_SYSTEM', '/var/www/html/system/');
define('DIR_IMAGE', '/var/www/html/image/');
define('DIR_STORAGE', DIR_SYSTEM . 'storage/');
define('DIR_CATALOG', '/var/www/html/catalog/');
define('DIR_LANGUAGE', DIR_APPLICATION . 'language/');
define('DIR_TEMPLATE', DIR_APPLICATION . 'view/template/');
define('DIR_CONFIG', DIR_SYSTEM . 'config/');
define('DIR_CACHE', DIR_STORAGE . 'cache/');
define('DIR_DOWNLOAD', DIR_STORAGE . 'download/');
define('DIR_LOGS', DIR_STORAGE . 'logs/');
define('DIR_MODIFICATION', DIR_STORAGE . 'modification/');
define('DIR_SESSION', DIR_STORAGE . 'session/');
define('DIR_UPLOAD', DIR_STORAGE . 'upload/');

// DB
define('DB_DRIVER', 'mysqli');
define('DB_HOSTNAME', 'db');
define('DB_USERNAME', 'opencart');
define('DB_PASSWORD', 'secret');
define('DB_DATABASE', 'opencart');
define('DB_PORT', '3306');
define('DB_PREFIX', 'oc_');

// OpenCart API
define('OPENCART_SERVER', 'https://www.opencart.com/');
```

### A10 — Verify

- **Site:** http://localhost:8000
- **Admin:** http://localhost:8000/admin → Extensions → Modifications → **Refresh**
- **phpMyAdmin:** http://localhost:8080

---


### Updating workflow files in site repos

When you update `oc-workflow` repo (master template):
- Site repo → Actions → **"Sync from Template Repo"** → Run
- Pulls latest `.github/`, `scripts/`, `docker/`, `.claude/`, `.gitignore`
- Does NOT touch `docker-compose.yml`, `.env`, or OpenCart code

---

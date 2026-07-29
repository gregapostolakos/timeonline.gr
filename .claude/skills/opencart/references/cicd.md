# CI/CD Reference — GitHub Actions + Deploy

## Architecture

```
develop (local Docker)  ──→  main (production, manual deploy)
         │                          │
   commit + push           Actions → Deploy → rsync → smoke test
```

All workflows are **manual** (`workflow_dispatch`). Nothing runs automatically on push.

| Branch | Environment |
|--------|-------------|
| `develop` | Local Docker (Mac) |
| `main` | Production server (cPanel) — deployed only when you trigger Deploy workflow |

---

## Two Repos in this Setup

| Repo | Purpose |
|------|---------|
| `oc-default-template` | GitHub template — copies OpenCart + Journal + workflow when you "Use this template" |
| `oc-workflow` | Master copy of workflow files. Site repos pull from here via `sync-template` |

When you fix a workflow once → push to `oc-workflow` → run `sync-template` on each site repo to receive the update.

---

## Workflow Files

All workflows have `workflow_dispatch` trigger (manual run).

### `.github/workflows/deploy.yml`
Run when you want to deploy to production.

**Concurrency protection:** Only one deploy/rollback runs at a time. Queues if triggered during another deploy.

Two jobs:
- **check-config** — verifies `SSH_HOST` exists. If not, skips deploy (template repo safe).
- **deploy** — runs only if secrets configured:
  1. Checkout
  2. Setup SSH key from `SSH_KEY` secret
  3. rsync to server (with `--checksum`, **no `--delete`**)
  4. Smoke test — HTTP check on `SITE_URL` (200/301/302)
  5. Slack notify (if `SLACK_WEBHOOK` set)
  6. Fails workflow if smoke test fails — run Rollback manually

No DB backup/restore (deploy only touches files).

### `.github/workflows/deploy-preview.yml`
Preview what files would change without actually transferring. Uses `--dry-run` + `--checksum`. Output shows only files that would transfer.

### `.github/workflows/rollback.yml`
Inputs:
- `commits` — how many commits to revert (default 1)
- `confirm` — must type `rollback`

Does: `git revert N commits` (handles merge commits with `-m 1`) → push → rsync → smoke test → Slack notify.

Same concurrency group as deploy.

### `.github/workflows/sync-files.yml`
Pull production files into the repo. Inputs:
- `branch` — `develop` or `main` (default `develop`)
- `force` — override conflict detection (default false)

Steps:
1. Checkout target branch (full history)
2. Setup SSH key
3. rsync from production (with `--checksum`)
4. **Conflict detection** — flags files with non-`sync:` recent commits; abort unless `force`
5. Commit with dynamic author (`github.actor`) + push to target branch
6. Create `develop` branch if missing

### `.github/workflows/sync-template.yml`
Pull workflow files from `oc-workflow` master repo into this site repo. Inputs:
- `template_repo` (default `webartstudiogr/oc-workflow`)
- `template_branch` (default `main`)

Steps:
1. Checkout current site repo
2. Checkout `oc-workflow` (using `WORKFLOW_PAT` if private, else `GITHUB_TOKEN`)
3. rsync template files: `.github/`, `scripts/`, `docker/`, `.claude/`, `.gitignore`
4. Commit + push (skips `docker-compose.yml`, `.env`, OpenCart code — those are per-project)

---

## Rsync Excludes

### Deploy (repo → server)
- `.git/`, `.github/`, `.claude/`
- `config.php`, `admin/config.php`, `config-dist.php`, `admin/config-dist.php`
- `.env`, `.gitignore`
- `system/storage/` (entire — never touches server's cache/sessions/vendor/uploads)
- `image/` (never touches production images)
- `docker/`, `docker-compose.yml`
- `scripts/`
- `install/`, `/.htaccess`, `/.htaccess.txt`
- `.user.ini`, `php.ini`
- `README.md`, `error_log`
- `catalog/view/theme/journal3/assets/.gitkeep`

### Sync Files (server → repo)
- `system/storage/cache/*`, `session/*`, `logs/*`, `download/*`, `upload/*`, `modification/*`
- `image/` (use `scripts/image-pull.sh` separately)
- `.git/`, `.github/`, `.claude/`
- `docker/`, `docker-compose.yml`, `.env`, `.gitignore`
- `scripts/`

`system/storage/vendor/` is **not** excluded → comes with sync (needed for local OC to run).

---

## GitHub Secrets & Variables

### Secrets (encrypted)

| Secret | Description | Example |
|--------|-------------|---------|
| `SSH_KEY` | Private SSH key content (entire `-----BEGIN...END-----` block) | `-----BEGIN OPENSSH PRIVATE KEY-----...` |
| `SSH_HOST` | Server IP or hostname | `xxx.xxx.xxx.xxx` |
| `SSH_USER` | cPanel username | `cpaneluser` |
| `REMOTE_PATH` | Absolute path on server | `/home/cpaneluser/public_html` |
| `DB_NAME` | Production database name | `cpaneluser_opencart` |
| `DB_USER` | Production database user | `cpaneluser_dbuser` |
| `DB_PASS` | Production database password | `secretpass` |
| `WORKFLOW_PAT` | PAT with `repo` + `workflow` scopes (for sync-template + rollback workflow changes) | `ghp_...` |

### Variables (not encrypted)

| Variable | Description | Default |
|----------|-------------|---------|
| `SITE_URL` | URL for smoke test, no trailing slash | *(required)* |
| `SSH_PORT` | SSH port | `22` |
| `PHP_VERSION` | PHP version | `8.3` |
| `SLACK_WEBHOOK` | Slack webhook URL (optional) | — |

### Pushing secrets — use the script

Instead of adding secrets manually in GitHub UI, run:

```bash
./scripts/setup-secrets.sh
```

It reads `.env` and pushes everything via `gh` CLI. Requires `gh auth login` first.

### Organization permissions
Once per organization: **github.com/ORG → Settings → Actions → General → Workflow permissions → "Read and write permissions"**.

---

## SSH Key Setup

Generate on the **server** (correct permissions), no passphrase:

```bash
# cPanel Terminal
ssh-keygen -t rsa -b 4096 -f ~/.ssh/id_rsa -N ""
cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
chmod 700 ~/.ssh
chmod 600 ~/.ssh/authorized_keys
```

Also authorize in cPanel UI: **Security → SSH Access → Manage SSH Keys → Authorize**.

Copy private key to local Mac:
```bash
scp -P SSH_PORT cpaneluser@SERVER_IP:~/.ssh/id_rsa ~/.ssh/mykey
chmod 600 ~/.ssh/mykey
```

In `.env`: `SSH_KEY=~/.ssh/mykey`

---

## Personal Access Token (PAT)

Required for:
- `sync-template` workflow (pulls from `oc-workflow` repo)
- `rollback` workflow (push includes workflow file changes)

Create once per developer:

1. **github.com → Settings → Developer settings → Personal access tokens → Tokens (classic)**
2. **Generate new token (classic)**
3. Scopes: `repo` + `workflow`
4. No expiration (or 1 year)
5. Copy token → paste into `.env` as `WORKFLOW_PAT`

`setup-secrets.sh` will push it to GitHub Secrets.

---

## Local Scripts

All scripts read settings from `.env`. Must run from project root.

### `scripts/setup-secrets.sh`
Reads `.env` → pushes to GitHub Secrets/Variables via `gh` CLI.

```bash
./scripts/setup-secrets.sh
```

Pushes:
- **Secrets:** SSH_KEY (file content), SSH_HOST, SSH_USER, REMOTE_PATH, DB_NAME, DB_USER, DB_PASS, WORKFLOW_PAT
- **Variables:** SITE_URL, SSH_PORT, PHP_VERSION, SLACK_WEBHOOK

Prerequisites: `brew install gh && gh auth login`.

### `scripts/db-pull.sh`
Pull production DB to local Docker.

```bash
./scripts/db-pull.sh
```

Steps:
1. Validate env + SSH key + Docker container
2. Confirmation prompt
3. SSH to production → `mysqldump` + gzip streamed → local
4. Drop & recreate local Docker DB → import
5. Auto-detect table prefix
6. Global URL replace across all text columns (both `https://` and `http://`)
7. Create storage dirs + set permissions

**Note:** After import, Admin → Extensions → Modifications → Refresh.

### `scripts/journal-push.sh`
Push local Journal config to production (zero-downtime atomic swap).

```bash
./scripts/journal-push.sh
```

Steps:
1. Validate env + SSH key + Docker container
2. Strong confirmation (must type `push`)
3. Auto-detect Journal tables (excludes customer data)
4. Backup production Journal tables to `~/backups/journal_pre_push_backup.sql.gz` on server
5. Dump local Journal tables → upload → import into `_tmp` tables
6. Atomic `RENAME TABLE` swap → production replaced in 1ms
7. Drop old tables → flush Journal cache on server
8. Auto-cleanup of temp tables on any failure (trap ERR)

Excluded (customer data, never overwritten):
- `oc_journal3_blog_comments`
- `oc_journal3_newsletter`
- `oc_journal3_message`

### `scripts/image-pull.sh`
Pull production images to local (excludes cache).

```bash
./scripts/image-pull.sh
```

---

## New Site Checklist

- [ ] Create repo from `oc-default-template` (Use this template)
- [ ] SSH key on server (no passphrase) + cPanel authorized
- [ ] Copy SSH private key to local Mac
- [ ] Clone repo locally
- [ ] Edit `.env` (Docker + production + WORKFLOW_PAT)
- [ ] `./scripts/setup-secrets.sh` (pushes everything to GitHub)
- [ ] Actions → "Sync Files from Production" (for existing sites)
- [ ] `docker compose up -d`
- [ ] `./scripts/db-pull.sh` (for existing sites)
- [ ] Edit `config.php` + `admin/config.php` for Docker
- [ ] Verify locally at `http://localhost:8000`
- [ ] First Actions → "Deploy to Production" → verify success

---

## Updating Workflow Files Across Sites

When you fix/improve a workflow in `oc-workflow`:

1. Push changes to `oc-workflow` repo
2. In each site repo: Actions → **"Sync from Template Repo"** → Run
3. Latest `.github/`, `scripts/`, `docker/`, `.claude/`, `.gitignore` copied over

Per-project files NOT touched: `docker-compose.yml`, `.env`, OpenCart code, README.

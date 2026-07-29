---
name: opencart
description: OpenCart 3 + Journal 3.2.5 development skill. Use when working on any OpenCart project — setup, module creation, extensions, Journal theme, Docker, Git workflow, or debugging.
user-invocable: true
allowed-tools: Read Grep Glob Edit Write Bash
---

# OpenCart 3 + Journal 3.2.5 — Development Skill

## Stack Overview

| Layer | Technology |
|-------|-----------|
| CMS | OpenCart 3.x |
| Theme | Journal 3.2.5 |
| PHP | 8.2 / 8.3 (primary), 7.3 / 7.4 (legacy sites) |
| DB | MariaDB 10.11 |
| Local dev | Docker (docker-compose) |
| Version control | Git — GitHub template repo, one repo per site |
| CI/CD | GitHub Actions — rsync deploy with lint + auto-rollback on merge to main |
| Extensions | 95% custom-built, some modified 3rd-party |

---

## Agent Rules

These rules apply in every session, without exception:

1. **Protected reference files** — The following files must NEVER be modified by the agent unless the user explicitly requests it:
   - `references/docker.md`
   - `references/journal.md`
   - `references/opencart.md`
   - `references/cicd.md`
   - `references/module-creation.md`

2. **Change log** — Every change made to any project file (code, config, templates, SQL) must be recorded in `references/changelog.md`. Each entry must include: date, file(s) changed, and a short description of what was changed and why. This applies to all projects using this skill.

3. **Keep it general** — This is a default template used across many projects. All skill files stay project-agnostic. Project-specific details belong in the project's own files, not here.

---

## How It Works — Full Flow

This repo (`oc-default-template`) is a **GitHub Template Repository**. It contains all the tooling (Docker, workflows, scripts, skill files) plus a base OpenCart 3 + Journal 3.2.5 install.

### Two setup paths

**Path A — Import existing production site:**
```
oc-default-template (this repo)
        |
        |  "Use this template" → new repo → Secrets → Run "Sync Files"
        v
client-site repo (template + production files)
        |
        |  git clone → docker compose up → ./scripts/db-pull.sh
        v
Local dev environment (http://localhost:8000)
```

**Path B — Fresh new site:**
```
oc-default-template → new repo → git clone
        |
        |  docker compose up → browser /install/ → configure Journal
        v
Fresh OpenCart + Journal local install
```

### Day-to-day development

```
develop branch (local Docker)
        |
        |  work on feature → commit → push
        v
        (no auto checks on develop)
        |
        |  merge develop → main
        v
Actions → Deploy → rsync → smoke test
        |
        |  if smoke test fails → workflow fails. Run Rollback manually.
        v
Production server updated
```

All deploys are **manual** (run from Actions tab). Nothing runs automatically on push.

### Two-repo setup

| Repo | Purpose |
|------|---------|
| `oc-default-template` | "Use this template" → creates new site repos (full OpenCart + Journal + tooling) |
| `oc-workflow` | Master copy of workflow files. Site repos pull updates via `sync-template` |

### Scripts (all read settings from `.env`)

| Script | What it does | When to use |
|--------|-------------|-------------|
| `scripts/setup-secrets.sh` | Push `.env` values to GitHub Secrets/Variables via `gh` CLI | Once per new site repo |
| `scripts/db-pull.sh` | Pull production DB to local Docker + URL replace | Need fresh production data locally |
| `scripts/journal-push.sh` | Push local Journal config to production (zero-downtime) | After changing Journal settings locally |
| `scripts/image-pull.sh` | Pull production images to local (excludes cache) | When you need images locally |

Each script validates env vars + SSH key + Docker container before running, and asks for confirmation before touching any DB.

→ Detailed step-by-step setup guide: [references/getting-started.md](references/getting-started.md)

---

## Docker Compose Structure

Each site uses a `docker-compose.yml` at the project root with four standard services:

- **php** — PHP-FPM (custom build; version matches production)
- **nginx** — proxies to php-fpm, handles SEO URLs
- **db** — MariaDB 10.11
- **phpmyadmin** — `http://localhost:8080`

All settings come from `.env` (container names, ports, DB credentials).

→ Full templates for all PHP versions: [references/docker.md](references/docker.md)

---

## Git Workflow

### What's committed
The entire OpenCart installation + all tooling. The `.gitignore` excludes only:
- `config.php`, `admin/config.php` (environment-specific)
- `system/storage/cache/`, `session/`, `logs/`
- `/image/cache/`
- `.DS_Store`, `opencart-skill.zip`

**Committed to GitHub but NEVER deployed to server** (rsync excludes):
`.github/`, `.claude/`, `docker/`, `docker-compose.yml`, `.env`, `scripts/`, `install/`, `/.htaccess`

### Branch strategy
```
main        → production (auto-deploy: GitHub Actions triggers rsync via SSH)
develop     → local development (Docker)
feature/*   → individual features / fixes
hotfix/*    → urgent production fixes
```

### Commit message convention
```
[type]: short description

type: feat | fix | style | refactor | chore | docs
```

### CI/CD Pipeline

All workflows are **manual** (`workflow_dispatch`). Nothing auto-triggers on push.

```
develop branch       →  local Docker development
                          |
                          v
                  merge develop → main
                          |
                          v
              Actions → "Deploy to Production"  →  rsync + smoke test
                          |
                          v
              If smoke test fails  →  Actions → "Rollback Production"
```

### Workflow files (all manual)

| File | What it does |
|------|-------------|
| `deploy.yml` | rsync repo → server + smoke test + Slack notify |
| `deploy-preview.yml` | Dry-run rsync to preview file changes |
| `rollback.yml` | Revert N commits + rsync + smoke test |
| `sync-files.yml` | rsync server → repo (with conflict detection) |
| `sync-template.yml` | Pull workflow updates from `oc-workflow` master repo |

**Deploy does NOT use `--delete`** — never removes files from server. Production images and uploads are safe.

→ Full CI/CD reference, GitHub Secrets, scripts, checklist: [references/cicd.md](references/cicd.md)

---

## Module Creation

### Choose the pattern first

| Pattern | When | Storage |
|---------|------|---------|
| Single-instance | Global settings page, module always active | `oc_setting` |
| Multi-instance | Placed in layouts multiple times, each with own settings | `oc_module` |

### File locations
```
admin/
  controller/extension/module/<name>.php   ← settings page + install/uninstall
  model/extension/module/<name>.php        ← install() uninstall() + admin queries
  view/template/extension/module/<name>.twig
  language/en-gb/extension/module/<name>.php
  language/el-gr/extension/module/<name>.php

catalog/
  controller/extension/module/<name>.php   ← front-end output (fragment or full page)
  model/extension/module/<name>.php        ← front-end queries only
  view/theme/default/template/extension/module/<name>.twig
  language/en-gb/extension/module/<name>.php
  language/el-gr/extension/module/<name>.php
```

### Critical rules
- `install()` / `uninstall()` live in the **admin model** only — never in catalog
- Layout modules return an HTML fragment — **no header/footer**
- Full custom pages include header/footer
- Always include **both** `en-gb` and `el-gr` language files
- All admin URLs require `user_token`
- Always check `$this->user->hasPermission('modify', ...)` before writes

→ Full boilerplates, admin CRUD, multi-instance, AJAX, events, OCMOD: [references/module-creation.md](references/module-creation.md)

---

## Journal 3.2.5 Customization

### Where changes go
- **Journal Admin** — layout, modules, colors, fonts, Custom CSS/JS
- **Twig templates** — `catalog/view/theme/journal3/template/`
- **OCMOD** — PHP-level changes to controllers/models
- **Custom CSS** — Journal Admin → Custom CSS

No child theme. All changes go directly into Journal files.

### Journal config is stored in DB
Journal settings live in `oc_journal3_*` tables. Use `scripts/journal-push.sh` to push local Journal changes to production (zero-downtime atomic swap).

The script excludes customer-written tables (blog comments, newsletter signups, contact messages) — they are never overwritten.

### Cache clearing
| What | Where |
|------|-------|
| Journal cache | Extensions → Journal 3 → Settings → Clear Cache |
| OC cache | `system/storage/cache/` |
| OCMOD cache | Admin → Extensions → Modifications → Refresh |

→ Full Journal reference: [references/journal.md](references/journal.md)

---

## Database Conventions

- Always use `DB_PREFIX` constant (`DB_PREFIX . 'table_name'`)
- Use OpenCart's DB class: `$this->db->query()`, `$this->db->escape()`
- Never use raw PDO or mysqli directly in OC code
- `utf8mb4` charset for all new tables
- Always include `install()` and `uninstall()` in extension admin models

→ Core DB tables, model methods, query patterns: [references/opencart.md](references/opencart.md)

---

## Common Debugging

| Problem | First steps |
|---------|------------|
| Blank white page | Enable error display in `config.php`, check error log |
| OCMOD not applying | Admin → Extensions → Modifications → Refresh |
| Journal layout broken | Clear Journal cache + OC cache |
| 500 error in Docker | `docker compose logs php` |
| DB connection refused | Check `.env` and `config.php` match Docker service name (`db`) |
| Images not showing | Check permissions on `/image/` — 755/644 |
| Module not in Layouts | Must be installed via Admin → Extensions first |
| Wrong settings loading (multi-instance) | Read from `model/setting/module→getModule($module_id)`, not `$this->config` |
| Deploy fails lint | Fix PHP syntax errors shown in GitHub Actions output |
| Deploy fails smoke test | Auto-rollback runs; check Actions log + site |
| SSH `Permission denied` in workflow | Key has passphrase, or not authorized in cPanel SSH Access |

---

## Code Standards

- **PHP**: Follow OpenCart's coding style; no strict PSR-2 but be consistent
- **PHP 8.x**: Use typed properties and return types for new code; avoid deprecated functions
- **Twig**: Keep logic minimal — move conditionals to controller
- **SQL**: Always escape via `$this->db->escape()`
- **No hardcoded strings**: All user-facing text in language files (both `en-gb` and `el-gr`)
- **No hardcoded URLs**: Use `HTTP_SERVER` or OC URL class

---

## Reference Files

| File | Read when |
|------|-----------|
| [references/getting-started.md](references/getting-started.md) | Step-by-step guide for new site — Path A (import) or Path B (fresh install) |
| [references/docker.md](references/docker.md) | Setting up or modifying Docker for any site |
| [references/opencart.md](references/opencart.md) | OC internals — MVC-L, events, models, checkout, mail, API, all extension types |
| [references/module-creation.md](references/module-creation.md) | Building any new module — boilerplate, admin CRUD, multi-instance, AJAX, OCMOD |
| [references/journal.md](references/journal.md) | Journal 3 modules, layout system, templates, DB tables |
| [references/cicd.md](references/cicd.md) | GitHub Actions, rsync deploy, Secrets setup, scripts, checklist |
| [references/changelog.md](references/changelog.md) | Log of all changes made across projects using this skill |

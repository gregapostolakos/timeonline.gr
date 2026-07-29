#!/bin/bash
# ============================================================
#  journal-push.sh — Push local Journal config to production
#  Zero-downtime using RENAME TABLE atomic swap
#  Reads all settings from .env
#
#  Usage: ./scripts/journal-push.sh
# ============================================================

set -e

# Load settings from .env
if [ ! -f ".env" ]; then
    echo "Error: .env file not found. Run from project root."
    exit 1
fi
source .env

# Validate required env vars
REQUIRED_VARS="COMPOSE_PROJECT_NAME DB_NAME DB_USER DB_PASS SSH_USER SSH_HOST SSH_PORT SSH_KEY PROD_DB_NAME PROD_DB_USER PROD_DB_PASS PROD_PATH"
MISSING=""
for VAR in $REQUIRED_VARS; do
    if [ -z "${!VAR}" ]; then
        MISSING="$MISSING $VAR"
    fi
done
if [ -n "$MISSING" ]; then
    echo "Error: Missing required .env variables:$MISSING"
    exit 1
fi

# Resolve ~ in SSH_KEY
SSH_KEY="${SSH_KEY/#\~/$HOME}"

if [ ! -f "$SSH_KEY" ]; then
    echo "Error: SSH key not found at $SSH_KEY"
    exit 1
fi

# Local Docker
LOCAL_CONTAINER="${COMPOSE_PROJECT_NAME}_db"

# Check Docker container is running
if ! docker ps --format '{{.Names}}' | grep -q "^${LOCAL_CONTAINER}$"; then
    echo "Error: Docker container '$LOCAL_CONTAINER' is not running."
    echo "Run: docker compose up -d"
    exit 1
fi

# Confirmation prompt — this touches PRODUCTION
echo ""
echo "================================================="
echo "  WARNING: PRODUCTION DATABASE WILL BE MODIFIED"
echo "================================================="
echo ""
echo "This will PUSH local Journal config to:"
echo "   Server     : $SSH_HOST"
echo "   DB         : $PROD_DB_NAME"
echo ""
echo "Preserved tables (customer data NOT overwritten):"
echo "   - journal3_blog_comments"
echo "   - journal3_newsletter"
echo "   - journal3_message"
echo ""
read -p "Type 'push' to continue: " -r
echo ""
if [ "$REPLY" != "push" ]; then
    echo "Aborted."
    exit 0
fi

DUMP_FILE="/tmp/journal_push_$(date +%Y%m%d_%H%M%S).sql.gz"

echo ""

# — Step 1: Detect prefix ———————————————————————————————————
echo "Step 1/6 — Detecting table prefix..."

DB_PREFIX=$(docker exec "$LOCAL_CONTAINER" \
    mariadb -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    -se "SHOW TABLES LIKE '%journal3_setting';" 2>/dev/null \
    | head -1 \
    | sed 's/journal3_setting$//')

if [ -z "$DB_PREFIX" ]; then
    echo "   Warning: prefix not found. Using 'oc_' as default."
    DB_PREFIX="oc_"
fi

echo "   Prefix: '${DB_PREFIX}'"

# — Step 2: Find Journal tables ————————————————————————————
echo ""
echo "Step 2/6 — Finding Journal tables..."

# These tables are written by customers — never overwritten
EXCLUDE_TABLES="${DB_PREFIX}journal3_blog_comments ${DB_PREFIX}journal3_newsletter ${DB_PREFIX}journal3_message"

ALL_TABLES=$(docker exec "$LOCAL_CONTAINER" \
    mariadb -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    -se "SHOW TABLES LIKE '${DB_PREFIX}journal3%';" 2>/dev/null)

JOURNAL_TABLES=""
for TABLE in $ALL_TABLES; do
    SKIP=0
    for EXCL in $EXCLUDE_TABLES; do
        if [ "$TABLE" = "$EXCL" ]; then
            SKIP=1
            break
        fi
    done
    if [ "$SKIP" -eq 0 ]; then
        JOURNAL_TABLES="$JOURNAL_TABLES $TABLE"
    fi
done
JOURNAL_TABLES=$(echo "$JOURNAL_TABLES" | xargs)

if [ -z "$JOURNAL_TABLES" ]; then
    echo "   No Journal tables found! Exiting."
    exit 1
fi

TABLE_COUNT=$(echo "$JOURNAL_TABLES" | wc -w | tr -d ' ')
echo "   Found $TABLE_COUNT Journal tables to sync"

# — Step 3: Backup production Journal tables ——————————————
echo ""
echo "Step 3/6 — Backing up production Journal tables..."

BACKUP_TABLE_LIST=$(echo "$JOURNAL_TABLES $EXCLUDE_TABLES" | xargs)

ssh -i "$SSH_KEY" -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" \
    "mkdir -p ~/backups && \
     mysqldump \
       --single-transaction \
       --quick \
       --lock-tables=false \
       -u '$PROD_DB_USER' \
       -p'$PROD_DB_PASS' \
       '$PROD_DB_NAME' $BACKUP_TABLE_LIST \
       | gzip > ~/backups/journal_pre_push_backup.sql.gz && \
     echo '   Backup saved: ~/backups/journal_pre_push_backup.sql.gz'"

# — Step 4: Dump local Journal tables ——————————————————————
echo ""
echo "Step 4/6 — Dumping local Journal tables..."

TABLE_LIST=$(echo "$JOURNAL_TABLES" | tr '\n' ' ')

docker exec "$LOCAL_CONTAINER" \
    mariadb-dump -u "$DB_USER" -p"$DB_PASS" \
    "$DB_NAME" $TABLE_LIST 2>/dev/null \
    | gzip > "$DUMP_FILE"

SIZE=$(du -sh "$DUMP_FILE" | cut -f1)
echo "   Dump OK ($SIZE compressed, $TABLE_COUNT tables)"

# — Step 5: Upload and atomic swap ————————————————————————
echo ""
echo "Step 5/6 — Uploading and importing to production (zero-downtime)..."

# Build SQL files LOCALLY (so backticks survive heredoc/ssh transit)
TS=$(date +%Y%m%d_%H%M%S)
SQL_CREATE="/tmp/journal_create_temps_${TS}.sql"
SQL_RENAME="/tmp/journal_rename_${TS}.sql"
SQL_DROP_OLD="/tmp/journal_drop_old_${TS}.sql"
SQL_DROP_TMP="/tmp/journal_drop_tmp_${TS}.sql"
DUMP_REWRITTEN="/tmp/journal_import_rewritten_${TS}.sql.gz"

# Build CREATE TEMPS file
> "$SQL_CREATE"
for TABLE in $JOURNAL_TABLES; do
    echo "DROP TABLE IF EXISTS \`${TABLE}_tmp\`;"          >> "$SQL_CREATE"
    echo "CREATE TABLE \`${TABLE}_tmp\` LIKE \`${TABLE}\`;" >> "$SQL_CREATE"
done

# Build RENAME file (atomic, single statement)
RENAME_PAIRS=""
for TABLE in $JOURNAL_TABLES; do
    RENAME_PAIRS="${RENAME_PAIRS}\`${TABLE}\` TO \`${TABLE}_old\`, \`${TABLE}_tmp\` TO \`${TABLE}\`, "
done
RENAME_PAIRS="${RENAME_PAIRS%, }"
echo "RENAME TABLE ${RENAME_PAIRS};" > "$SQL_RENAME"

# Build DROP OLD file
DROP_OLD_LIST=""
for TABLE in $JOURNAL_TABLES; do
    DROP_OLD_LIST="${DROP_OLD_LIST}\`${TABLE}_old\`, "
done
DROP_OLD_LIST="${DROP_OLD_LIST%, }"
echo "DROP TABLE IF EXISTS ${DROP_OLD_LIST};" > "$SQL_DROP_OLD"

# Build DROP TMP file (cleanup on error)
DROP_TMP_LIST=""
for TABLE in $JOURNAL_TABLES; do
    DROP_TMP_LIST="${DROP_TMP_LIST}\`${TABLE}_tmp\`, "
done
DROP_TMP_LIST="${DROP_TMP_LIST%, }"
echo "DROP TABLE IF EXISTS ${DROP_TMP_LIST};" > "$SQL_DROP_TMP"

# Rewrite dump LOCALLY: replace `tablename` with `tablename_tmp`
SED_EXPR=""
for TABLE in $JOURNAL_TABLES; do
    SED_EXPR="${SED_EXPR}s/\`${TABLE}\`/\`${TABLE}_tmp\`/g; "
done
gunzip -c "$DUMP_FILE" | sed "$SED_EXPR" | gzip > "$DUMP_REWRITTEN"

# Upload all files
echo "   Uploading SQL files..."
scp -i "$SSH_KEY" -P "$SSH_PORT" -q \
    "$SQL_CREATE" "$SQL_RENAME" "$SQL_DROP_OLD" "$SQL_DROP_TMP" "$DUMP_REWRITTEN" \
    "$SSH_USER@$SSH_HOST:/tmp/"

REMOTE_CREATE="/tmp/$(basename "$SQL_CREATE")"
REMOTE_RENAME="/tmp/$(basename "$SQL_RENAME")"
REMOTE_DROP_OLD="/tmp/$(basename "$SQL_DROP_OLD")"
REMOTE_DROP_TMP="/tmp/$(basename "$SQL_DROP_TMP")"
REMOTE_DUMP="/tmp/$(basename "$DUMP_REWRITTEN")"

# Run on production — with cleanup on failure (single quoted heredoc, vars passed via env)
ssh -i "$SSH_KEY" -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" \
    PROD_DB_USER="$PROD_DB_USER" \
    PROD_DB_PASS="$PROD_DB_PASS" \
    PROD_DB_NAME="$PROD_DB_NAME" \
    REMOTE_CREATE="$REMOTE_CREATE" \
    REMOTE_RENAME="$REMOTE_RENAME" \
    REMOTE_DROP_OLD="$REMOTE_DROP_OLD" \
    REMOTE_DROP_TMP="$REMOTE_DROP_TMP" \
    REMOTE_DUMP="$REMOTE_DUMP" \
    bash <<'REMOTE_SCRIPT'
set -e

cleanup_on_error() {
    echo "   ERROR: cleaning up temp tables..."
    mysql -u "$PROD_DB_USER" -p"$PROD_DB_PASS" "$PROD_DB_NAME" < "$REMOTE_DROP_TMP" 2>/dev/null || true
    rm -f "$REMOTE_CREATE" "$REMOTE_RENAME" "$REMOTE_DROP_OLD" "$REMOTE_DROP_TMP" "$REMOTE_DUMP"
    exit 1
}
trap cleanup_on_error ERR

# Step A: Create temp tables
mysql -u "$PROD_DB_USER" -p"$PROD_DB_PASS" "$PROD_DB_NAME" < "$REMOTE_CREATE"
echo "   Temp tables created"

# Step B: Import into temp tables
gunzip -c "$REMOTE_DUMP" | mysql -u "$PROD_DB_USER" -p"$PROD_DB_PASS" "$PROD_DB_NAME"
echo "   Data imported to temp tables"

# Step C: Atomic swap — zero downtime
mysql -u "$PROD_DB_USER" -p"$PROD_DB_PASS" "$PROD_DB_NAME" < "$REMOTE_RENAME"
echo "   Atomic swap done"

# Step D: Drop old tables
mysql -u "$PROD_DB_USER" -p"$PROD_DB_PASS" "$PROD_DB_NAME" < "$REMOTE_DROP_OLD"
echo "   Old tables dropped"

rm -f "$REMOTE_CREATE" "$REMOTE_RENAME" "$REMOTE_DROP_OLD" "$REMOTE_DROP_TMP" "$REMOTE_DUMP"
REMOTE_SCRIPT

# Local cleanup
rm -f "$SQL_CREATE" "$SQL_RENAME" "$SQL_DROP_OLD" "$SQL_DROP_TMP" "$DUMP_REWRITTEN"

echo "   Production DB updated"

# — Step 6: Flush cache —————————————————————————————————————
echo ""
echo "Step 6/6 — Flushing Journal cache on production..."

ssh -i "$SSH_KEY" -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" \
    "rm -rf $PROD_PATH/system/storage/cache/* && \
     echo '   Cache cleared'"

# — Cleanup ————————————————————————————————————————————————
rm -f "$DUMP_FILE"

echo ""
echo "================================================="
echo "  Done! Journal config pushed to production."
echo "  Tables updated: $TABLE_COUNT"
echo "  Method: zero-downtime (atomic RENAME swap)"
echo "  Backup: ~/backups/journal_pre_push_backup.sql.gz (on server)"
echo "================================================="
echo ""

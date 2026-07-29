#!/bin/bash
# ============================================================
#  db-pull.sh — Pull production DB to local Docker
#  Reads all settings from .env
#
#  Usage: ./scripts/db-pull.sh
# ============================================================

set -e

# Load settings from .env
if [ ! -f ".env" ]; then
    echo "Error: .env file not found. Run from project root."
    exit 1
fi
source .env

# Validate required env vars
REQUIRED_VARS="COMPOSE_PROJECT_NAME NGINX_PORT DB_NAME DB_USER DB_PASS SSH_USER SSH_HOST SSH_PORT SSH_KEY PROD_DB_NAME PROD_DB_USER PROD_DB_PASS PROD_URL"
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
LOCAL_URL="http://localhost:${NGINX_PORT}"

# Check Docker container is running
if ! docker ps --format '{{.Names}}' | grep -q "^${LOCAL_CONTAINER}$"; then
    echo "Error: Docker container '$LOCAL_CONTAINER' is not running."
    echo "Run: docker compose up -d"
    exit 1
fi

# Confirmation prompt
echo ""
echo "This will REPLACE the local database '$DB_NAME' with production data."
echo "   Production : $PROD_DB_NAME @ $SSH_HOST"
echo "   Local      : $DB_NAME @ $LOCAL_CONTAINER"
echo ""
read -p "Continue? (y/N): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 0
fi

DUMP_FILE="/tmp/db_pull_$(date +%Y%m%d_%H%M%S).sql.gz"

echo ""

# — Step 1: mysqldump from production ——————————————————————
echo "Step 1/4 — Dumping production DB..."
ssh -i "$SSH_KEY" -p "$SSH_PORT" "$SSH_USER@$SSH_HOST" \
    "mysqldump \
      --single-transaction \
      --quick \
      --lock-tables=false \
      -u '$PROD_DB_USER' \
      -p'$PROD_DB_PASS' \
      '$PROD_DB_NAME' | gzip" \
    > "$DUMP_FILE"

SIZE=$(du -sh "$DUMP_FILE" | cut -f1)
echo "   Dump OK ($SIZE compressed)"

# — Step 2: Import to local Docker ————————————————————————
echo ""
echo "Step 2/4 — Importing to local Docker DB..."

docker exec "$LOCAL_CONTAINER" \
    mariadb -u root -proot \
    -e "DROP DATABASE IF EXISTS \`$DB_NAME\`; CREATE DATABASE \`$DB_NAME\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

gunzip -c "$DUMP_FILE" | docker exec -i "$LOCAL_CONTAINER" \
    mariadb -u "$DB_USER" -p"$DB_PASS" "$DB_NAME"

echo "   Import OK"

# — Step 3: Detect table prefix ———————————————————————————
echo ""
echo "Step 3/4 — Detecting table prefix..."

DB_PREFIX=$(docker exec "$LOCAL_CONTAINER" \
    mariadb -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    -se "SHOW TABLES LIKE '%setting';" 2>/dev/null \
    | head -1 \
    | sed 's/setting$//')
DB_PREFIX="${DB_PREFIX:-oc_}"

echo "   Prefix: '$DB_PREFIX'"

# — Step 4: URL replacement ———————————————————————————————
echo ""
echo "Step 4/4 — Replacing URLs ($PROD_URL -> $LOCAL_URL)..."

REPLACE_SQL=$(docker exec "$LOCAL_CONTAINER" \
    mariadb -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
    -se "
    SELECT CONCAT(
        'UPDATE \`', TABLE_NAME, '\` SET \`', COLUMN_NAME, '\` = ',
        'REPLACE(\`', COLUMN_NAME, '\`, ',
        '''$PROD_URL'', ',
        '''$LOCAL_URL'') ',
        'WHERE \`', COLUMN_NAME, '\` LIKE ''%$PROD_URL%'';'
    )
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = '$DB_NAME'
    AND DATA_TYPE IN ('varchar','text','mediumtext','longtext','tinytext')
    ORDER BY TABLE_NAME, COLUMN_NAME;
" 2>/dev/null)

REPLACED=0
while IFS= read -r sql; do
    if [ -n "$sql" ]; then
        docker exec "$LOCAL_CONTAINER" \
            mariadb -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
            -se "$sql" 2>/dev/null || true
        REPLACED=$((REPLACED + 1))
    fi
done <<< "$REPLACE_SQL"

# Handle http variant
PROD_URL_HTTP=$(echo "$PROD_URL" | sed 's|https://|http://|')
if [ "$PROD_URL_HTTP" != "$PROD_URL" ]; then
    REPLACE_HTTP=$(docker exec "$LOCAL_CONTAINER" \
        mariadb -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
        -se "
        SELECT CONCAT(
            'UPDATE \`', TABLE_NAME, '\` SET \`', COLUMN_NAME, '\` = ',
            'REPLACE(\`', COLUMN_NAME, '\`, ',
            '''$PROD_URL_HTTP'', ',
            '''$LOCAL_URL'') ',
            'WHERE \`', COLUMN_NAME, '\` LIKE ''%$PROD_URL_HTTP%'';'
        )
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = '$DB_NAME'
        AND DATA_TYPE IN ('varchar','text','mediumtext','longtext','tinytext')
        ORDER BY TABLE_NAME, COLUMN_NAME;
    " 2>/dev/null)

    while IFS= read -r sql; do
        if [ -n "$sql" ]; then
            docker exec "$LOCAL_CONTAINER" \
                mariadb -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" \
                -se "$sql" 2>/dev/null || true
        fi
    done <<< "$REPLACE_HTTP"
fi

echo "   URL replaced in $REPLACED DB columns"

echo ""
echo "   NOTE: Go to Admin → Extensions → Modifications → Refresh"
echo "   to rebuild OCMOD cache (required after DB import)"

# — Cleanup ———————————————————————————————————————————————
rm -f "$DUMP_FILE"

echo ""
echo "================================================="
echo "  Done! Local environment is ready."
echo ""
echo "  Site  : $LOCAL_URL"
echo "  Admin : $LOCAL_URL/admin"
echo "  DB    : phpMyAdmin at http://localhost:$PMA_PORT"
echo "================================================="
echo ""

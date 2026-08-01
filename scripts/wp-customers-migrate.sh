#!/bin/bash
# ============================================================
#  wp-customers-migrate.sh — migrate WordPress customers to OpenCart
#
#  Exports every WordPress account with the "customer" role from the
#  production WordPress database and imports it into OpenCart, together
#  with the WordPress password hash. Addresses are NOT migrated.
#
#  Hashes cannot be converted between the two systems, so each customer
#  keeps their WordPress hash in <prefix>customer_wp_password until their
#  first login, when the "WordPress Password Bridge" modification verifies
#  it and re-hashes the password into OpenCart's own format.
#
#  Usage:
#    ./scripts/wp-customers-migrate.sh [--dry-run] [--target=local|prod]
#                                      [--include-2024-spam]
#
#  Reads settings from .env (WP_DB_* plus the usual SSH_* / DB_* values).
# ============================================================

set -e

DRY_RUN=""
TARGET="local"
SPAM_FILTER=1

for ARG in "$@"; do
    case "$ARG" in
        --dry-run)            DRY_RUN="--dry-run" ;;
        --target=local)       TARGET="local" ;;
        --target=prod)        TARGET="prod" ;;
        --include-2024-spam)  SPAM_FILTER=0 ;;
        *) echo "Unknown option: $ARG"; exit 1 ;;
    esac
done

if [ ! -f ".env" ]; then
    echo "Error: .env file not found. Run from project root."
    exit 1
fi
source .env

REQUIRED_VARS="COMPOSE_PROJECT_NAME DB_NAME DB_USER DB_PASS SSH_USER SSH_HOST SSH_PORT SSH_KEY WP_DB_NAME WP_DB_USER WP_DB_PASS"
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

WP_PREFIX="${WP_TABLE_PREFIX:-wp_}"
OC_PREFIX="${OC_TABLE_PREFIX:-oc_}"
CUSTOMER_GROUP_ID="${OC_CUSTOMER_GROUP_ID:-1}"
LANGUAGE_ID="${OC_LANGUAGE_ID:-2}"

SSH_KEY="${SSH_KEY/#\~/$HOME}"
if [ ! -f "$SSH_KEY" ]; then
    echo "Error: SSH key not found at $SSH_KEY"
    exit 1
fi

SSH="ssh -i $SSH_KEY -p $SSH_PORT $SSH_USER@$SSH_HOST"

# The May–June 2024 bot registrations came in without a name and with legacy
# $P$ hashes; the same wave filled OpenCart with ~2,000 "Customer" accounts.
if [ "$SPAM_FILTER" = "1" ]; then
    SPAM_CLAUSE="AND NOT (u.user_registered >= '2024-01-01' AND u.user_registered < '2025-01-01' AND COALESCE(fn.meta_value, '') = '')"
else
    SPAM_CLAUSE=""
fi

echo ""
echo "  WordPress  : $WP_DB_NAME @ $SSH_HOST"
if [ "$TARGET" = "prod" ]; then
    echo "  OpenCart   : $PROD_DB_NAME @ $SSH_HOST (PRODUCTION)"
else
    echo "  OpenCart   : $DB_NAME @ ${COMPOSE_PROJECT_NAME}_db (local Docker)"
fi
echo "  Spam filter: $([ "$SPAM_FILTER" = "1" ] && echo 'on (2024 bot accounts excluded)' || echo 'OFF')"
echo "  Mode       : $([ -n "$DRY_RUN" ] && echo 'dry run' || echo 'WRITE')"
echo ""

if [ -z "$DRY_RUN" ]; then
    read -p "Continue? (y/N): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        echo "Aborted."
        exit 0
    fi
fi

DUMP_FILE=$(mktemp "${TMPDIR:-/tmp}/wp_customers_XXXXXX.hex")
trap 'rm -f "$DUMP_FILE"' EXIT

# — Step 1: export customers from WordPress ————————————————————
# One hex-encoded JSON object per line, so names and hashes survive
# transport without any quoting or escaping surprises.
echo "Step 1/2 — Exporting customers from WordPress..."

EXPORT_SQL="
SELECT HEX(JSON_OBJECT(
    'email',      u.user_email,
    'firstname',  COALESCE(fn.meta_value, ''),
    'lastname',   COALESCE(ln.meta_value, ''),
    'hash',       u.user_pass,
    'date_added', DATE_FORMAT(u.user_registered, '%Y-%m-%d %H:%i:%s')
))
FROM ${WP_PREFIX}users u
INNER JOIN ${WP_PREFIX}usermeta cap
        ON cap.user_id = u.ID AND cap.meta_key = '${WP_PREFIX}capabilities'
LEFT JOIN ${WP_PREFIX}usermeta fn
       ON fn.user_id = u.ID AND fn.meta_key = 'first_name'
LEFT JOIN ${WP_PREFIX}usermeta ln
       ON ln.user_id = u.ID AND ln.meta_key = 'last_name'
WHERE cap.meta_value LIKE '%\"customer\"%'
  AND u.user_email <> ''
  AND u.user_pass <> ''
  ${SPAM_CLAUSE}
ORDER BY u.ID;
"

$SSH "mysql -h 127.0.0.1 -u '$WP_DB_USER' -p'$WP_DB_PASS' '$WP_DB_NAME' -N --batch -e \"$EXPORT_SQL\"" > "$DUMP_FILE"

COUNT=$(grep -c . "$DUMP_FILE" || true)
echo "   Exported $COUNT customers"

if [ "$COUNT" -eq 0 ]; then
    echo "Error: no customers exported — aborting."
    exit 1
fi

# — Step 2: import into OpenCart ————————————————————————————————
echo ""
echo "Step 2/2 — Importing into OpenCart..."

if [ "$TARGET" = "prod" ]; then
    REMOTE_SCRIPT="/tmp/wp-customers-import-$$.php"
    REMOTE_DATA="/tmp/wp-customers-data-$$.hex"

    scp -q -i "$SSH_KEY" -P "$SSH_PORT" scripts/wp-customers-import.php "$SSH_USER@$SSH_HOST:$REMOTE_SCRIPT"
    scp -q -i "$SSH_KEY" -P "$SSH_PORT" "$DUMP_FILE" "$SSH_USER@$SSH_HOST:$REMOTE_DATA"

    $SSH "OC_DB_HOST=127.0.0.1 \
          OC_DB_USER='$PROD_DB_USER' \
          OC_DB_PASS='$PROD_DB_PASS' \
          OC_DB_NAME='$PROD_DB_NAME' \
          OC_DB_PREFIX='$OC_PREFIX' \
          OC_CUSTOMER_GROUP_ID='$CUSTOMER_GROUP_ID' \
          OC_LANGUAGE_ID='$LANGUAGE_ID' \
          php $REMOTE_SCRIPT $DRY_RUN < $REMOTE_DATA; \
          rm -f $REMOTE_SCRIPT $REMOTE_DATA"
else
    LOCAL_CONTAINER="${COMPOSE_PROJECT_NAME}_php"

    if ! docker ps --format '{{.Names}}' | grep -q "^${LOCAL_CONTAINER}$"; then
        echo "Error: Docker container '$LOCAL_CONTAINER' is not running."
        echo "Run: docker compose up -d"
        exit 1
    fi

    docker exec -i \
        -e OC_DB_HOST=db \
        -e OC_DB_USER="$DB_USER" \
        -e OC_DB_PASS="$DB_PASS" \
        -e OC_DB_NAME="$DB_NAME" \
        -e OC_DB_PREFIX="$OC_PREFIX" \
        -e OC_CUSTOMER_GROUP_ID="$CUSTOMER_GROUP_ID" \
        -e OC_LANGUAGE_ID="$LANGUAGE_ID" \
        "$LOCAL_CONTAINER" \
        php /var/www/html/scripts/wp-customers-import.php $DRY_RUN < "$DUMP_FILE"
fi

echo ""
echo "  NOTE: Admin -> Extensions -> Modifications -> Refresh"
echo "  is required so the WordPress Password Bridge is applied."
echo ""

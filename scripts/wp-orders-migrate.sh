#!/bin/bash
# ============================================================
#  wp-orders-migrate.sh — migrate WooCommerce orders to OpenCart
#
#  Exports WooCommerce orders placed on/after --since from the production
#  WordPress database and imports them into OpenCart, with line items,
#  totals, addresses and status history.
#
#  Orders are tagged `comment = WC#<id>` in oc_order. That marker is the
#  idempotency key — re-running never duplicates an order, so an overlapping
#  --since is harmless.
#
#  Usage:
#    ./scripts/wp-orders-migrate.sh --since=YYYY-MM-DD
#                                   [--dry-run] [--target=local|prod]
#
#  Reads settings from .env (WP_DB_* plus the usual SSH_* / DB_* values).
# ============================================================

set -e

DRY_RUN=""
TARGET="local"
SINCE=""

for ARG in "$@"; do
    case "$ARG" in
        --dry-run)      DRY_RUN="--dry-run" ;;
        --target=local) TARGET="local" ;;
        --target=prod)  TARGET="prod" ;;
        --since=*)      SINCE="${ARG#--since=}" ;;
        *) echo "Unknown option: $ARG"; exit 1 ;;
    esac
done

if [ -z "$SINCE" ]; then
    echo "Error: --since=YYYY-MM-DD is required (orders placed on/after this date)."
    exit 1
fi

if ! [[ "$SINCE" =~ ^[0-9]{4}-[0-9]{2}-[0-9]{2}$ ]]; then
    echo "Error: --since must be YYYY-MM-DD, got '$SINCE'."
    exit 1
fi

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
LANGUAGE_ID="${OC_LANGUAGE_ID:-2}"
CURRENCY_ID="${OC_ORDER_CURRENCY_ID:-3}"
CUSTOMER_GROUP_ID="${OC_CUSTOMER_GROUP_ID:-1}"
STORE_NAME="${OC_STORE_NAME:-TimeOnline}"
STORE_URL="${OC_STORE_URL:-https://timeonline.gr}"
INVOICE_PREFIX="${OC_INVOICE_PREFIX:-INV-}"

SSH_KEY="${SSH_KEY/#\~/$HOME}"
if [ ! -f "$SSH_KEY" ]; then
    echo "Error: SSH key not found at $SSH_KEY"
    exit 1
fi

SSH="ssh -i $SSH_KEY -p $SSH_PORT $SSH_USER@$SSH_HOST"

echo ""
echo "  WordPress  : $WP_DB_NAME @ $SSH_HOST"
if [ "$TARGET" = "prod" ]; then
    echo "  OpenCart   : $PROD_DB_NAME @ $SSH_HOST (PRODUCTION)"
else
    echo "  OpenCart   : $DB_NAME @ ${COMPOSE_PROJECT_NAME}_db (local Docker)"
fi
echo "  Since      : $SINCE"
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

DUMP_FILE=$(mktemp "${TMPDIR:-/tmp}/wp_orders_XXXXXX.hex")
trap 'rm -f "$DUMP_FILE"' EXIT

# — Step 1: export orders + line items ————————————————————————
# Two record types in one stream, each a hex-encoded JSON object, so names
# and addresses survive transport without any quoting surprises.
echo "Step 1/2 — Exporting orders from WooCommerce..."

EXPORT_SQL="
SELECT HEX(JSON_OBJECT(
    'type',          'order',
    'wc_id',         o.ID,
    'status',        o.post_status,
    'date_added',    DATE_FORMAT(o.post_date, '%Y-%m-%d %H:%i:%s'),
    'date_modified', DATE_FORMAT(o.post_modified, '%Y-%m-%d %H:%i:%s'),
    'meta',          JSON_OBJECTAGG(m.meta_key, COALESCE(m.meta_value, ''))
))
FROM ${WP_PREFIX}posts o
INNER JOIN ${WP_PREFIX}postmeta m ON m.post_id = o.ID
WHERE o.post_type = 'shop_order'
  AND o.post_date >= '${SINCE} 00:00:00'
  AND m.meta_key IN (
    '_billing_first_name','_billing_last_name','_billing_company',
    '_billing_address_1','_billing_address_2','_billing_city',
    '_billing_postcode','_billing_country','_billing_state',
    '_billing_email','_billing_phone',
    '_shipping_first_name','_shipping_last_name','_shipping_company',
    '_shipping_address_1','_shipping_address_2','_shipping_city',
    '_shipping_postcode','_shipping_country','_shipping_state',
    '_order_total','_order_shipping','_order_tax','_order_shipping_tax',
    '_cart_discount','_payment_method','_payment_method_title',
    '_customer_user','_order_currency','_customer_ip_address','_customer_user_agent'
  )
GROUP BY o.ID, o.post_status, o.post_date, o.post_modified
ORDER BY o.ID;

SELECT HEX(JSON_OBJECT(
    'type',       'item',
    'order_id',   oi.order_id,
    'item_id',    oi.order_item_id,
    'name',       oi.order_item_name,
    'qty',        COALESCE(q.meta_value, '1'),
    'line_total', COALESCE(lt.meta_value, '0'),
    'sku',        COALESCE(NULLIF(vsku.meta_value, ''), NULLIF(psku.meta_value, ''), '')
))
FROM ${WP_PREFIX}woocommerce_order_items oi
INNER JOIN ${WP_PREFIX}posts o
        ON o.ID = oi.order_id
       AND o.post_type = 'shop_order'
       AND o.post_date >= '${SINCE} 00:00:00'
LEFT JOIN ${WP_PREFIX}woocommerce_order_itemmeta q
       ON q.order_item_id = oi.order_item_id AND q.meta_key = '_qty'
LEFT JOIN ${WP_PREFIX}woocommerce_order_itemmeta lt
       ON lt.order_item_id = oi.order_item_id AND lt.meta_key = '_line_total'
LEFT JOIN ${WP_PREFIX}woocommerce_order_itemmeta pid
       ON pid.order_item_id = oi.order_item_id AND pid.meta_key = '_product_id'
LEFT JOIN ${WP_PREFIX}woocommerce_order_itemmeta vid
       ON vid.order_item_id = oi.order_item_id AND vid.meta_key = '_variation_id'
LEFT JOIN ${WP_PREFIX}postmeta vsku
       ON vsku.post_id = vid.meta_value AND vsku.meta_key = '_sku'
LEFT JOIN ${WP_PREFIX}postmeta psku
       ON psku.post_id = pid.meta_value AND psku.meta_key = '_sku'
WHERE oi.order_item_type = 'line_item'
ORDER BY oi.order_id, oi.order_item_id;
"

$SSH "mysql -h 127.0.0.1 -u '$WP_DB_USER' -p'$WP_DB_PASS' '$WP_DB_NAME' -N --batch -e \"$EXPORT_SQL\"" > "$DUMP_FILE"

COUNT=$(grep -c . "$DUMP_FILE" || true)
echo "   Exported $COUNT records (orders + line items)"

if [ "$COUNT" -eq 0 ]; then
    echo "No orders on/after $SINCE — nothing to do."
    exit 0
fi

# — Step 2: import into OpenCart ————————————————————————————————
echo ""
echo "Step 2/2 — Importing into OpenCart..."

if [ "$TARGET" = "prod" ]; then
    REMOTE_SCRIPT="/tmp/wp-orders-import-$$.php"
    REMOTE_DATA="/tmp/wp-orders-data-$$.hex"

    scp -q -i "$SSH_KEY" -P "$SSH_PORT" scripts/wp-orders-import.php "$SSH_USER@$SSH_HOST:$REMOTE_SCRIPT"
    scp -q -i "$SSH_KEY" -P "$SSH_PORT" "$DUMP_FILE" "$SSH_USER@$SSH_HOST:$REMOTE_DATA"

    $SSH "OC_DB_HOST=127.0.0.1 \
          OC_DB_USER='$PROD_DB_USER' \
          OC_DB_PASS='$PROD_DB_PASS' \
          OC_DB_NAME='$PROD_DB_NAME' \
          OC_DB_PREFIX='$OC_PREFIX' \
          OC_LANGUAGE_ID='$LANGUAGE_ID' \
          OC_CURRENCY_ID='$CURRENCY_ID' \
          OC_CUSTOMER_GROUP_ID='$CUSTOMER_GROUP_ID' \
          OC_STORE_NAME='$STORE_NAME' \
          OC_STORE_URL='$STORE_URL' \
          OC_INVOICE_PREFIX='$INVOICE_PREFIX' \
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
        -e OC_LANGUAGE_ID="$LANGUAGE_ID" \
        -e OC_CURRENCY_ID="$CURRENCY_ID" \
        -e OC_CUSTOMER_GROUP_ID="$CUSTOMER_GROUP_ID" \
        -e OC_STORE_NAME="$STORE_NAME" \
        -e OC_STORE_URL="$STORE_URL" \
        -e OC_INVOICE_PREFIX="$INVOICE_PREFIX" \
        "$LOCAL_CONTAINER" \
        php /var/www/html/scripts/wp-orders-import.php $DRY_RUN < "$DUMP_FILE"
fi

echo ""

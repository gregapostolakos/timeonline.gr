#!/bin/bash
# ============================================================
#  image-pull.sh — Pull production images to local
#  Excludes image/cache. Reads settings from .env
#
#  Usage: ./scripts/image-pull.sh
# ============================================================

set -e

if [ ! -f ".env" ]; then
    echo "Error: .env file not found. Run from project root."
    exit 1
fi
source .env

# Validate required env vars
REQUIRED_VARS="SSH_USER SSH_HOST SSH_PORT SSH_KEY PROD_PATH"
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

SSH_KEY="${SSH_KEY/#\~/$HOME}"

if [ ! -f "$SSH_KEY" ]; then
    echo "Error: SSH key not found at $SSH_KEY"
    exit 1
fi

echo ""
echo "Image Pull: $SSH_USER@$SSH_HOST -> local"
echo "   Source : $PROD_PATH/image/"
echo "   Target : ./image/"
echo ""
read -p "Continue? (y/N): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 0
fi

echo ""
echo "Syncing images (this may take a while)..."
echo ""

rsync -avz --progress \
    --exclude='cache/' \
    -e "ssh -i $SSH_KEY -p $SSH_PORT" \
    "$SSH_USER@$SSH_HOST:$PROD_PATH/image/" ./image/

echo ""
echo "================================================="
echo "  Done! Images synced to ./image/"
echo "================================================="
echo ""

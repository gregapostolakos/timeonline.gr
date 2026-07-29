#!/bin/bash
# ============================================================
#  setup-secrets.sh — Push .env values to GitHub Secrets/Variables
#  Requires: gh CLI installed + authenticated
#
#  Usage: ./scripts/setup-secrets.sh
# ============================================================

set -e

# Load .env
if [ ! -f ".env" ]; then
    echo "Error: .env file not found. Run from project root."
    exit 1
fi
source .env

# Check gh CLI
if ! command -v gh &> /dev/null; then
    echo "Error: GitHub CLI (gh) not found."
    echo "Install: brew install gh"
    echo "Then: gh auth login"
    exit 1
fi

# Check authenticated
if ! gh auth status &> /dev/null; then
    echo "Error: Not authenticated with GitHub CLI."
    echo "Run: gh auth login"
    exit 1
fi

# Detect repo from git
REPO=$(gh repo view --json nameWithOwner -q .nameWithOwner 2>/dev/null || echo "")
if [ -z "$REPO" ]; then
    echo "Error: Could not detect GitHub repo. Are you in the right directory?"
    exit 1
fi

echo ""
echo "================================================="
echo "  Pushing .env values to GitHub: $REPO"
echo "================================================="
echo ""

read -p "Continue? (y/N): " -n 1 -r
echo ""
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 0
fi

echo ""

# — Secrets (encrypted) —————————————————————————————————————
echo "Setting secrets..."

set_secret() {
    local NAME=$1
    local VALUE=$2
    if [ -z "$VALUE" ]; then
        echo "   Skipped: $NAME (empty in .env)"
        return
    fi
    echo "$VALUE" | gh secret set "$NAME" --repo "$REPO" > /dev/null
    echo "   OK: $NAME"
}

set_secret "SSH_KEY"      "$(cat ${SSH_KEY/#\~/$HOME} 2>/dev/null)"
set_secret "SSH_HOST"     "$SSH_HOST"
set_secret "SSH_USER"     "$SSH_USER"
set_secret "REMOTE_PATH"  "$PROD_PATH"
set_secret "DB_NAME"      "$PROD_DB_NAME"
set_secret "DB_USER"      "$PROD_DB_USER"
set_secret "DB_PASS"      "$PROD_DB_PASS"
set_secret "WORKFLOW_PAT" "$WORKFLOW_PAT"

# — Variables (not encrypted) —————————————————————————————
echo ""
echo "Setting variables..."

set_variable() {
    local NAME=$1
    local VALUE=$2
    if [ -z "$VALUE" ]; then
        echo "   Skipped: $NAME (empty)"
        return
    fi
    gh variable set "$NAME" --repo "$REPO" --body "$VALUE" > /dev/null
    echo "   OK: $NAME = $VALUE"
}

set_variable "SITE_URL"       "$PROD_URL"
set_variable "SSH_PORT"       "$SSH_PORT"
set_variable "PHP_VERSION"    "${PHP_VERSION:-8.3}"
set_variable "SLACK_WEBHOOK"  "$SLACK_WEBHOOK"

echo ""
echo "================================================="
echo "  Done! Secrets + Variables synced from .env."
echo "================================================="
echo ""
echo "Verify at: https://github.com/$REPO/settings/secrets/actions"
echo ""

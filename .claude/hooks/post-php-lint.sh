#!/bin/bash
set -euo pipefail

# PostToolUse hook: mago lint + format after PHP file edits
FILE=$(jq -r '.tool_input.file_path // .tool_input.path' <<< "$(cat)")

# Only process PHP files
case "$FILE" in
  *.php) ;;
  *) exit 0 ;;
esac

cd "$CLAUDE_PROJECT_DIR"

# Format the file (suppress errors)
vendor/bin/mago fmt "$FILE" 2>/dev/null || true

# Lint the file and capture diagnostics
DIAG=$(vendor/bin/mago lint "$FILE" 2>&1 | head -20) || true

if [ -n "$DIAG" ] && echo "$DIAG" | grep -qE '(error|warning|note)'; then
  echo ""
  echo "mago lint diagnostics:"
  echo "$DIAG"
fi

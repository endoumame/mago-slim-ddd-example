#!/bin/bash
set -euo pipefail

# PostToolUse hook: mago fmt + lint + analyze after PHP file edits
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
LINT_DIAG=$(vendor/bin/mago lint "$FILE" 2>&1 | head -20) || true

# Analyze the file and capture diagnostics
ANALYZE_DIAG=$(vendor/bin/mago analyze "$FILE" 2>&1 | head -20) || true

# Combine diagnostics
DIAG=""
if [ -n "$LINT_DIAG" ] && echo "$LINT_DIAG" | grep -qE '(error|warning|note)'; then
  DIAG="$LINT_DIAG"
fi
if [ -n "$ANALYZE_DIAG" ] && echo "$ANALYZE_DIAG" | grep -qE '(error|warning|note)'; then
  if [ -n "$DIAG" ]; then
    DIAG="$DIAG"$'\n'"$ANALYZE_DIAG"
  else
    DIAG="$ANALYZE_DIAG"
  fi
fi

# Output diagnostics in JSON format for Claude Code
if [ -n "$DIAG" ]; then
  MSG=$(echo "$DIAG" | jq -Rs .)
  echo "{\"event\":\"PostToolUse\",\"additionalContext\":$MSG}"
fi

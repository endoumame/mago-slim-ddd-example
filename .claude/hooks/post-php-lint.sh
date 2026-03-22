#!/bin/bash
set -euo pipefail

input="$(cat)"
file="$(jq -r '.tool_input.file_path // .tool_input.path // empty' <<< "$input")"

# Only process PHP files
case "$file" in
  *.php) ;;
  *) exit 0 ;;
esac

cd "$CLAUDE_PROJECT_DIR"

# Format the file (suppress errors)
vendor/bin/mago fmt "$file" >/dev/null 2>&1 || true

# Lint + analyze: only capture output when the command fails (exit code != 0)
diag=""
if ! lint="$(vendor/bin/mago lint "$file" 2>&1 | head -20)"; then
  diag="$lint"
fi
if ! analyze="$(vendor/bin/mago analyze "$file" 2>&1 | head -20)"; then
  diag="$diag"$'\n'"$analyze"
fi

if [ -n "$diag" ]; then
  jq -Rn --arg msg "$diag" '{
    hookSpecificOutput: {
      hookEventName: "PostToolUse",
      additionalContext: $msg
    }
  }'
fi

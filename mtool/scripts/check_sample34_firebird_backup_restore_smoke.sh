#!/usr/bin/env bash
set -euo pipefail

COMPOSE_FILE="${1:-compose.user-db-firebird.yaml}"

docker compose -f "$COMPOSE_FILE" exec -T user-db-firebird sh -s <<'REMOTE'
set -eu

backup="/tmp/sample34-firebird-promotion.fbk"
restored="/tmp/sample34-firebird-promotion-restored.fdb"
rm -f "$backup" "$restored"

/opt/firebird/bin/gbak \
  -b \
  -user "$FIREBIRD_USER" \
  -password "$FIREBIRD_PASSWORD" \
  "localhost:/var/lib/firebird/data/$FIREBIRD_DATABASE" \
  "$backup"

/opt/firebird/bin/gbak \
  -c \
  -user "$FIREBIRD_USER" \
  -password "$FIREBIRD_PASSWORD" \
  "$backup" \
  "$restored"

parent_count=$(
  echo 'select count(*) as PARENT_COUNT from "parent";' \
    | /opt/firebird/bin/isql -user "$FIREBIRD_USER" -password "$FIREBIRD_PASSWORD" "$restored" \
    | awk '/PARENT_COUNT/ {found=1; next} found && /====/ {next} found && NF {gsub(/[[:space:]]/, "", $0); print; exit}'
)
record_count=$(
  echo 'select count(*) as RECORD_COUNT from "record";' \
    | /opt/firebird/bin/isql -user "$FIREBIRD_USER" -password "$FIREBIRD_PASSWORD" "$restored" \
    | awk '/RECORD_COUNT/ {found=1; next} found && /====/ {next} found && NF {gsub(/[[:space:]]/, "", $0); print; exit}'
)

ok=false
if [ "$parent_count" = "1" ] && [ "$record_count" = "2" ]; then
  ok=true
fi

printf '{"ok":%s,"stage":"sample34_firebird_backup_restore_smoke","mutation_performed":true,"details":{"parent_count":%s,"record_count":%s,"backup_path":"%s","restore_path":"%s"}}\n' \
  "$ok" "$parent_count" "$record_count" "$backup" "$restored"

rm -f "$backup" "$restored"

if [ "$ok" != "true" ]; then
  exit 2
fi
REMOTE

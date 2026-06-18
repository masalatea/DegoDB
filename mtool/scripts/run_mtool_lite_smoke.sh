#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

cd "$REPO_ROOT"

timestamp="$(date +%Y%m%d%H%M%S)"
store_dir="${APP_CONFIG_STORE_DIR:-work/tmp/mtool-lite-smoke-$timestamp}"
backup_dir="${CONFIG_DB_BACKUP_DIR:-work/tmp/mtool-lite-smoke-backups-$timestamp}"

export APP_CONFIG_STORE_DIR="$store_dir"
export CONFIG_DB_BACKUP_DIR="$backup_dir"

cleanup() {
  APP_CONFIG_STORE_DIR="$store_dir" make down-mtool-lite >/dev/null 2>&1 || true
}
trap cleanup EXIT

echo "[mtool-lite-smoke] store: $store_dir"
echo "[mtool-lite-smoke] backups: $backup_dir"

make up-mtool-lite

echo "[mtool-lite-smoke] admin/lab health"
make health-mtool-lite

echo "[mtool-lite-smoke] admin top page"
APP_CONFIG_STORE_DIR="$store_dir" docker compose \
  -f compose.yaml \
  -f mtool/docker/compose/01_mtool-lite.compose.yaml \
  exec -T web-admin curl -fsS http://127.0.0.1/ >/dev/null

echo "[mtool-lite-smoke] config preflight"
make config-db-preflight-mtool-lite

echo "[mtool-lite-smoke] config migrate"
make db-config-migrate-mtool-lite

echo "[mtool-lite-smoke] MTOOL core seed"
bash mtool/scripts/apply_config_sample_seed.sh \
  --compose-file=mtool/docker/compose/01_mtool-lite.compose.yaml \
  mtool/docker/mariadb/config-seed

echo "[mtool-lite-smoke] config preflight after MTOOL core seed"
make config-db-preflight-mtool-lite

echo "[mtool-lite-smoke] sqlite backup"
backup_file="$(
  APP_CONFIG_STORE_DIR="$store_dir" \
  CONFIG_DB_BACKUP_DIR="$backup_dir" \
  php mtool/scripts/config_store_sqlite_backup.php \
    --action=backup \
    --backup-dir="$backup_dir" \
    --profile=mtool-lite-smoke \
  | php -r '$j=json_decode(stream_get_contents(STDIN), true); if (!is_array($j) || empty($j["backup_file"])) { fwrite(STDERR, "backup_file missing\n"); exit(1); } echo $j["backup_file"], PHP_EOL;'
)"

test -f "$backup_file"
echo "[mtool-lite-smoke] backup file: $backup_file"

echo "[mtool-lite-smoke] sqlite restore"
APP_CONFIG_STORE_DIR="$store_dir" \
CONFIG_DB_BACKUP_DIR="$backup_dir" \
CONFIRM_RESTORE=yes \
php mtool/scripts/config_store_sqlite_backup.php \
  --action=restore \
  --backup-dir="$backup_dir" \
  --backup-file="$backup_file" \
  --confirm-restore=yes \
  --profile=mtool-lite-smoke >/dev/null

echo "[mtool-lite-smoke] config preflight after restore"
make config-db-preflight-mtool-lite

echo "[mtool-lite-smoke] OK"

#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

usage() {
  cat <<'EOF'
usage: run_artifact_parity_capture.sh --lane=mysql|sqlite --run-id=ID

Runs the artifact parity sample set for one config-store lane and
captures published source outputs under work/artifact-parity/<run-id>/<lane>/.
EOF
}

lane=""
run_id=""

for argument in "$@"; do
  case "$argument" in
    --lane=*)
      lane="${argument#--lane=}"
      ;;
    --run-id=*)
      run_id="${argument#--run-id=}"
      ;;
    --help|-h)
      usage
      exit 0
      ;;
    *)
      echo "unsupported argument: $argument" >&2
      usage >&2
      exit 1
      ;;
  esac
done

if [ "$lane" != "mysql" ] && [ "$lane" != "sqlite" ]; then
  usage >&2
  exit 1
fi

if [ -z "$run_id" ]; then
  usage >&2
  exit 1
fi

capture_root="$REPO_ROOT/work/artifact-parity/$run_id/$lane"
rm -rf "$capture_root"
mkdir -p "$capture_root"

run_target() {
  local target="$1"
  (cd "$REPO_ROOT" && make "$target")
}

copy_output() {
  local project_key="$1"
  local sample_key="$2"
  local source_output_key="$3"
  local source_root="$REPO_ROOT/work/source-outputs/$project_key/$source_output_key"
  local dest_root="$capture_root/$sample_key/$source_output_key"

  if [ ! -d "$source_root" ]; then
    echo "source output not found after parity capture: $source_root" >&2
    exit 1
  fi

  rm -rf "$dest_root"
  mkdir -p "$(dirname "$dest_root")"
  cp -R "$source_root" "$dest_root"
}

copy_path() {
  local source_root="$1"
  local sample_key="$2"
  local artifact_key="$3"
  local dest_root="$capture_root/$sample_key/$artifact_key"

  if [ ! -d "$source_root" ]; then
    echo "artifact path not found after parity capture: $source_root" >&2
    exit 1
  fi

  rm -rf "$dest_root"
  mkdir -p "$(dirname "$dest_root")"
  cp -R "$source_root" "$dest_root"
}

if [ "$lane" = "mysql" ]; then
  run_target sample01-pack-runtime-test
  copy_output SAMPLE1 sample01-simple-table-runtime DATACLASS-PHP
  copy_output SAMPLE1 sample01-simple-table-runtime DBACCESS-PHP

  run_target sample02-pack-runtime-test
  copy_output SAMPLE02 sample02-dataclass-nullable-default-status DATACLASS-PHP

  run_target sample03-pack-runtime-test
  copy_output SAMPLE03 sample03-dataclass-lookup-and-helper DATACLASS-PHP

  run_target sample04-pack-runtime-test
  copy_output SAMPLE04 sample04-dataclass-parent-child-basic DATACLASS-PHP

  run_target sample05-pack-runtime-test
  copy_output SAMPLE05 sample05-dbaccess-select-basic DATACLASS-PHP
  copy_output SAMPLE05 sample05-dbaccess-select-basic DBACCESS-PHP

  run_target sample06-pack-runtime-test
  copy_output SAMPLE06 sample06-dbaccess-filter-sort-page DATACLASS-PHP
  copy_output SAMPLE06 sample06-dbaccess-filter-sort-page DBACCESS-PHP

  run_target sample07-pack-runtime-test
  copy_output SAMPLE07 sample07-dbaccess-crud-basic DATACLASS-PHP
  copy_output SAMPLE07 sample07-dbaccess-crud-basic DBACCESS-PHP

  run_target sample08-pack-runtime-test
  copy_output SAMPLE08 sample08-dbaccess-join-read-model DATACLASS-PHP
  copy_output SAMPLE08 sample08-dbaccess-join-read-model DBACCESS-PHP

  run_target sample09-pack-runtime-test
  copy_output SAMPLE09 sample09-dbaccess-aggregate-report DATACLASS-PHP
  copy_output SAMPLE09 sample09-dbaccess-aggregate-report DBACCESS-PHP

  run_target sample10-pack-runtime-test
  copy_output SAMPLE10 sample10-dbaccess-mini-crud-flow DATACLASS-PHP
  copy_output SAMPLE10 sample10-dbaccess-mini-crud-flow DBACCESS-PHP

  run_target sample11-pack-runtime-test
  copy_output SAMPLE11 sample11-html-template-output HTML-PAGE

  run_target sample12-pack-runtime-test
  copy_output SAMPLE12 sample12-external-db-source-import DATACLASS-PHP

  run_target sample13-pack-runtime-test
  copy_output SAMPLE13 sample13-openapi-api-surface OPENAPI-JSON

  run_target sample14-pack-runtime-test
  copy_output SAMPLE14 sample14-custom-proxy-runtime CUSTOM-PROXY-SERVER

  run_target sample15-pack-runtime-test
  copy_path "$REPO_ROOT/work/sample-packs/sample15-project-metadata-export-import/tmp/sample15-project-metadata-bundle" \
    sample15-project-metadata-export-import PROJECT-METADATA-BUNDLE

  run_target sample16-pack-runtime-test
  copy_output SAMPLE16 sample16-authenticated-proxy AUTH-PROXY-SERVER

  run_target sample17-pack-runtime-test
  copy_output SAMPLE17 sample17-multi-output-project DATACLASS-PHP
  copy_output SAMPLE17 sample17-multi-output-project DBACCESS-PHP
  copy_output SAMPLE17 sample17-multi-output-project HTML-PAGE
  copy_output SAMPLE17 sample17-multi-output-project OPENAPI-JSON
else
  run_target sample01-pack-runtime-test-sqlite
  copy_output SAMPLE1 sample01-simple-table-runtime DATACLASS-PHP
  copy_output SAMPLE1 sample01-simple-table-runtime DBACCESS-PHP

  run_target sample02-pack-runtime-test-sqlite
  copy_output SAMPLE02 sample02-dataclass-nullable-default-status DATACLASS-PHP

  run_target sample03-pack-runtime-test-sqlite
  copy_output SAMPLE03 sample03-dataclass-lookup-and-helper DATACLASS-PHP

  run_target sample04-pack-runtime-test-sqlite
  copy_output SAMPLE04 sample04-dataclass-parent-child-basic DATACLASS-PHP

  run_target sample05-pack-runtime-test-sqlite
  copy_output SAMPLE05 sample05-dbaccess-select-basic DATACLASS-PHP
  copy_output SAMPLE05 sample05-dbaccess-select-basic DBACCESS-PHP

  run_target sample06-pack-runtime-test-sqlite
  copy_output SAMPLE06 sample06-dbaccess-filter-sort-page DATACLASS-PHP
  copy_output SAMPLE06 sample06-dbaccess-filter-sort-page DBACCESS-PHP

  run_target sample07-pack-runtime-test-sqlite
  copy_output SAMPLE07 sample07-dbaccess-crud-basic DATACLASS-PHP
  copy_output SAMPLE07 sample07-dbaccess-crud-basic DBACCESS-PHP

  run_target sample08-pack-runtime-test-sqlite
  copy_output SAMPLE08 sample08-dbaccess-join-read-model DATACLASS-PHP
  copy_output SAMPLE08 sample08-dbaccess-join-read-model DBACCESS-PHP

  run_target sample09-pack-runtime-test-sqlite
  copy_output SAMPLE09 sample09-dbaccess-aggregate-report DATACLASS-PHP
  copy_output SAMPLE09 sample09-dbaccess-aggregate-report DBACCESS-PHP

  run_target sample10-pack-runtime-test-sqlite
  copy_output SAMPLE10 sample10-dbaccess-mini-crud-flow DATACLASS-PHP
  copy_output SAMPLE10 sample10-dbaccess-mini-crud-flow DBACCESS-PHP

  run_target sample11-pack-runtime-test-sqlite
  copy_output SAMPLE11 sample11-html-template-output HTML-PAGE

  run_target sample12-pack-runtime-test-sqlite
  copy_output SAMPLE12 sample12-external-db-source-import DATACLASS-PHP

  run_target sample13-pack-runtime-test-sqlite
  copy_output SAMPLE13 sample13-openapi-api-surface OPENAPI-JSON

  run_target sample14-pack-runtime-test-sqlite
  copy_output SAMPLE14 sample14-custom-proxy-runtime CUSTOM-PROXY-SERVER

  run_target sample15-pack-runtime-test-sqlite
  copy_path "$REPO_ROOT/work/sample-packs/sample15-project-metadata-export-import-sqlite/tmp/sample15-project-metadata-bundle" \
    sample15-project-metadata-export-import PROJECT-METADATA-BUNDLE

  run_target sample16-pack-runtime-test-sqlite
  copy_output SAMPLE16 sample16-authenticated-proxy AUTH-PROXY-SERVER

  run_target sample17-pack-runtime-test-sqlite
  copy_output SAMPLE17 sample17-multi-output-project DATACLASS-PHP
  copy_output SAMPLE17 sample17-multi-output-project DBACCESS-PHP
  copy_output SAMPLE17 sample17-multi-output-project HTML-PAGE
  copy_output SAMPLE17 sample17-multi-output-project OPENAPI-JSON
fi

php "$REPO_ROOT/mtool/scripts/artifact_parity.php" manifest \
  --root="$capture_root" \
  --lane="$lane" \
  --output="$capture_root/manifest.json" \
  --pretty

echo "captured artifact parity lane: $lane -> $capture_root"

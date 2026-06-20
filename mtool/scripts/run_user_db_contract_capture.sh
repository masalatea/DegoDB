#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

if [ -f "$REPO_ROOT/.env" ]; then
  set -a
  # shellcheck disable=SC1091
  . "$REPO_ROOT/.env"
  set +a
fi

usage() {
  cat <<'EOF'
usage: run_user_db_contract_capture.sh --lane=mysql|sqlite|pgsql --run-id=ID [--sample=sample10-dbaccess-mini-crud-flow]

Runs one user DB contract sample for one lane and captures DBAccess output
under work/user-db-contract/<run-id>/<lane>/.
EOF
}

lane=""
run_id=""
sample="sample10-dbaccess-mini-crud-flow"

for argument in "$@"; do
  case "$argument" in
    --lane=*)
      lane="${argument#--lane=}"
      ;;
    --run-id=*)
      run_id="${argument#--run-id=}"
      ;;
    --sample=*)
      sample="${argument#--sample=}"
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

if [ "$lane" != "mysql" ] && [ "$lane" != "sqlite" ] && [ "$lane" != "pgsql" ]; then
  usage >&2
  exit 1
fi

if [ -z "$run_id" ]; then
  usage >&2
  exit 1
fi

case "$sample" in
  sample06-dbaccess-filter-sort-page)
    project_key="SAMPLE06"
    mysql_target="sample06-pack-runtime-test"
    sqlite_target="sample06-pack-runtime-test-sqlite"
    compose_file="sample/tutorials/sample06-dbaccess-filter-sort-page/compose.yaml"
    run_script="./sample/tutorials/sample06-dbaccess-filter-sort-page/run.sh"
    ;;
  sample08-dbaccess-join-read-model)
    project_key="SAMPLE08"
    mysql_target="sample08-pack-runtime-test"
    sqlite_target="sample08-pack-runtime-test-sqlite"
    compose_file="sample/tutorials/sample08-dbaccess-join-read-model/compose.yaml"
    run_script="./sample/tutorials/sample08-dbaccess-join-read-model/run.sh"
    ;;
  sample09-dbaccess-aggregate-report)
    project_key="SAMPLE09"
    mysql_target="sample09-pack-runtime-test"
    sqlite_target="sample09-pack-runtime-test-sqlite"
    compose_file="sample/tutorials/sample09-dbaccess-aggregate-report/compose.yaml"
    run_script="./sample/tutorials/sample09-dbaccess-aggregate-report/run.sh"
    ;;
  sample10-dbaccess-mini-crud-flow)
    project_key="SAMPLE10"
    mysql_target="sample10-pack-runtime-test"
    sqlite_target="sample10-pack-runtime-test-sqlite"
    compose_file="sample/tutorials/sample10-dbaccess-mini-crud-flow/compose.yaml"
    run_script="./sample/tutorials/sample10-dbaccess-mini-crud-flow/run.sh"
    ;;
  sample18-mini-task-board-demo)
    project_key="SAMPLE18"
    mysql_target="sample18-pack-runtime-test"
    sqlite_target="sample18-pack-runtime-test-sqlite"
    compose_file="sample/tutorials/sample18-mini-task-board-demo/compose.yaml"
    run_script="./sample/tutorials/sample18-mini-task-board-demo/run.sh"
    ;;
  sample19-json-first-content-model-demo)
    project_key="SAMPLE19"
    mysql_target="sample19-pack-runtime-test"
    sqlite_target="sample19-pack-runtime-test-sqlite"
    compose_file="sample/tutorials/sample19-json-first-content-model-demo/compose.yaml"
    run_script="./sample/tutorials/sample19-json-first-content-model-demo/run.sh"
    ;;
  sample21-ebook-catalog-api-demo)
    project_key="SAMPLE21"
    mysql_target="sample21-pack-runtime-test"
    sqlite_target=""
    compose_file="sample/tutorials/sample21-ebook-catalog-api-demo/compose.yaml"
    run_script="./sample/tutorials/sample21-ebook-catalog-api-demo/run.sh"
    ;;
  sample22-ebook-chapter-workflow-demo)
    project_key="SAMPLE22"
    mysql_target="sample22-pack-runtime-test"
    sqlite_target=""
    compose_file="sample/tutorials/sample22-ebook-chapter-workflow-demo/compose.yaml"
    run_script="./sample/tutorials/sample22-ebook-chapter-workflow-demo/run.sh"
    ;;
  sample23-ebook-media-metadata-demo)
    project_key="SAMPLE23"
    mysql_target="sample23-pack-runtime-test"
    sqlite_target=""
    compose_file="sample/tutorials/sample23-ebook-media-metadata-demo/compose.yaml"
    run_script="./sample/tutorials/sample23-ebook-media-metadata-demo/run.sh"
    ;;
  sample24-ebook-public-reader-site-demo)
    project_key="SAMPLE24"
    mysql_target="sample24-pack-runtime-test"
    sqlite_target=""
    compose_file="sample/tutorials/sample24-ebook-public-reader-site-demo/compose.yaml"
    run_script="./sample/tutorials/sample24-ebook-public-reader-site-demo/run.sh"
    ;;
  sample25-ebook-editor-auth-cms-demo)
    project_key="SAMPLE25"
    mysql_target="sample25-pack-runtime-test"
    sqlite_target=""
    compose_file="sample/tutorials/sample25-ebook-editor-auth-cms-demo/compose.yaml"
    run_script="./sample/tutorials/sample25-ebook-editor-auth-cms-demo/run.sh"
    ;;
  sample26-ebook-headless-cms-capstone)
    project_key="SAMPLE26"
    mysql_target="sample26-pack-runtime-test"
    sqlite_target=""
    compose_file="sample/tutorials/sample26-ebook-headless-cms-capstone/compose.yaml"
    run_script="./sample/tutorials/sample26-ebook-headless-cms-capstone/run.sh"
    ;;
  *)
    echo "unsupported user DB contract sample: $sample" >&2
    exit 1
    ;;
esac

capture_root="$REPO_ROOT/work/user-db-contract/$run_id/$lane"
dbaccess_root="$capture_root/$sample/DBACCESS-PHP"
dataclass_root="$capture_root/$sample/DATACLASS-PHP"
source_dbaccess_root="$REPO_ROOT/work/source-outputs/$project_key/DBACCESS-PHP"
source_dataclass_root="$REPO_ROOT/work/source-outputs/$project_key/DATACLASS-PHP"

rm -rf "$capture_root"
mkdir -p "$capture_root"

run_target() {
  local target="$1"
  if [ "$lane" = "mysql" ]; then
    (cd "$REPO_ROOT" && KEEP_SAMPLE_STACK_RUNNING=1 make "$target")
    return
  fi

  if [ "$lane" = "pgsql" ]; then
    (cd "$REPO_ROOT" && make "$target")
    return
  fi

  (cd "$REPO_ROOT" && make "$target")
}

cleanup_mysql_stack() {
  if [ "$lane" = "mysql" ]; then
    "$REPO_ROOT/$run_script" reset >/dev/null 2>&1 || true
  fi
}

trap cleanup_mysql_stack EXIT

if [ "$lane" = "mysql" ] || [ "$lane" = "pgsql" ]; then
  run_target "$mysql_target"
else
  if [ -z "$sqlite_target" ]; then
    echo "sqlite user DB contract capture is not defined for sample: $sample" >&2
    exit 1
  fi
  run_target "$sqlite_target"
fi

if [ ! -d "$source_dbaccess_root" ]; then
  echo "DBAccess output not found after user DB contract capture: $source_dbaccess_root" >&2
  exit 1
fi

if [ ! -d "$source_dataclass_root" ]; then
  echo "DataClass output not found after user DB contract capture: $source_dataclass_root" >&2
  exit 1
fi

mkdir -p "$(dirname "$dbaccess_root")"
cp -R "$source_dbaccess_root" "$dbaccess_root"
cp -R "$source_dataclass_root" "$dataclass_root"

runtime_dbaccess_root="$dbaccess_root"
runtime_dataclass_root="$dataclass_root"
runtime_output="$capture_root/$sample/runtime.json"
runtime_sqlite_path="$capture_root/$sample/runtime.sqlite"

if [ "$lane" = "mysql" ]; then
  runtime_dbaccess_root="${runtime_dbaccess_root#"$REPO_ROOT/"}"
  runtime_dataclass_root="${runtime_dataclass_root#"$REPO_ROOT/"}"
  runtime_output="${runtime_output#"$REPO_ROOT/"}"

  compose_cmd=(docker compose)
  while IFS= read -r resolved_compose_file; do
    [ -n "$resolved_compose_file" ] || continue
    compose_cmd+=(-f "$resolved_compose_file")
  done < <(
    bash "$REPO_ROOT/mtool/scripts/list_compose_stack_files.sh" \
      --lane=local \
      --compose-file="$compose_file" \
      --compose-file=sample/_pack-support/sample-pack-lifecycle.compose.yaml
  )

  "${compose_cmd[@]}" exec -T \
    -e MTOOL_RUNTIME_DB_HOST=db-lab \
    -e MTOOL_RUNTIME_DB_PORT=3306 \
    -e "MTOOL_RUNTIME_DB_USER=${LAB_DB_USER:-lab_app}" \
    -e "MTOOL_RUNTIME_DB_PASSWORD=${LAB_DB_PASSWORD:-lab_app_password}" \
    -e "MTOOL_RUNTIME_DB_NAME=${LAB_DB_NAME:-lab_app}" \
    web-admin php /var/www/mtool/scripts/user_db_contract_runtime_smoke.php \
      --dbaccess-root="/var/www/$runtime_dbaccess_root" \
      --dataclass-root="/var/www/$runtime_dataclass_root" \
      --dialect=mysql \
      --sample="$sample" \
      --output="/var/www/$runtime_output" \
      --pretty
elif [ "$lane" = "pgsql" ]; then
  php "$REPO_ROOT/mtool/scripts/user_db_contract_runtime_smoke.php" \
    --dbaccess-root="$runtime_dbaccess_root" \
    --dataclass-root="$runtime_dataclass_root" \
    --dialect=pgsql \
    --sample="$sample" \
    --output="$runtime_output" \
    --pretty
else
  php "$REPO_ROOT/mtool/scripts/user_db_contract_runtime_smoke.php" \
    --dbaccess-root="$runtime_dbaccess_root" \
    --dataclass-root="$runtime_dataclass_root" \
    --dialect=sqlite \
    --sample="$sample" \
    --sqlite-path="$runtime_sqlite_path" \
    --output="$runtime_output" \
    --pretty
fi

php "$REPO_ROOT/mtool/scripts/user_db_contract.php" manifest \
  --root="$dbaccess_root" \
  --dialect="$lane" \
  --sample="$sample" \
  --output="$capture_root/manifest.json" \
  --pretty

echo "captured user DB contract lane: $lane -> $capture_root"

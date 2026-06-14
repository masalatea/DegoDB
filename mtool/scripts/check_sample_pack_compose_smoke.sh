#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

usage() {
  cat <<'EOF'
usage: check_sample_pack_compose_smoke.sh [--pack=PACK_NAME]

Validate that each active runtime sample pack compose override can be merged with
the root compose file and resolves the expected service list.

Options:
  --pack=PACK_NAME   Validate only one runtime pack from app_sample_pack_runtime_pack_names()
  --help             Show this help
EOF
}

selected_pack=""

for argument in "$@"; do
  case "$argument" in
    --pack=*)
      selected_pack="${argument#--pack=}"
      ;;
    --help)
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

if ! command -v php >/dev/null 2>&1; then
  echo "php is required to read the sample pack catalog" >&2
  exit 1
fi

expected_services=$'db-config\ndb-lab\nweb-admin\nweb-lab'
validated_count=0
selected_found=0

while IFS=$'\t' read -r pack_name relative_path; do
  if [ -z "$pack_name" ] || [ -z "$relative_path" ]; then
    continue
  fi

  if [ -n "$selected_pack" ] && [ "$pack_name" != "$selected_pack" ]; then
    continue
  fi

  selected_found=1
  compose_file="$REPO_ROOT/sample/$relative_path/compose.yaml"
  if [ ! -f "$compose_file" ]; then
    echo "compose file not found for [$pack_name]: $compose_file" >&2
    exit 1
  fi

  compose_cmd=(docker compose)
  while IFS= read -r resolved_compose_file; do
    [ -n "$resolved_compose_file" ] || continue
    compose_cmd+=(-f "$resolved_compose_file")
  done < <(
    bash "$REPO_ROOT/mtool/scripts/list_compose_stack_files.sh" \
      "--compose-file=$compose_file"
  )

  services="$(
    "${compose_cmd[@]}" config --services \
      | sort
  )"

  if [ "$services" != "$expected_services" ]; then
    echo "unexpected services for [$pack_name]" >&2
    echo "expected:" >&2
    printf '%s\n' "$expected_services" >&2
    echo "actual:" >&2
    printf '%s\n' "$services" >&2
    exit 1
  fi

  echo "ok [$pack_name] $relative_path"
  validated_count=$((validated_count + 1))
done < <(
  cd "$REPO_ROOT"
  php -r '
require "mtool/app/sample_pack_catalog.php";
foreach (app_sample_pack_runtime_pack_names() as $packName) {
    $relativePath = app_sample_pack_relative_path($packName);
    if ($relativePath === "") {
        fwrite(STDERR, "missing relative path for runtime pack: " . $packName . PHP_EOL);
        exit(1);
    }
    echo $packName, "\t", $relativePath, PHP_EOL;
}
'
)

if [ -n "$selected_pack" ] && [ "$selected_found" -eq 0 ]; then
  echo "runtime pack not found in catalog: $selected_pack" >&2
  exit 1
fi

if [ "$validated_count" -eq 0 ]; then
  echo "no runtime sample packs were validated" >&2
  exit 1
fi

echo "validated runtime sample pack compose smoke: $validated_count pack(s)"

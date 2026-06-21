#!/usr/bin/env bash
set -euo pipefail

db_path="${SQLITE_API_EXAMPLE_DB:-/tmp/dego-sqlite-api-generation-example.sqlite}"
script_dir="$(cd "$(dirname "$0")" && pwd)"

rm -f "${db_path}"
cd "${script_dir}"
sqlite3 "${db_path}" < crud-smoke.sql

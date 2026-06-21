#!/usr/bin/env bash
set -euo pipefail

if [[ -z "${POSTGRESQL_EXAMPLE_DSN:-}" ]]; then
  echo "POSTGRESQL_EXAMPLE_DSN is required."
  echo "Example: POSTGRESQL_EXAMPLE_DSN='postgresql://user:pass@127.0.0.1:5432/example' $0"
  exit 2
fi

psql "${POSTGRESQL_EXAMPLE_DSN}" \
  -v ON_ERROR_STOP=1 \
  -f "$(dirname "$0")/../schema/schema.sql" \
  -f "$(dirname "$0")/../seed.sql" \
  -f "$(dirname "$0")/representative-query.sql"

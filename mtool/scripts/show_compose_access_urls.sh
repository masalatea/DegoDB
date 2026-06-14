#!/usr/bin/env bash
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
REPO_ROOT="$(cd "$SCRIPT_DIR/../.." && pwd)"

compose_cmd=(
  docker
  compose
  -f "$REPO_ROOT/compose.yaml"
)
env_file="$REPO_ROOT/.env"

for argument in "$@"; do
  case "$argument" in
    --compose-file=*)
      compose_file="${argument#--compose-file=}"
      if [ "${compose_file#/}" = "$compose_file" ]; then
        compose_file="$REPO_ROOT/$compose_file"
      fi
      compose_cmd+=(-f "$compose_file")
      ;;
    --help|-h)
      cat <<'EOF'
usage: show_compose_access_urls.sh [--compose-file=FILE ...]

Print admin/lab access URLs and optional DB UI URLs from published Docker Compose ports.
EOF
      exit 0
      ;;
    *)
      echo "unsupported argument: $argument" >&2
      exit 1
      ;;
  esac
done

resolve_config_value() {
  local key="$1"
  local line=""

  if [ "${!key+x}" = "x" ]; then
    printf '%s' "${!key}"
    return 0
  fi

  if [ ! -f "$env_file" ]; then
    return 1
  fi

  line="$(grep -E "^${key}=" "$env_file" | tail -n 1 || true)"
  if [ -z "$line" ]; then
    return 1
  fi

  printf '%s' "${line#*=}"
}

resolve_url() {
  local service_name="$1"
  local label="$2"
  local container_port="$3"
  local port_mapping=""
  local published_port=""

  if ! port_mapping="$("${compose_cmd[@]}" port "$service_name" "$container_port" 2>/dev/null | tail -n 1)"; then
    return 0
  fi

  published_port="${port_mapping##*:}"
  if [ -z "$published_port" ]; then
    return 0
  fi

  printf '  %-14s http://127.0.0.1:%s\n' "$label" "$published_port"
}

print_stub_auth() {
  local site_label="$1"
  local username_key="$2"
  local password_key="$3"
  local mode_key="$4"
  local url_line="$5"
  local mode=""
  local username=""
  local password=""
  local root_url=""

  mode="$(resolve_config_value "$mode_key" || true)"
  if [ -z "$mode" ]; then
    mode="stub"
  fi

  if [ -n "$url_line" ]; then
    root_url="${url_line##* }"
  fi

  echo "  [$site_label]"
  echo "    auth mode: $mode"

  if [ -n "$root_url" ]; then
    echo "    login URL: $root_url/login"
  fi

  if [ "$mode" != "stub" ]; then
    return 0
  fi

  username="$(resolve_config_value "$username_key" || true)"
  password="$(resolve_config_value "$password_key" || true)"

  if [ -n "$username" ]; then
    echo "    ID: $username"
  fi

  if [ -n "$password" ]; then
    echo "    Password: $password"
  else
    echo "    Password: (empty)"
  fi
}

print_db_ui_access() {
  local tool_label="$1"
  local url_line="$2"
  local default_server="$3"
  local db_name_key="$4"
  local username_key="$5"
  local password_key="$6"
  local root_url=""
  local db_name=""
  local username=""
  local password=""

  echo "  [$tool_label]"

  if [ -z "$url_line" ]; then
    echo "    URL: unavailable"
    return 0
  fi

  root_url="${url_line##* }"
  db_name="$(resolve_config_value "$db_name_key" || true)"
  username="$(resolve_config_value "$username_key" || true)"
  password="$(resolve_config_value "$password_key" || true)"

  echo "    URL: $root_url"
  echo "    default server: $default_server"

  if [ -n "$db_name" ]; then
    echo "    DB: $db_name"
  fi

  if [ -n "$username" ]; then
    echo "    User: $username"
  fi

  if [ -n "$password" ]; then
    echo "    Password: $password"
  else
    echo "    Password: (empty)"
  fi
}

admin_url_line="$(resolve_url web-admin admin 80 || true)"
lab_url_line="$(resolve_url web-lab lab 80 || true)"
lab_db_ui_url_line="$(resolve_url lab-db-ui lab-db-ui 8080 || true)"

if [ -z "$admin_url_line" ] && [ -z "$lab_url_line" ] && [ -z "$lab_db_ui_url_line" ]; then
  echo "Access URLs: unavailable"
else
  echo "Access URLs:"
  if [ -n "$admin_url_line" ]; then
    echo "$admin_url_line"
  fi
  if [ -n "$lab_url_line" ]; then
    echo "$lab_url_line"
  fi
  if [ -n "$lab_db_ui_url_line" ]; then
    echo "$lab_db_ui_url_line"
  fi
fi

echo "Login:"
print_stub_auth admin ADMIN_AUTH_STUB_USER ADMIN_AUTH_STUB_PASSWORD ADMIN_AUTH_MODE "$admin_url_line"
print_stub_auth lab LAB_AUTH_STUB_USER LAB_AUTH_STUB_PASSWORD LAB_AUTH_MODE "$lab_url_line"

echo "DB UI:"
print_db_ui_access lab-db-ui "$lab_db_ui_url_line" db-lab LAB_DB_NAME LAB_DB_USER LAB_DB_PASSWORD

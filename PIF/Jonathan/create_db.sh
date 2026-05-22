#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR=$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)
SQL_FILE="$ROOT_DIR/db_setup.sql"

HOST="${MYSQL_HOST:-localhost}"
PORT="${MYSQL_PORT:-3306}"
DB="${MYSQL_DB:-sofjo685_pif}"
USER="${MYSQL_USER:-sofjo685}"

if [[ -z "${MYSQL_PASSWORD:-}" ]]; then
  read -r -s -p "MySQL password for ${USER}@${HOST}: " MYSQL_PASSWORD
  echo
fi

export MYSQL_PWD="$MYSQL_PASSWORD"
mysql_cmd=(mysql --protocol=tcp -h "$HOST" -P "$PORT" -u "$USER")

"${mysql_cmd[@]}" -e "CREATE DATABASE IF NOT EXISTS \`$DB\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
# Strip CREATE/USE lines so the script can target any database name.
sed -E "/^CREATE DATABASE /d; /^USE /d" "$SQL_FILE" | "${mysql_cmd[@]}" "$DB"

unset MYSQL_PWD
printf "Database '%s' created/updated from %s\n" "$DB" "$SQL_FILE"

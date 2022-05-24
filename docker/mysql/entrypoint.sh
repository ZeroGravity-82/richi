#!/bin/bash

# Create test database
export MYSQL_PWD=${MYSQL_ROOT_PASSWORD}
export MYSQL_TEST_DATABASE="${MYSQL_DATABASE}_test"

mysql_log() {
	local type="$1"; shift
	# accept argument string or stdin
	local text="$*"; if [ "$#" -eq 0 ]; then text="$(cat)"; fi
	local dt; dt="$(date --rfc-3339=seconds)"
	printf '%s [%s] [Entrypoint]: %s\n' "$dt" "$type" "$text"
}
mysql_note() {
	mysql_log Note "$@"
}

if [ -n "$MYSQL_TEST_DATABASE" ]; then
  mysql_note "Creating test database $MYSQL_TEST_DATABASE"
  mysql -uroot -e "CREATE DATABASE IF NOT EXISTS $MYSQL_TEST_DATABASE;"
fi
if [ -n "$MYSQL_USER" ] && [ -n "$MYSQL_PASSWORD" ]; then
  if [ -n "$MYSQL_TEST_DATABASE" ]; then
    mysql_note "Giving user ${MYSQL_USER} access to schema $MYSQL_TEST_DATABASE"
    mysql -uroot -e "GRANT ALL ON $MYSQL_TEST_DATABASE.* TO '$MYSQL_USER'@'%';"
  fi
fi

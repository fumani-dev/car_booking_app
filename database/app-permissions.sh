# Sourced by the MySQL entrypoint after it creates the database and app user.
case "$MYSQL_DATABASE" in
    ''|*[!a-zA-Z0-9_]*)
        echo 'DB_NAME must contain only letters, digits, and underscores.' >&2
        exit 1
        ;;
esac
case "$MYSQL_USER" in
    ''|*[!a-zA-Z0-9_]*)
        echo 'DB_USER must contain only letters, digits, and underscores.' >&2
        exit 1
        ;;
esac

# Match the database-level grant created by the MySQL entrypoint.
mysql_grant_database=${MYSQL_DATABASE//_/\\_}

docker_process_sql <<SQL
REVOKE ALL PRIVILEGES ON \`$mysql_grant_database\`.* FROM '$MYSQL_USER'@'%';
GRANT SELECT, INSERT ON \`$MYSQL_DATABASE\`.users TO '$MYSQL_USER'@'%';
GRANT SELECT, UPDATE ON \`$MYSQL_DATABASE\`.cars TO '$MYSQL_USER'@'%';
GRANT SELECT, INSERT, UPDATE ON \`$MYSQL_DATABASE\`.bookings TO '$MYSQL_USER'@'%';
SQL

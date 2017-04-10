#!/bin/bash

DBUSER=freepbxuser
DBPASS=
DBHOST=localhost
DBNAME=asteriskcdrdb

ntpstat > /dev/null 2>&1

NTPRESULT=$?

if [ $NTPRESULT -ne 0 ]; then
    exit 0
fi

DBOLDDATE=$(date -d '6 months ago' '+%F %T')

mysql --user="$DBUSER" --password="$DBPASS" --database="$DBNAME" --execute="DELETE FROM cdr WHERE calldate < '$DBOLDDATE'; OPTIMIZE TABLE cdr;" > /dev/null 2>&1

find /var/spool/asterisk/monitor/ -mtime +182 -type f -delete
find /var/spool/asterisk/backup/  -mtime +182 -type f -delete
find /var/spool/asterisk/monitor/ -type d -empty -delete

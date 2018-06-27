#!/usr/bin/env bash

mysqlUser=$1
mysqlPassword=$2
mysql Hjc1001 -u $mysqlUser -p$mysqlPassword <<EOF
truncate table users;
truncate table pets;
alter table users auto_increment=10000;
alter table pets auto_increment=10000;
EOF
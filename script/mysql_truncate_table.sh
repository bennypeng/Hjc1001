#!/usr/bin/env bash

mysql Hjc1001 -u Hjc1001 -pZaKx68Qk <<EOF
truncate table users;
truncate table pets;
alter table users auto_increment=10000;
alter table pets auto_increment=10000;
EOF
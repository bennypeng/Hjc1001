#!/usr/bin/env bash

var1=10
while [ $var1 -ge 0 ]
do
    redis-cli -h 127.0.0.1 -p 6379 -a ar2PBmus -n $var1 flushdb
    var1=$[ $var1 -1 ]
done


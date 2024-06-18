#!/bin/bash

######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
ERD_DIR="$DIR/../build/erd"
CONF_DIR="$DIR/../conf"
PARAMS_FILE='parameters.yml.dist'

function cleanup {
    echo "Cleaning up container"
    docker rm pcmt_schemaspy
}
trap cleanup EXIT

function parse_yaml {
   local prefix=$2
   local s='[[:space:]]*' w='[a-zA-Z0-9_]*' fs=$(echo @|tr @ '\034')
   sed -ne "s|^\($s\):|\1|" \
        -e "s|^\($s\)\($w\)$s:$s[\"']\(.*\)[\"']$s\$|\1$fs\2$fs\3|p" \
        -e "s|^\($s\)\($w\)$s:$s\(.*\)$s\$|\1$fs\2$fs\3|p"  $1 |
   awk -F$fs '{
      indent = length($1)/2;
      vname[indent] = $2;
      for (i in vname) {if (i > indent) {delete vname[i]}}
      if (length($3) > 0) {
         vn=""; for (i=0; i<indent; i++) {vn=(vn)(vname[i])("_")}
         printf("%s%s%s=\"%s\"\n", "'$prefix'",vn, $2, $3);
      }
   }'
}

echo "Reading from $CONF_DIR/$PARAMS_FILE"

eval $(parse_yaml "$CONF_DIR/$PARAMS_FILE")
db_port=$parameters__database_port
echo " DB port ($db_port)"
db_name=$parameters__database_name
echo " DB name ($db_name)"
db_user=$parameters__database_user
echo " DB user ($db_user)"
db_pass=$parameters__database_password
echo " DB pass ($db_pass)"
db_host=$parameters__database_host
echo " DB host ($db_host)"

docker run \
    --network pcmt_akeneo \
    --name pcmt_schemaspy \
    schemaspy/schemaspy:snapshot \
        -t mysql \
        -host "$db_host" \
        -port "$db_port" \
        -db "$db_name" \
        -s "$db_name" \
        -u "$db_user" \
        -p "$db_pass" \
        -norows \
        -hq 

echo "Copying to $ERD_DIR"
rm -Rf $ERD_DIR
mkdir -p $ERD_DIR
docker cp pcmt_schemaspy:/output/. $ERD_DIR/
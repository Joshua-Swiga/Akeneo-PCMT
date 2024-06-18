#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################


FILE=pcmt.sql
if test -f "$FILE"; then
    echo "$FILE exists, starting import..."
else
    echo "$FILE does not exist, import can not be performed. Exiting."
    exit
fi

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
CONF_DIR="$DIR/../conf"
MYSQL_ROOT_PASSWORD=`cat "$CONF_DIR"/mysql-root-password.dist`

# handle the pcmt migration table
echo 'DROP TABLE IF EXISTS pcmt_migration_versions;' > pcmt_temp.sql
docker exec -i $(docker-compose ps -q mysql) mysql -uroot -p"$MYSQL_ROOT_PASSWORD" akeneo_pim < pcmt_temp.sql
rm pcmt_temp.sql

# perform sql import
docker exec -i $(docker-compose ps -q mysql) mysql -uroot -p"$MYSQL_ROOT_PASSWORD" akeneo_pim < $FILE

# perform pcmt migrations
make dev-pcmt-migrate

# clear cache and reset ES indexes
$DIR/../ddev.sh exec fpm bin/console cache:clear --env=prod
$DIR/../ddev.sh exec fpm bin/console akeneo:elasticsearch:reset-indexes --env=prod
$DIR/../ddev.sh exec fpm bin/console pim:product:index --all --env=prod
$DIR/../ddev.sh exec fpm bin/console pim:product-model:index --all --env=prod

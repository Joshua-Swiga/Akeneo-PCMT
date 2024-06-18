#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
source $DIR/../ddev.sh exec fpm cp -v /srv/pim/app/config/migrations.yml /srv/pim/app/config/backup_migrations.yml
source $DIR/../ddev.sh exec fpm cp -v /srv/pim/app/config/pcmt_migrations.yml /srv/pim/app/config/migrations.yml
source $DIR/../ddev.sh exec fpm rm -rf var/cache
source $DIR/../ddev.sh exec fpm bin/console --env=prod doctrine:migrations:generate
source $DIR/../ddev.sh exec fpm cp -v /srv/pim/app/config/backup_migrations.yml /srv/pim/app/config/migrations.yml
source $DIR/../ddev.sh exec fpm rm -rf var/cache
#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

cp -v /srv/pim/app/config/migrations.yml /srv/pim/app/config/backup_migrations.yml
cp -v /srv/pim/app/config/pcmt_migrations.yml /srv/pim/app/config/migrations.yml
rm -rf var/cache
bin/console --env=prod doctrine:migrations:migrate
cp -v /srv/pim/app/config/backup_migrations.yml /srv/pim/app/config/migrations.yml
rm -rf var/cache
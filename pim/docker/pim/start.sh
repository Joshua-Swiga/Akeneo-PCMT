#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

function finish {
    pkill -f job-queue-consumer-daemon
}
trap finish EXIT

profile="production"
if [ ! -z "$PCMT_PROFILE" ]; then
    echo "PCMT_PROFILE env found, setting profile to $PCMT_PROFILE"
    profile=$PCMT_PROFILE
fi
echo "Running PCMT with profile: $profile"

source cpFromTmp.sh

./wait.sh mysql 3306
./wait.sh elasticsearch 9200

source write-env-reverse-proxy.sh
source cronRun.sh

shopt -s nocasematch
if [ "production" != "$profile" ]; then
    bin/console --env=prod pim:install --force --symlink
else
    bin/console --env=prod pim:installer:check-requirements
fi

./pcmtMigrate.sh
bin/console --env=prod akeneo:batch:job-queue-consumer-daemon &

sudo php-fpm7.2 -F
#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################
set -e

# name of the dev volume that has /srv/pim
PCMT_PIM_VOL='pcmt_pim'

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

# Set profile based on detection of volume
# volume present -> we've started before, so start in "production" to avoid 
#   cleaning.
# volume not present -> we haven't started, so run in non-"production" which
#   will init the db and clean install assets
pcmt_profile='production'
if [ $PCMT_PIM_VOL != "$(docker volume ls -qf name=${PCMT_PIM_VOL})" ]; then
    pcmt_profile='dev'
fi

PCMT_PROFILE=$pcmt_profile $DIR/quick-up.sh $DIR/../ddev.sh up -d
source $DIR/wait-http.sh "http://localhost"

# install new assets
if [ 'production' != $pcmt_profile ]; then
    echo First run, installing web assets
    source $DIR/install-assets.sh
fi

echo SUCCESS:  PCMT Dev now available at http://localhost
#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################
set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

pcmt_profile='test'

PCMT_PROFILE=$pcmt_profile $DIR/quick-up.sh $DIR/../ddev.sh up -d
source $DIR/wait-http.sh "http://docker:80"

# install new assets
source $DIR/../ddev.sh exec -T fpm sudo rm -rf var/cache
source $DIR/../ddev.sh exec -T fpm bin/console --env=test pim:installer:assets --clean
source $DIR/../ddev.sh run --rm node yarn run webpack

echo SUCCESS:  PCMT Dev now available at http://docker:80
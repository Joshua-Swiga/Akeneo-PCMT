#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
DDEV_CMD="$DIR/../ddev.sh"
BUILD_DIR="$DIR/../build"
PROJECT_NAME='pcmt'

ddev_run_ret_val=0

cd "$DIR/.." || exit 1

$DDEV_CMD -p "$PROJECT_NAME" exec \
    fpm /srv/pim/vendor/phpunit/phpunit/phpunit \
		-c /srv/pim/phpunit.xml.dist \
		--log-junit /srv/pim/unit-results.xml \
		--coverage-clover /srv/pim/coverage.xml \
		--coverage-html /srv/pim/coverage-report
ddev_run_ret_val=$?

CONTAINER_NAME='pcmt_fpm_1'
mkdir -p "$BUILD_DIR"
docker cp "$CONTAINER_NAME":/srv/pim/unit-results.xml "$BUILD_DIR"/unit-results.xml
docker cp "$CONTAINER_NAME":/srv/pim/coverage-report "$BUILD_DIR"
docker cp "$CONTAINER_NAME":/srv/pim/coverage.xml "$BUILD_DIR"

exit "$ddev_run_ret_val"
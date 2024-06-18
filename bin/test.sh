#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
DDEV_CMD="$DIR/../ddev.sh"
BUILD_DIR="$DIR/../build"
PROJECT_NAME='devtest'

ddev_run_ret_val=0
function cleanup {
    ret_val=$?
    echo "Cleaning up..."
    $DDEV_CMD -p "$PROJECT_NAME" down -v
    exit $(( ret_val | ddev_run_ret_val ))
}
trap cleanup EXIT

cd "$DIR/.." || exit 1
CONTAINER_NAME=$PROJECT_NAME'_fpm'
$DDEV_CMD -p "$PROJECT_NAME" run \
    --no-deps \
    --name "$CONTAINER_NAME" \
    fpm /srv/pim/vendor/phpunit/phpunit/phpunit \
		-c /srv/pim/phpunit.xml.dist \
		--log-junit /srv/pim/unit-results.xml \
		--coverage-clover /srv/pim/coverage.xml \
		--coverage-html /srv/pim/coverage-report \
		--coverage-text \
		--colors=never
ddev_run_ret_val=$?

mkdir -p "$BUILD_DIR"
docker cp "$CONTAINER_NAME":/srv/pim/unit-results.xml "$BUILD_DIR"/unit-results.xml
docker cp "$CONTAINER_NAME":/srv/pim/coverage-report "$BUILD_DIR"
docker cp "$CONTAINER_NAME":/srv/pim/coverage.xml "$BUILD_DIR"

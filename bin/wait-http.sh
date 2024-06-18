#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

URL=$1

echo "Waiting for $URL..."
until $(curl --output /dev/null --silent --head --fail "$URL"); do
    printf '.'
    sleep 5
done
echo "... $URL is up"
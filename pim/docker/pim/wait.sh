#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

uri=$1
port=$2

echo "Waiting for $uri at $port"

while ! nc -z ${uri} ${port}; do
    sleep 0.5
done

echo "$uri started"
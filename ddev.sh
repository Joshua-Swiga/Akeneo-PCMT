#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

PCMT_VER=$(bin/pcmt-ver-sha.sh)

PCMT_VER=$PCMT_VER docker-compose -f docker-compose.yml \
    -f docker-compose.dev.yml \
    -f docker-compose.test.yml \
    ${@}
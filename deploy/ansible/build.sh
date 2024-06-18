#!/bin/sh
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

PCMT_REG=${1:-"pcmt"}

docker build -t $PCMT_REG/ansible:latest .
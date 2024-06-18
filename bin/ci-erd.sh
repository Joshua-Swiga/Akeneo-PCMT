#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

PCMT_URL=${1:-"http://localhost"}

export PCMT_VER=$("$DIR/pcmt-ver-sha.sh")
$DIR/quick-up.sh
$DIR/wait-http.sh "$PCMT_URL"
$DIR/erd.sh

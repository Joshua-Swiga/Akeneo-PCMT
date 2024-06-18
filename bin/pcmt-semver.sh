#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

# path of this script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

# get path to .env file
ENV_PATH="$DIR/../.env"

# Load PCMT_VER semver from .env file
if [ ! -r "$ENV_PATH" ]; then
    echo >&2 ".env file not readable at: $ENV_PATH - Aborting."
    exit 1;
fi
PCMT_SEMVER=$(grep -v '^#' $ENV_PATH | grep '^PCMT_VER=' -)

echo ${PCMT_SEMVER##*=}
#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

set -e
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

: "${PCMT_PROFILE:=production}"

ENV_NAME=$1

if [ -z "$ENV_NAME" ]; then
    echo "ERROR: Environment name not given as first argument"
    exit 1
fi

EXTRA_CMD="${@:2}"
if [ ! -z "$EXTRA_CMD" ]; then
    echo "ERROR: Unrecognized: $EXTRA_CMD"
    exit 1
fi

if [ -z "$PCMT_VER" ]; then
    echo "PCMT_VER not set, so setting to full version..."
    export PCMT_VER=$("$DIR/pcmt-ver-sha.sh")
fi
    
if [ -z "$PCMT_ASSET_URL" ]; then
    COMMIT_SHA=$(git rev-parse HEAD)
    export PCMT_ASSET_URL="https://gitlab.com/pcmt/pcmt/-/archive/$COMMIT_SHA/pcmt-$COMMIT_SHA.tar.gz"
fi

echo "Deploying $PCMT_VER to $ENV_NAME using $PCMT_ASSET_URL with profile $PCMT_PROFILE"
source "$DIR/../deploy/terraform/run-docker.sh" "$ENV_NAME" apply -auto-approve
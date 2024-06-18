#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

# path of this script
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

PCMT_SEMVER=$("$DIR/pcmt-semver.sh")

# Get commit sha
command -v git >/dev/null 2>&1 || {
    echo >&2 "I require git but it's not installed.  Aborting.";
    exit 1;
}
if [ "true" != $(git rev-parse --is-inside-work-tree) ]; then 
    echo >&2 "Not a git repository, unable to generate commit SHA. Aborting.";
    exit 1;
fi
GIT_SHA=$(git rev-parse --short=8 HEAD)

echo "$PCMT_SEMVER-sha$GIT_SHA"
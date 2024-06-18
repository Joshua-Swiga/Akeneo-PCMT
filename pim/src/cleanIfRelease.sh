#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
rmOnRelease="$DIR/PcmtPreReleaseBundle/**/*"

semVer=${1:-"-"}
echo Semver is $semVer

isRelease=false
if [[ "$semVer" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
    echo Detected that this is a release...
    isRelease="true"
else
    echo Detected this is not a release...
fi

if [ "true" == "$isRelease" ]; then
    echo ... removing $rmOnRelease
    rm -rv ${rmOnRelease}
fi
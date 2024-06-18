#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

if [ -z "$PCMT_ASSET_URL" ]; then
    echo PCMT ASSET URL missing
    exit 1
fi

if [ -z "$PCMT_PROFILE" ]; then
    echo "Warning: PCMT Profile not set"
fi

if [ -z "$PCMT_VER" ]; then
    echo "Warning: PCMT Version not set"
fi

SSH_KEY="/tmp/.ssh/id_rsa"
if [ ! -r "$SSH_KEY" -o ! -f "$SSH_KEY" ]; then
    echo "SSH Key $SSH_KEY not accessible"
    exit 1
fi
cp -R /tmp/.ssh /root/.ssh
chmod 700 /root/.ssh
chmod 400 /root/.ssh/*

echo Starting ssh-agent and adding default key
eval `ssh-agent -s`
ssh-add

exec "$@"
#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

. /set-env.sh

sftp -i "$SFTP_PRIVKEY_FILENAME" \
    -oHostKeyAlgorithms=+ssh-dss \
    -oStrictHostKeyChecking=no \
    -b - \
    "$SFTP_USER@$SFTP_HOST"
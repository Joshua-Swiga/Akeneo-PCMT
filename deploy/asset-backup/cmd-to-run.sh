#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################


set -o errexit -o pipefail -o noclobber -o nounset

: "${ASSET_DIR:=/pim-assets}"
: "${BACKUP_DIR:=/backup}"
: "${PCMT_HOSTNAME:=pcmt}"

backupName="$BACKUP_DIR/$PCMT_HOSTNAME-asset_backup-$(date -u +'%Y%m%dT%H%M%SZ').tgz"

cd "$ASSET_DIR" || exit 1
tar -cvzf "$backupName" ./*
retVal=$?

if [ $retVal -eq 0 ]; then
  echo "Backup written to:  $backupName"
fi
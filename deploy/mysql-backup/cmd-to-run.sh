#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

set -o pipefail

: "${CRED_PATH:=/run/secrets/mysql-creds}"
if [ ! -r "$CRED_PATH" ]; then
    echo "$CRED_PATH not readable"
    exit 1
fi

set -o allexport
source $CRED_PATH
set +o allexport

: "${DB_HOST:?DB_HOST not found}"
: "${DB_PORT:?DB_PORT not found}"
: "${DB_NAME:?DB_NAME not found}"
: "${DB_USER:?DB_USER not found}"
: "${DB_PASS:?DB_PASS not found}"
: "${BACKUP_DIR:=/backup}"
: "${PCMT_HOSTNAME:=pcmt}"

backupName="$PCMT_HOSTNAME-mysql-dump-$(date -u -Iminutes).sql.gz"

mysqldump --no-tablespaces \
  -h "$DB_HOST" \
  --port="$DB_PORT" \
  -u "$DB_USER" \
  -p"$DB_PASS" \
  "$DB_NAME" \
    | gzip \
    > "$BACKUP_DIR/$backupName"
retVal=$?

if [ $retVal -eq 0 ]; then
  echo "Backup written to:  $backupName"
fi
#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

: "${CRED_PATH:=/run/secrets/s3-creds}"
if [ ! -r "$CRED_PATH" ]; then
    echo "$CRED_PATH not readable"
    exit 1
fi

set -o allexport
source $CRED_PATH
set +o allexport

: "${AWS_ACCESS_KEY_ID:?AWS_ACCESS_KEY_ID not set}"
: "${AWS_SECRET_ACCESS_KEY:?AWS_ACCESS_KEY_ID not set}"
: "${S3_BUCKET:?AWS S3 Bucket not set}"
: "${LOCAL_DIR_TO_SYNC_OUT:=/backup}"

if [ ! -r "$LOCAL_DIR_TO_SYNC_OUT" ]; then
    echo "Directory not readable to sync: $LOCAL_DIR_TO_SYNC_OUT"
    exit 1
fi

echo Syncing to S3...
s3cmd sync "$LOCAL_DIR_TO_SYNC_OUT" "$S3_BUCKET"

if [ $? == 0 ]; then
    echo "Removing local copies..."
    find "$LOCAL_DIR_TO_SYNC_OUT" -type f -print -delete
fi
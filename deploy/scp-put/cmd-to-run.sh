#!/bin/bash
######################################################################
# Copyright (c) 2022, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

: "${CRED_PATH:=/run/secrets/scp-creds}"
if [ ! -r "$CRED_PATH" ]; then
    echo "$CRED_PATH not readable"
    exit 1
fi

set -o allexport
source $CRED_PATH
set +o allexport

# command reference:  scp -i SCP_IDENTITY_PATH -P SCP_REMOTE_PORT -r SCP_LOCAL_PATH SCP_REMOTE_URI
: "${SCP_IDENTITY_PATH:=/run/secrets/scp-identity}"
: "${SCP_LOCAL_PATH:?SCP_LOCAL_PATH not set}"
: "${SCP_REMOTE_URI:?SCP_REMOTE_URI not set}" # e.g. someuser@someHost:/some/remote/path
: "${SCP_REMOTE_PORT:=22}"

if [ ! -r "$SCP_IDENTITY_PATH" ]; then
    echo "Identity file not readable: $SCP_IDENTITY_PATH"
    exit 1
fi

if [ ! -r "$SCP_LOCAL_PATH" ]; then
    echo "Local directory not readable: $SCP_LOCAL_PATH"
    exit 1
fi

echo Copying from "$SCP_LOCAL_PATH" to "$SCP_REMOTE_URI" ...
scp -o "StrictHostKeyChecking no" \
    -i "$SCP_IDENTITY_PATH" \
    -P "$SCP_REMOTE_PORT" \
    -r "$SCP_LOCAL_PATH" \
    "$SCP_REMOTE_URI" 

if [ $? == 0 ]; then
    echo "Copy complete, removing local copies..."
    find "$SCP_LOCAL_PATH" -type f -print -delete
fi
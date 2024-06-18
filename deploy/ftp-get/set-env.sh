#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

# Intended to be used with "source" to export all the environment variables
# this finds/checks/defaults

error_when_unreadable() {
  if [ ! -r "$1" ]; then
    echo "$1 is not readable"
    exit 1
  fi
}

set -o allexport

: "${FTP_GET_CREDS:=/run/secrets/ftp-get-creds}"
error_when_unreadable "$FTP_GET_CREDS"

source "$FTP_GET_CREDS"

: "${SFTP_USER:?SFTP_USER not set}"
: "${SFTP_HOST:?SFTP_HOST not set}"
: "${SFTP_PRIVKEY_FILENAME:?SFTP_PRIVKEY_FILENAME not set}"
error_when_unreadable "$SFTP_PRIVKEY_FILENAME"
: "${SFTP_LOCAL_DIR:=/downloads}"
error_when_unreadable "$SFTP_LOCAL_DIR"
: "${SFTP_REMOTE_DIR:?SFTP_REMOTE_DIR not set}"
: "${SFTP_REMOTE_ARCHIVE_DIR:?SFTP_REMOTE_ARCHIVE_DIR not set}"

set +o allexport
#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

. /set-env.sh
umask 0000
chown docker:docker "$SFTP_LOCAL_DIR"

mapfile -t files < <( ls -1 "$SFTP_LOCAL_DIR" )
printf "Files to upload: "
printf '%s, ' "${files[@]}"

(
  echo cd "$SFTP_REMOTE_DIR"
  for file in "${files[@]}"; do
    echo put "$file"
    echo !mv "$file" "$SFTP_LOCAL_ARCHIVE_DIR/$file"
    echo rename "$file" "$SFTP_REMOTE_ARCHIVE_DIR/$file"
  done
) > /put-files.ftpbatch

cd "/tmp/ftp-put" || exit 1
cat /put-files.ftpbatch | /ftp-cmd.sh

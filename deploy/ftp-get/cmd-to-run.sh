#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

. /set-env.sh
umask 0000
chown docker:docker "$SFTP_LOCAL_DIR"

mapfile -t files < <( cat /list-files.ftpbatch \
  | envsubst \
  | /ftp-cmd.sh \
  | sed '/^sftp>/d' )
printf "Files to download: "
printf '%s, ' "${files[@]}"

(
  echo cd "$SFTP_REMOTE_DIR"
  for file in "${files[@]}"; do
    echo get "$file"
    echo !chown docker:docker "$file"
    echo !mv "$file" "$SFTP_LOCAL_DIR/$file"
    echo rename "$file" "$SFTP_REMOTE_ARCHIVE_DIR/$file"
  done
) > /get-files.ftpbatch 

cd "/tmp/ftp-get" || exit 1
cat /get-files.ftpbatch | /ftp-cmd.sh

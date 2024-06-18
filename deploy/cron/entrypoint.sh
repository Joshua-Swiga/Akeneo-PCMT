#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

if [ -z "$CRON_SCHEDULE" ]; then
    echo "Cron schedule unset..."
    CRON_SCHEDULE="* * * * *"
fi

if [ ! -x "/cmd-to-run.sh" ]; then
    echo "/cmd-to-run.sh is not executable"
    exit 1
fi

cat <<EOT >/var/spool/cron/crontabs/root
$CRON_SCHEDULE /cmd-to-run.sh > /proc/1/fd/1 2>/proc/1/fd/2
EOT
echo "---Crontab---"
cat /var/spool/cron/crontabs/root

exec "${@}"
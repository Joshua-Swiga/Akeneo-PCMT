#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

sudo bash -c 'echo "docker" >> /etc/cron.allow'
sudo service cron restart

crontab /srv/pim/crontab
sudo service cron restart


#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

FROM pcmt/cron:latest

RUN apk -v --update add \
        libmagic \
        python \
        py-pip \
        && \
    pip install --upgrade s3cmd python-magic && \
    apk -v --purge del py-pip && \
    rm /var/cache/apk/*
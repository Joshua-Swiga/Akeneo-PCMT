######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

FROM hashicorp/terraform:0.12.31
RUN apk add docker bash

ADD script/docker-entrypoint.sh /docker-entrypoint.sh
ADD . /app
WORKDIR /app

ENTRYPOINT ["/docker-entrypoint.sh"]
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

FROM alpine:3.11

RUN apk add bash

ADD entrypoint.sh /entrypoint.sh
ENTRYPOINT ["/entrypoint.sh"]
CMD ["/usr/sbin/crond", "-f"]

ONBUILD ADD cmd-to-run.sh /cmd-to-run.sh
ONBUILD RUN chmod u+x /cmd-to-run.sh
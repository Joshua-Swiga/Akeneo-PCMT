######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

FROM williamyeh/ansible:ubuntu18.04-onbuild

ADD docker-entrypoint.sh /docker-entrypoint.sh
RUN apt-get update && \
    apt-get install python-pip -y && \
    pip install -r requirements/ansible.pip && \
    ansible-galaxy install -p vendor/roles -r requirements/galaxy.yml && \
    chmod 770 /tmp && \
    apt-get clean && \
    apt-get autoremove

ENTRYPOINT ["/docker-entrypoint.sh"]
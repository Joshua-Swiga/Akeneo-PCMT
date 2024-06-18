#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

docker_cmd=${1:-'docker-compose'}
profile=${PCMT_PROFILE:-'dev'}
secret_conf=${PCMT_SECRET_CONF:-'./conf/parameters.yml.dist'}
traefik_conf=${PCMT_TRAEFIK_CONF:-'./conf/traefik.toml.dist'}

echo Starting PCMT with:
echo --- DOCKER COMMAND: $docker_cmd
echo --- PCMT_PROFILE: $profile
echo --- PCMT_SECRET_CONF: $secret_conf
echo --- PCMT_TRAEFIK_CONF: $traefik_conf

PCMT_PROFILE=$profile PCMT_SECRET_CONF=$secret_conf ${docker_cmd} up -d
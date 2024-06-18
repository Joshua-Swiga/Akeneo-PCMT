#!/bin/sh
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

# Usage:  inteded to be used from Terraform's local provisioner.

if [ -z "$PCMT_AWS_CREDS_VOLUME" ]; then
  echo "PCMT_AWS_CREDS_VOLUME not set"
  exit 1
fi

if [ -z "$1" ]; then
    echo IP Argument Missing
    exit 1
fi
TARGET_IP=$1

if [ -z "$2" ]; then
    echo Domain Name Missing
    exit 1
fi
HOSTNAME=$2

if [ ! -z "$PCMT_SECRETS_VOLUME" ]; then
    PCMT_SECRETS_VOLUME="-v $PCMT_SECRETS_VOLUME:/tmp/secrets"
fi

docker run --rm \
    -e PCMT_PROFILE \
    -e PCMT_VER \
    -e PCMT_ASSET_URL \
    $PCMT_SECRETS_VOLUME \
    -v "$PCMT_AWS_CREDS_VOLUME":/tmp/.ssh \
    pcmt/ansible ansible-playbook \
        -v \
        -i "$TARGET_IP", \
        -e ansible_ssh_user=ubuntu \
        -e pcmt_hostname="$HOSTNAME" \
        playbook.yml
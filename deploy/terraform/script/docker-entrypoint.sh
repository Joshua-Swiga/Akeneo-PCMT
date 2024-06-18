#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

TF_ENV=$1
TF_CMD="${@:2}"

if [ ! -r "$AWS_SHARED_CREDENTIALS_FILE" ]; then
  echo "AWS_SHARED_CREDENTIALS_FILE not readable/present: $AWS_SHARED_CREDENTIALS_FILE"
  ls /tmp/.aws
  exit 1
fi

if [ ! -r "/var/run/docker.sock" ]; then
    echo Docker socket not mounted
    exit 1
fi

if [ ! -d "$1" ]; then
    echo "Environment isn't known directory: $1"
    exit 1
fi

SSH_KEY="/tmp/.aws/id_rsa"
if [ ! -r "$SSH_KEY" ] || [ ! -f "$SSH_KEY" ]; then
    echo "SSH Key $SSH_KEY not accessible"
    exit 1
fi

mkdir -p /root/.ssh
cp "$SSH_KEY" /root/.ssh
chmod 700 /root/.ssh
chmod 400 /root/.ssh/*

echo Starting ssh-agent and adding default key
eval "$(ssh-agent -s)"
ssh-add

echo "On environment $TF_ENV, running terraform $TF_CMD"
cd "$TF_ENV" || exit 1

terraform init
terraform "${@:2}"

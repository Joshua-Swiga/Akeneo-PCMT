#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

set -e
DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

: "${AWS_SHARED_CREDENTIALS_FILE:=$HOME/.aws/credentials}"
: "${SSH_PRIV_KEY_PATH:=$HOME/.ssh/id_rsa}"
HELPER_CONTAINER="pcmt-tf-helper"
SECRETS_VOL="pcmt-secrets-tf"
AWS_CREDS_VOL="pcmt-aws-creds-tf"

function cleanup {
    prevExit="$?"
    echo "Cleaning up container and volumes..."
    docker rm "$HELPER_CONTAINER"
    docker volume rm "$AWS_CREDS_VOL"
    docker volume rm "$SECRETS_VOL"
    exit $prevExit
}
trap cleanup EXIT

# copies file whose path is in param 1, into the helper container at path
#  given in param 2
cpFileFromEnvIntoHelper() {
    filePath=$1
    volPath=$2
    if [[ -f "$filePath" && -r "$filePath" ]]; then
        docker cp "$filePath" "$HELPER_CONTAINER":"$volPath"
        echo "Secret set: $volPath"
    else
        echo "Secret not set: $volPath"
    fi
}

# check SSH key is available
if [ ! -r "$SSH_PRIV_KEY_PATH" ] || [ ! -f "$SSH_PRIV_KEY_PATH" ]; then
    echo "SSH Key $SSH_PRIV_KEY_PATH not accessible"
    exit 1
fi

# setup ssh and secrets volumes
docker volume create "$AWS_CREDS_VOL"
docker volume create "$SECRETS_VOL"
docker create --name "$HELPER_CONTAINER" \
    -v "$AWS_CREDS_VOL":/tmp/.aws \
    -v "$SECRETS_VOL":/conf \
    busybox

# copy aws creds into aws creds vol
cpFileFromEnvIntoHelper "$SSH_PRIV_KEY_PATH" "/tmp/.aws/id_rsa"
cpFileFromEnvIntoHelper "$AWS_SHARED_CREDENTIALS_FILE" \
    "/tmp/.aws/aws-credentials"

# copy deploy secrets into secrets volume
cpFileFromEnvIntoHelper "$PCMT_SECRET_CONF" "/conf/parameters.yml.dist"
cpFileFromEnvIntoHelper "$PCMT_MYSQL_CREDS_CONF" "/conf/mysql-creds.env"
cpFileFromEnvIntoHelper "$PCMT_S3_CREDS_CONF" "/conf/aws-s3-creds.env"
cpFileFromEnvIntoHelper "$PCMT_FTP_GET_CREDS_CONF" "/conf/ftp-get-creds.env"
cpFileFromEnvIntoHelper "$PCMT_FTP_PUT_CREDS_CONF" "/conf/ftp-put-creds.env"
cpFileFromEnvIntoHelper "$PCMT_SFTP_PRIVKEY_FILENAME" "/conf/sftp-privkey"
cpFileFromEnvIntoHelper "$PCMT_GRAFANA_CREDS_CONF" "/conf/grafana-creds.json.dist"
cpFileFromEnvIntoHelper "$PCMT_MYSQL_INIT_CONF" "/conf/mysql-init.sql.dist"
cpFileFromEnvIntoHelper "$PCMT_MYSQL_ROOT_PASSWORD_CONF" \
    "/conf/mysql-root-password.dist"
cpFileFromEnvIntoHelper "$PCMT_MYSQL_USERNAME_CONF" \
    "/conf/mysql-username.dist"
cpFileFromEnvIntoHelper "$PCMT_MYSQL_PASSWORD_CONF" \
    "/conf/mysql-password.dist"
cpFileFromEnvIntoHelper "$PCMT_MYSQL_SSH_AUTHORIZED_KEY_CONF" \
    "/conf/ssh_authorized_key"

## detect if dev mode asked for
if [ ! -z ${PCMT_TF_DEV+x} ]; then
    echo "PCMT_TF_DEV set, mounting terraform directly"
fi

docker run --rm \
    -e AWS_SHARED_CREDENTIALS_FILE="/tmp/.aws/aws-credentials" \
    -e PCMT_PROFILE \
    -e PCMT_VER \
    -e PCMT_ASSET_URL \
    -e PCMT_SECRETS_VOLUME="$SECRETS_VOL" \
    -e PCMT_AWS_CREDS_VOLUME="$AWS_CREDS_VOL" \
    -v "$SECRETS_VOL":/conf \
    -v "$AWS_CREDS_VOL":/tmp/.aws \
    -v "/var/run/docker.sock:/var/run/docker.sock" \
    ${PCMT_TF_DEV:+-v "$DIR:/app"} \
    pcmt/terraform "${@}"
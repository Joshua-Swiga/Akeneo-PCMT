#!/bin/bash
######################################################################
# Copyright (c) 2019, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

# finds the ip of the reverse-proxy container through dns

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

reverseProxyIp=$(dig reverse-proxy +short)

if [ -z "$reverseProxyIp" ]; then
  echo "Host not found: reverse-proxy"
  exit 1
fi

echo "TRUSTED_PROXY_IPS=$reverseProxyIp" > "$DIR"/.env
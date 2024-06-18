#!/bin/bash
######################################################################
# Copyright (c) 2020, VillageReach
# Licensed under the Non-Profit Open Software License version 3.0.
# SPDX-License-Identifier: NPOSL-3.0
######################################################################

set -e

DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"

THRESHOLD=$1

if [ "$THRESHOLD" -gt "100" ]
then
  echo "Code coverage test: ERROR! The argument can have a maximum value of 100" 1>&2
  exit 1
fi

FILE="$DIR/../build/coverage.xml"

if [[ ! -f "$FILE" ]]
then
  echo "Code coverage test: ERROR! $FILE doesn't exist!" 1>&2
  exit 1
fi

statements=$(($(xmllint --xpath "string(/coverage/project/metrics/@statements)" "$FILE")))
covered_statements=$(($(xmllint --xpath "string(/coverage/project/metrics/@coveredstatements)" "$FILE")))

coverage=$(bc <<< "scale=2; $covered_statements/$statements")
threshold=$(bc <<< "scale=2; $THRESHOLD/100")

if (( $(echo "$coverage < $threshold" |bc -l) ))
then
  echo "Code coverage test: ERROR! THRESHOLD: $threshold, CURRENT COVERAGE: $coverage. Code coverage is under the limit!" 1>&2
  exit 1
fi

echo "Code coverage test: PASSED. THRESHOLD: $threshold, CURRENT COVERAGE: $coverage."
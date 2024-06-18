<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

$container->setParameter('pcmt_version', $_SERVER['PCMT_VER'] ?? $_ENV['PCMT_VER'] ?? '');
<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

class AttributeMappingTypesChecker
{
    public function checkIfPossible(string $typeSource, string $typeDestination): bool
    {
        if ($typeSource === $typeDestination) {
            return true;
        }
        $types = [
            'pim_catalog_text',
            'pim_catalog_simpleselect',
        ];
        if (in_array($typeSource, $types) && in_array($typeDestination, $types)) {
            return true;
        }

        return false;
    }
}
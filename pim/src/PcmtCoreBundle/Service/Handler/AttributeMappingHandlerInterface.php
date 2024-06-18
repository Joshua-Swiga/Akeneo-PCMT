<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Service\Handler;

use PcmtCoreBundle\Entity\Attribute;

interface AttributeMappingHandlerInterface
{
    public function createMapping(Attribute $mappingAttribute, Attribute $mappedAttribute): void;
}

<?php
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Service\Helper;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use PcmtDraftBundle\Service\Draft\DraftCreatorInterface;

class SpecialCategoryUpdater
{
    /** @var ObjectUpdaterInterface */
    private $objectUpdater;

    public function __construct(ObjectUpdaterInterface $objectUpdater)
    {
        $this->objectUpdater = $objectUpdater;
    }

    public function addSpecialCategory(EntityWithValuesInterface $entity): void
    {
        $categoryData['values'] = [];
        $categoryData['categories'] = [];
        $categoryData['categories'][] = DraftCreatorInterface::CATEGORY_FOR_BASE_PRODUCTS;
        try {
            $this->objectUpdater->update($entity, $categoryData);
        } catch (InvalidPropertyException $e) {
            // silent error when the category does not exist
        }
    }
}
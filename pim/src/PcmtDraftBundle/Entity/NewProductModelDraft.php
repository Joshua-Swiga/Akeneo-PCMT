<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;

class NewProductModelDraft extends AbstractProductModelDraft implements NewObjectDraftInterface
{
    public const TYPE = 'new product model draft';

    public function __construct(
        array $productModelData,
        ?UserInterface $author,
        \DateTime $created
    ) {
        $this->productData = $productModelData;
        parent::__construct($author, $created);
    }
}
<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;

class NewProductDraft extends AbstractProductDraft implements NewObjectDraftInterface
{
    public const TYPE = 'new product draft';

    public function __construct(
        array $productData,
        \DateTime $created,
        ?UserInterface $author = null
    ) {
        $this->productData = $productData;
        parent::__construct($created, $author);
    }
}
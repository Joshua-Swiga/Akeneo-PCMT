<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Saver;

use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;

class ProductImportSaver implements SaverInterface
{
    /** @var SaverInterface */
    private $baseSaver;

    public function __construct(SaverInterface $baseSaver)
    {
        $this->baseSaver = $baseSaver;
    }

    /**
     * {@inheritdoc}
     */
    public function save($object, array $options = []): void
    {
        $this->baseSaver->save($object, $options);
    }
}
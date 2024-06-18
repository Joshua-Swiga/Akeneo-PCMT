<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Category\Model\Category;

class CategoryBuilder
{
    public const EXAMPLE_CODE = 'example_category';

    /** @var Category */
    private $category;

    public function __construct()
    {
        $this->category = new Category();
        $this->category->setCode(self::EXAMPLE_CODE);
    }

    public function withCode(string $code): self
    {
        $this->category->setCode($code);

        return $this;
    }

    public function build(): Category
    {
        return $this->category;
    }
}
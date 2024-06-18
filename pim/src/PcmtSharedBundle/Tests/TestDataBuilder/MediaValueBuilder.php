<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtSharedBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValue;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;

class MediaValueBuilder
{
    /** @var string */
    private $attributeCode;

    /** @var FileInfoInterface */
    private $dataValue;

    /**
     * ValueBuilder constructor.
     */
    public function __construct()
    {
        $this->attributeCode = 'media_attr_code';
    }

    public function withAttributeCode(string $code): self
    {
        $this->attributeCode = $code;

        return $this;
    }

    public function withData(FileInfoInterface $data): self
    {
        $this->dataValue = $data;

        return $this;
    }

    public function build(): ValueInterface
    {
        return MediaValue::value($this->attributeCode, $this->dataValue);
    }
}
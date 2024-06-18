<?php
/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtSharedBundle\Tests\TestDataBuilder;

use Akeneo\Pim\Structure\Component\Model\AssociationType;

class AssociationTypeBuilder
{
    /** @var AssociationType */
    private $associationType;

    public const DEFAULT_TYPE_ID = 12;

    public const DEFAULT_CODE = 'EXAMPLE_CODE';

    public function __construct()
    {
        $this->associationType = new AssociationType();
        $this->withId(self::DEFAULT_TYPE_ID);
        $this->withCode(self::DEFAULT_CODE);
    }

    public function withId(int $id): self
    {
        $this->associationType->setId($id);

        return $this;
    }

    public function withCode(?string $code): self
    {
        $this->associationType->setCode($code);

        return $this;
    }

    public function build(): AssociationType
    {
        return $this->associationType;
    }
}
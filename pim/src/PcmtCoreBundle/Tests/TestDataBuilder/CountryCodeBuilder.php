<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Tests\TestDataBuilder;

use PcmtCoreBundle\Entity\ReferenceData\CountryCode;

class CountryCodeBuilder
{
    /** @var CountryCode */
    private $countryCode;

    public function __construct()
    {
        $this->countryCode = new CountryCode();
    }

    public function withCode(string $code): self
    {
        $this->countryCode->setCode($code);

        return $this;
    }

    public function withName(string $name): self
    {
        $this->countryCode->setName($name);

        return $this;
    }

    public function build(): CountryCode
    {
        return $this->countryCode;
    }
}
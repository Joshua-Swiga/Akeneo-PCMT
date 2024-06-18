<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Entity;

use DateTime;
use PcmtCoreBundle\Entity\ReferenceData\CountryCode;

class Subscription
{
    /** @var int */
    private $id;

    /** @var DateTime */
    private $created;

    /** @var ?DateTime */
    private $updated;

    /** @var ?string */
    private $dataRecipientsGLN;

    /** @var ?string */
    private $dataSourcesGLN;

    /** @var ?string */
    private $GTIN;

    /** @var ?string */
    private $GPCCategoryCode;

    /** @var ?CountryCode */
    private $targetMarketCountryCode;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function setDataRecipientsGLN(string $dataRecipientsGLN): void
    {
        $this->dataRecipientsGLN = $dataRecipientsGLN ?: null;
    }

    public function setDataSourcesGLN(string $dataSourcesGLN): void
    {
        $this->dataSourcesGLN = $dataSourcesGLN ?: null;
    }

    public function setGTIN(string $GTIN): void
    {
        $this->GTIN = $GTIN ?: null;
    }

    public function setGPCCategoryCode(string $GPCCategoryCode): void
    {
        $this->GPCCategoryCode = $GPCCategoryCode ?: null;
    }

    public function setTargetMarketCountryCode(?CountryCode $targetMarketCountryCode): void
    {
        $this->targetMarketCountryCode = $targetMarketCountryCode;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getUpdated(): ?DateTime
    {
        return $this->updated;
    }

    public function getDataRecipientsGLN(): ?string
    {
        return $this->dataRecipientsGLN;
    }

    public function getDataSourcesGLN(): ?string
    {
        return $this->dataSourcesGLN;
    }

    public function getGTIN(): ?string
    {
        return $this->GTIN;
    }

    public function getGPCCategoryCode(): ?string
    {
        return $this->GPCCategoryCode;
    }

    public function getTargetMarketCountryCode(): ?CountryCode
    {
        return $this->targetMarketCountryCode;
    }

    public function updateTimestamps(): void
    {
        $this->updated = new DateTime('now');
        if (null === $this->created) {
            $this->created = new DateTime('now');
        }
    }
}
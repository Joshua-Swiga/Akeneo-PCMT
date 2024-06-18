<?php
/**
 * Copyright (c) 2022, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace FhirBundle\Entity;

class FhirMapping
{
    /** @var int */
    private $id;

    /** @var string */
    private $code;

    /** @var string */
    private $type;

    /** @var string */
    private $mapping;

    /** @return int */
    public function getId(): int
    {
        return $this->id;
    }

    /** @return object */
    public function setId(int $id): object
    {
        $this->id = $id;

        return $this;
    }

    /** @return object */
    public function setCode(string $code): object
    {
        $this->code = $code;

        return $this;
    }

    /** @return string */
    public function getCode(): string
    {
        return $this->code;
    }

    /** @return object */
    public function setType(string $type): object
    {
        $this->type = $type;

        return $this;
    }

    /** @return string */
    public function getType(): string
    {
        return $this->type;
    }

    /** @return object */
    public function setMapping(string $mapping): object
    {
        $this->mapping = $mapping;

        return $this;
    }

    /** @return string */
    public function getMapping(): string
    {
        return $this->mapping;
    }
}
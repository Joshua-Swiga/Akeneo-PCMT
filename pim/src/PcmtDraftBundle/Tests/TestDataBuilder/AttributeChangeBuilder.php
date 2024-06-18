<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\TestDataBuilder;

use PcmtDraftBundle\Entity\AttributeChange;

class AttributeChangeBuilder
{
    public const EXAMPLE_NAME = 'exampleName';

    public const EXAMPLE_PREVIOUS_VALUE = 'prev value';

    public const EXAMPLE_NEW_VALUE = 'new val';

    /** @var string */
    private $name;

    /** @var ?string */
    private $previousValue;

    /** @var ?string */
    private $newValue;

    public function __construct()
    {
        $this->name = self::EXAMPLE_NAME;
        $this->previousValue = self::EXAMPLE_PREVIOUS_VALUE;
        $this->newValue = self::EXAMPLE_NEW_VALUE;
    }

    public function withName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function withPreviousValue(string $previousValue): self
    {
        $this->previousValue = $previousValue;

        return $this;
    }

    public function withNewValue(string $newValue): self
    {
        $this->newValue = $newValue;

        return $this;
    }

    public function build(): AttributeChange
    {
        return new AttributeChange($this->name, $this->previousValue, $this->newValue);
    }
}
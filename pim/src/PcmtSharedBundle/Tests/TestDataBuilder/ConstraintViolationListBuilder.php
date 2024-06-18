<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtSharedBundle\Tests\TestDataBuilder;

use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class ConstraintViolationListBuilder
{
    /** @var ConstraintViolationList */
    private $list;

    public function __construct()
    {
        $this->list = new ConstraintViolationList();
    }

    public function withViolation(ConstraintViolation $violation): self
    {
        $this->list->add($violation);

        return $this;
    }

    public function build(): ConstraintViolationList
    {
        return $this->list;
    }
}
<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtSharedBundle\Tests\TestDataBuilder;

use Symfony\Component\Validator\ConstraintViolation;

class ConstraintViolationBuilder
{
    private const EXAMPLE_MESSAGE = 'example message';
    private const EXAMPLE_MESSAGE_TEMPLATE = 'example template';
    private const EXAMPLE_PARAMETERS = [];
    private const EXAMPLE_ROOT = false;
    private const EXAMPLE_PROPERTY_PATH = 'path';
    private const EXAMPLE_INVALID_VALUE = false;

    /** @var ConstraintViolation */
    private $constraintViolation;

    public function __construct()
    {
        $this->constraintViolation = new ConstraintViolation(
            self::EXAMPLE_MESSAGE,
            self::EXAMPLE_MESSAGE_TEMPLATE,
            self::EXAMPLE_PARAMETERS,
            self::EXAMPLE_ROOT,
            self::EXAMPLE_PROPERTY_PATH,
            self::EXAMPLE_INVALID_VALUE
        );
    }

    public function build(): ConstraintViolation
    {
        return $this->constraintViolation;
    }
}
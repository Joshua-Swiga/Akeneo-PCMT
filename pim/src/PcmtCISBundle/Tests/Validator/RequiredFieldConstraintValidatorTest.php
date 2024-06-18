<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Validator;

use PcmtCISBundle\Entity\Subscription;
use PcmtCISBundle\Tests\TestDataBuilder\RequiredFieldConstraintBuilder;
use PcmtCISBundle\Tests\TestDataBuilder\SubscriptionBuilder;
use PcmtCISBundle\Validator\RequiredFieldConstraintValidator;
use PcmtCoreBundle\Entity\ReferenceData\CountryCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class RequiredFieldConstraintValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface|MockObject */
    private $contextMock;

    /** @var ConstraintViolationBuilderInterface|MockObject */
    private $violationBuilderMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);

        $this->violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->violationBuilderMock->method('atPath')->willReturn($this->violationBuilderMock);
    }

    public function dataValidate(): array
    {
        $countryCode = new CountryCode();
        $countryCode->setCode('dddd');

        $sub1 = (new SubscriptionBuilder())->withGTIN('ffff')->withGPCCategoryCode('ddd')->withTargetMarketCountryCode($countryCode)->build();
        $sub2 = (new SubscriptionBuilder())->withGTIN('')->withGPCCategoryCode('')->withTargetMarketCountryCode($countryCode)->build();
        $sub3 = (new SubscriptionBuilder())->withGTIN('')->withGPCCategoryCode('xx')->withTargetMarketCountryCode(null)->build();
        $sub4 = (new SubscriptionBuilder())->withGTIN('')->withGPCCategoryCode('')->withTargetMarketCountryCode(null)->build();

        return [
            [$sub1, true],
            [$sub2, true],
            [$sub3, true],
            [$sub4, false],
        ];
    }

    /** @dataProvider dataValidate */
    public function testValidate(Subscription $subscription, bool $result): void
    {
        $constraint = (new RequiredFieldConstraintBuilder())->build();

        if ($result) {
            $this->contextMock->expects($this->never())->method('buildViolation');
        } else {
            $this->contextMock->expects($this->once())->method('buildViolation')->willReturn($this->violationBuilderMock);
        }

        $validator = $this->getValidatorInstance();
        $validator->validate($subscription, $constraint);
    }

    public function testValidateThrowsException(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $constraint = $this->createMock(Constraint::class);
        $sub = (new SubscriptionBuilder())->build();

        $validator = $this->getValidatorInstance();
        $validator->validate($sub, $constraint);
    }

    private function getValidatorInstance(): RequiredFieldConstraintValidator
    {
        $validator = new RequiredFieldConstraintValidator();
        $validator->initialize($this->contextMock);

        return $validator;
    }
}
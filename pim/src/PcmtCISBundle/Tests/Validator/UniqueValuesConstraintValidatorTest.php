<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Tests\Validator;

use PcmtCISBundle\Entity\Subscription;
use PcmtCISBundle\Repository\SubscriptionRepository;
use PcmtCISBundle\Tests\TestDataBuilder\SubscriptionBuilder;
use PcmtCISBundle\Tests\TestDataBuilder\UniqueValuesConstraintBuilder;
use PcmtCISBundle\Validator\UniqueValuesConstraintValidator;
use PcmtCoreBundle\Entity\ReferenceData\CountryCode;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class UniqueValuesConstraintValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface|MockObject */
    private $contextMock;

    /** @var ConstraintViolationBuilderInterface|MockObject */
    private $violationBuilderMock;

    /** @var SubscriptionRepository|MockObject */
    private $subscriptionRepositoryMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);

        $this->violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->violationBuilderMock->method('atPath')->willReturn($this->violationBuilderMock);

        $this->subscriptionRepositoryMock = $this->createMock(SubscriptionRepository::class);
    }

    public function dataValidate(): array
    {
        $countryCode = new CountryCode();
        $countryCode->setCode('dddd');

        $sub1 = (new SubscriptionBuilder())
            ->withGTIN('ffff')
            ->withGPCCategoryCode('ddd')
            ->withTargetMarketCountryCode($countryCode)
            ->build();

        $criteria1 = [
            'dataRecipientsGLN'       => $sub1->getDataRecipientsGLN(),
            'dataSourcesGLN'          => $sub1->getDataSourcesGLN(),
            'GTIN'                    => 'ffff',
            'GPCCategoryCode'         => 'ddd',
            'targetMarketCountryCode' => $countryCode,
        ];

        $sub2 = (new SubscriptionBuilder())
            ->withGTIN('aaa')
            ->withGPCCategoryCode('ee')
            ->build();

        $criteria2 = [
            'dataRecipientsGLN'       => $sub2->getDataRecipientsGLN(),
            'dataSourcesGLN'          => $sub2->getDataSourcesGLN(),
            'GTIN'                    => 'aaa',
            'GPCCategoryCode'         => 'ee',
            'targetMarketCountryCode' => null,
        ];

        return [
            [$sub1, $criteria1, 1, true],
            [$sub2, $criteria2, 0, false],
        ];
    }

    /** @dataProvider dataValidate */
    public function testValidate(Subscription $subscription, array $criteria, int $results, bool $violationBuilt): void
    {
        $constraint = (new UniqueValuesConstraintBuilder())->build();

        $this->subscriptionRepositoryMock
            ->expects($this->once())
            ->method('count')
            ->with($criteria)
            ->willReturn($results);

        if (!$violationBuilt) {
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

    private function getValidatorInstance(): UniqueValuesConstraintValidator
    {
        $validator = new UniqueValuesConstraintValidator($this->subscriptionRepositoryMock);
        $validator->initialize($this->contextMock);

        return $validator;
    }
}
<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Validators;

use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PcmtRulesBundle\Tests\TestDataBuilder\DifferentFamilyConstraintBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Validators\DifferentFamilyConstraintValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class DifferentFamilyConstraintValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface|MockObject */
    private $contextMock;

    /** @var ConstraintViolationBuilderInterface|MockObject */
    private $violationBuilderMock;

    /** @var FamilyRepositoryInterface|MockObject */
    private $familyRepositoryMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);

        $this->violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->violationBuilderMock->method('atPath')->willReturn($this->violationBuilderMock);

        $this->familyRepositoryMock = $this->createMock(FamilyRepositoryInterface::class);
    }

    public function testValidateWhenFamiliesAreDifferent(): void
    {
        $family1 = (new FamilyBuilder())->withCode('xxx')->withId(1)->build();
        $family2 = (new FamilyBuilder())->withCode('yyy')->withId(2)->build();

        $this->familyRepositoryMock
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls(
                $family1,
                $family2
            );

        $constraint = (new DifferentFamilyConstraintBuilder())->build();

        $this->contextMock->expects($this->never())->method('buildViolation');

        $root = [
            'sourceFamily'      => $family1->getCode(),
            'destinationFamily' => $family2->getCode(),
            'keyAttribute'      => 'test',
            'user_to_notify'    => '',
        ];

        $this->contextMock
            ->method('getRoot')
            ->willReturn($root);

        $validator = $this->getValidatorInstance();
        $validator->validate('xxx', $constraint);
    }

    public function testValidateWhenFamiliesAreTheSame(): void
    {
        $family = (new FamilyBuilder())->withCode('xxx')->withId(1)->build();

        $this->familyRepositoryMock
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls(
                $family,
                $family
            );

        $constraint = (new DifferentFamilyConstraintBuilder())->build();

        $this->contextMock->expects($this->once())->method('buildViolation')->willReturn($this->violationBuilderMock);

        $root = [
            'sourceFamily'      => $family->getCode(),
            'destinationFamily' => $family->getCode(),
            'keyAttribute'      => 'test',
            'user_to_notify'    => '',
        ];

        $this->contextMock
            ->method('getRoot')
            ->willReturn($root);

        $validator = $this->getValidatorInstance();
        $validator->validate('xxx', $constraint);
    }

    public function testValidateWhenOneFamilyIsNotFound(): void
    {
        $family1 = (new FamilyBuilder())->withCode('xxx')->withId(1)->build();

        $this->familyRepositoryMock
            ->method('findOneBy')
            ->willReturnOnConsecutiveCalls(
                $family1,
                null
            );

        $constraint = (new DifferentFamilyConstraintBuilder())->build();

        $this->contextMock->expects($this->never())->method('buildViolation');

        $root = [
            'sourceFamily'      => $family1->getCode(),
            'destinationFamily' => '',
            'keyAttribute'      => 'test',
            'user_to_notify'    => '',
        ];

        $this->contextMock
            ->method('getRoot')
            ->willReturn($root);

        $validator = $this->getValidatorInstance();
        $validator->validate('xxx', $constraint);
    }

    public function testValidateThrowsException(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $constraint = $this->createMock(Constraint::class);

        $validator = $this->getValidatorInstance();
        $validator->validate('xxx', $constraint);
    }

    private function getValidatorInstance(): DifferentFamilyConstraintValidator
    {
        $validator = new DifferentFamilyConstraintValidator(
            $this->familyRepositoryMock
        );
        $validator->initialize($this->contextMock);

        return $validator;
    }
}

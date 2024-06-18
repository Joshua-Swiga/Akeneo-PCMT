<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Validators;

use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyHasVariantsConstraintBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyVariantBuilder;
use PcmtRulesBundle\Validators\FamilyHasVariantsConstraintValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class FamilyHasVariantsConstraintValidatorTest extends TestCase
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

    public function dataValidate(): array
    {
        $variantCollection = new ArrayCollection();
        $variantCollection->add((new FamilyVariantBuilder())->build());

        $family1 = (new FamilyBuilder())->withCode('xxx')->withId(1)->build();
        $family2 = (new FamilyBuilder())->withCode('yyy')->withId(2)
            ->withFamilyVariants($variantCollection)
            ->build();

        return [
            [$family1, 1],
            [$family2, 0],
        ];
    }

    /**
     * @dataProvider dataValidate
     */
    public function testValidate(FamilyInterface $family, int $expectedViolations): void
    {
        $this->familyRepositoryMock
            ->method('findOneBy')
            ->willReturn($family);

        $constraint = (new FamilyHasVariantsConstraintBuilder())->build();

        $this->contextMock->expects($this->exactly($expectedViolations))
            ->method('buildViolation')
            ->willReturn($this->violationBuilderMock);

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

    private function getValidatorInstance(): FamilyHasVariantsConstraintValidator
    {
        $validator = new FamilyHasVariantsConstraintValidator(
            $this->familyRepositoryMock
        );
        $validator->initialize($this->contextMock);

        return $validator;
    }
}

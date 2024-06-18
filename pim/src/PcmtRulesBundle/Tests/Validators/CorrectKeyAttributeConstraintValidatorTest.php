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
use PcmtRulesBundle\Service\AttributeMappingTypesChecker;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\CorrectKeyAttributeConstraintBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Validators\CorrectKeyAttributeConstraintValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class CorrectKeyAttributeConstraintValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface|MockObject */
    private $contextMock;

    /** @var ConstraintViolationBuilderInterface|MockObject */
    private $violationBuilderMock;

    /** @var FamilyRepositoryInterface|MockObject */
    private $familyRepositoryMock;

    /** @var AttributeMappingTypesChecker|MockObject */
    private $attributeMappingTypesCheckerMock;

    protected function setUp(): void
    {
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);

        $this->violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->violationBuilderMock->method('atPath')->willReturn($this->violationBuilderMock);

        $this->familyRepositoryMock = $this->createMock(FamilyRepositoryInterface::class);

        $this->attributeMappingTypesCheckerMock = $this->createMock(AttributeMappingTypesChecker::class);
    }

    public function dataValidate(): array
    {
        $sourceFamily = (new FamilyBuilder())->withCode('SOURCE')->build();
        $sourceFamily->addAttribute(
            (new AttributeBuilder())->withCode('attr_source')->withType('type1')->build()
        );
        $destinationFamily = (new FamilyBuilder())->withCode('DESTINATION')->build();
        $destinationFamily->addAttribute(
            (new AttributeBuilder())->withCode('attr_dest')->withType('type1')->build()
        );
        $destinationFamily->addAttribute(
            (new AttributeBuilder())->withCode('attr_dest2')->withType('type2')->build()
        );

        return [
            [
                [],
                $sourceFamily,
                $destinationFamily,
                false,
            ],
            [
                [
                    'sourceKeyAttribute'      => '',
                    'destinationKeyAttribute' => '',
                ],
                $sourceFamily,
                $destinationFamily,
                false,
            ],
            [
                [
                    'sourceKeyAttribute'      => '',
                    'destinationKeyAttribute' => 'attr_dest',
                ],
                $sourceFamily,
                $destinationFamily,
                false,
            ],
            [
                [
                    'sourceKeyAttribute'      => 'attr_source',
                    'destinationKeyAttribute' => 'attr_dest',
                ],
                $sourceFamily,
                $destinationFamily,
                true,
            ],
            [
                [
                    'sourceKeyAttribute'      => 'attr_source_not_existing',
                    'destinationKeyAttribute' => 'attr_dest1',
                ],
                $sourceFamily,
                $destinationFamily,
                false,
            ],
            [
                [
                    'sourceKeyAttribute'      => 'attr_source',
                    'destinationKeyAttribute' => 'attr_dest_not_existing',
                ],
                $sourceFamily,
                $destinationFamily,
                false,
            ],
        ];
    }

    /** @dataProvider dataValidate */
    public function testValidate(array $keyAttribute, FamilyInterface $sourceFamily, FamilyInterface $destinationFamily, bool $result): void
    {
        $this->attributeMappingTypesCheckerMock->method('checkIfPossible')->willReturn(true);

        $constraint = (new CorrectKeyAttributeConstraintBuilder())->build();

        $this->familyRepositoryMock
            ->method('findOneBy')
            ->withConsecutive(
                [['code' => $sourceFamily->getCode()]],
                [['code' => $destinationFamily->getCode()]]
            )
            ->willReturnOnConsecutiveCalls(
                $sourceFamily,
                $destinationFamily
            );

        $root = [
            'sourceFamily'      => $sourceFamily->getCode(),
            'destinationFamily' => $destinationFamily->getCode(),
            'keyAttribute'      => $keyAttribute,
        ];
        $this->contextMock
            ->method('getRoot')
            ->willReturn($root);

        if ($result) {
            $this->contextMock->expects($this->never())->method('buildViolation');
        } else {
            $this->contextMock->expects($this->once())->method('buildViolation')->willReturn(
                $this->violationBuilderMock
            );
        }

        $validator = $this->getValidatorInstance();
        $validator->validate('x', $constraint);
    }

    public function dataValidateWrongAttributes(): array
    {
        $sourceFamily = (new FamilyBuilder())->withCode('SOURCE')->build();
        $sourceFamily->addAttribute(
            (new AttributeBuilder())->withCode('attr_source')->withType('type1')->build()
        );
        $destinationFamily = (new FamilyBuilder())->withCode('DESTINATION')->build();
        $destinationFamily->addAttribute(
            (new AttributeBuilder())->withCode('attr_dest')->withType('type1')->build()
        );

        return [
            [
                [
                    'sourceKeyAttribute'      => 'attr_source',
                    'destinationKeyAttribute' => 'attr_dest',
                ],
                $sourceFamily,
                $destinationFamily,
            ],
        ];
    }

    /** @dataProvider dataValidateWrongAttributes */
    public function testValidateWrongAttributes(array $keyAttribute, FamilyInterface $sourceFamily, FamilyInterface $destinationFamily): void
    {
        $this->attributeMappingTypesCheckerMock->method('checkIfPossible')->willReturn(false);
        $constraint = (new CorrectKeyAttributeConstraintBuilder())->build();

        $this->familyRepositoryMock
            ->method('findOneBy')
            ->withConsecutive(
                [['code' => $sourceFamily->getCode()]],
                [['code' => $destinationFamily->getCode()]]
            )
            ->willReturnOnConsecutiveCalls(
                $sourceFamily,
                $destinationFamily
            );

        $root = [
            'sourceFamily'      => $sourceFamily->getCode(),
            'destinationFamily' => $destinationFamily->getCode(),
            'keyAttribute'      => $keyAttribute,
        ];
        $this->contextMock
            ->method('getRoot')
            ->willReturn($root);

        $this->contextMock->expects($this->once())->method('buildViolation')->willReturn(
            $this->violationBuilderMock
        );

        $validator = $this->getValidatorInstance();
        $validator->validate('x', $constraint);
    }

    /** @dataProvider dataValidate */
    public function testValidateNoFamily(array $keyAttribute, FamilyInterface $sourceFamily, FamilyInterface $destinationFamily, bool $result): void
    {
        $constraint = (new CorrectKeyAttributeConstraintBuilder())->build();

        $this->familyRepositoryMock
            ->method('findOneBy')
            ->withConsecutive(
                [['code' => $sourceFamily->getCode()]],
                [['code' => $destinationFamily->getCode()]]
            )
            ->willReturnOnConsecutiveCalls(
                $sourceFamily,
                null
            );

        $root = [
            'sourceFamily'      => $sourceFamily->getCode(),
            'destinationFamily' => $destinationFamily->getCode(),
            'keyAttribute'      => $keyAttribute,
        ];
        $this->contextMock
            ->method('getRoot')
            ->willReturn($root);

        $this->contextMock->expects($this->never())->method('buildViolation');

        $validator = $this->getValidatorInstance();
        $validator->validate('x', $constraint);
    }

    public function testValidateThrowsException(): void
    {
        $this->expectException(UnexpectedTypeException::class);
        $constraint = $this->createMock(Constraint::class);

        $value = [
            'sourceFamily'      => 'SOURCE',
            'destinationFamily' => 'DESTINATION',
            'keyAttribute'      => '',
            'user_to_notify'    => '',
        ];

        $validator = $this->getValidatorInstance();
        $validator->validate($value, $constraint);
    }

    private function getValidatorInstance(): CorrectKeyAttributeConstraintValidator
    {
        $validator = new CorrectKeyAttributeConstraintValidator(
            $this->familyRepositoryMock,
            $this->attributeMappingTypesCheckerMock
        );
        $validator->initialize($this->contextMock);

        return $validator;
    }
}

<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Validators;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use PcmtRulesBundle\Service\RuleAttributeProvider;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeExistsInBothFamiliesConstraintBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\FamilyBuilder;
use PcmtRulesBundle\Validators\AttributeExistsInBothFamiliesConstraintValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributeExistsInBothFamiliesConstraintValidatorTest extends TestCase
{
    /** @var RuleAttributeProvider|MockObject */
    private $ruleAttributeProviderMock;

    /** @var ExecutionContextInterface|MockObject */
    private $contextMock;

    /** @var ConstraintViolationBuilderInterface|MockObject */
    private $violationBuilderMock;

    /** @var FamilyRepositoryInterface|MockObject */
    private $familyRepositoryMock;

    /** @var AttributeRepositoryInterface|MockObject */
    private $attributeRepositoryMock;

    protected function setUp(): void
    {
        $this->ruleAttributeProviderMock = $this->createMock(RuleAttributeProvider::class);
        $this->contextMock = $this->createMock(ExecutionContextInterface::class);

        $this->violationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->violationBuilderMock->method('atPath')->willReturn($this->violationBuilderMock);

        $this->familyRepositoryMock = $this->createMock(FamilyRepositoryInterface::class);
        $this->attributeRepositoryMock = $this->createMock(AttributeRepositoryInterface::class);
    }

    public function dataValidate(): array
    {
        $a1 = (new AttributeBuilder())->withCode('AAA1')->build();
        $a2 = (new AttributeBuilder())->withCode('AAA2')->build();

        return [
            [
                $a1,
                [
                    $a1,
                    $a2,
                ],
                true,
            ],
            [
                $a1,
                [$a2],
                false,
            ],
            [
                $a2,
                [$a2],
                true,
            ],
            [
                $a2,
                [],
                false,
            ],
        ];
    }

    /** @dataProvider dataValidate */
    public function testValidate(?AttributeInterface $keyAttribute, array $attributes, bool $result): void
    {
        $this->ruleAttributeProviderMock->method('getPossibleForKeyAttribute')->willReturn($attributes);
        $constraint = (new AttributeExistsInBothFamiliesConstraintBuilder())->build();

        $sourceFamily = (new FamilyBuilder())->withCode('SOURCE')->build();
        $destinationFamily = (new FamilyBuilder())->withCode('DESTINATION')->build();

        $this->familyRepositoryMock
            ->method('findOneBy')
            ->withConsecutive(
                [['code' => 'SOURCE']],
                [['code' => 'DESTINATION']]
            )
            ->willReturnOnConsecutiveCalls(
                $sourceFamily,
                $destinationFamily
            );

        $this->attributeRepositoryMock
            ->method('findOneBy')
            ->willReturn($keyAttribute);

        $root = [
            'sourceFamily'      => 'SOURCE',
            'destinationFamily' => 'DESTINATION',
            'keyAttribute'      => $keyAttribute->getCode(),
            'user_to_notify'    => '',
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
        $validator->validate($keyAttribute->getCode(), $constraint);
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

    public function testValidateWhenKeyAttributeIsNotFound(): void
    {
        $this->attributeRepositoryMock
            ->method('findOneBy')
            ->willReturn(null);

        $root = [
            'sourceFamily'      => 'SOURCE',
            'destinationFamily' => 'DESTINATION',
            'keyAttribute'      => '',
            'user_to_notify'    => '',
        ];
        $this->contextMock
            ->method('getRoot')
            ->willReturn($root);

        $this->ruleAttributeProviderMock
            ->expects($this->never())
            ->method('getPossibleForKeyAttribute');

        $constraint = (new AttributeExistsInBothFamiliesConstraintBuilder())->build();
        $sourceFamily = (new FamilyBuilder())->withCode('SOURCE')->build();
        $destinationFamily = (new FamilyBuilder())->withCode('DESTINATION')->build();

        $this->familyRepositoryMock
            ->method('findOneBy')
            ->withConsecutive(
                [['code' => 'SOURCE']],
                [['code' => 'DESTINATION']]
            )
            ->willReturnOnConsecutiveCalls(
                $sourceFamily,
                $destinationFamily
            );

        $validator = $this->getValidatorInstance();
        $validator->validate('', $constraint);
    }

    private function getValidatorInstance(): AttributeExistsInBothFamiliesConstraintValidator
    {
        $validator = new AttributeExistsInBothFamiliesConstraintValidator(
            $this->ruleAttributeProviderMock,
            $this->familyRepositoryMock,
            $this->attributeRepositoryMock
        );
        $validator->initialize($this->contextMock);

        return $validator;
    }
}

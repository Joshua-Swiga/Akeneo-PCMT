<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Tests\Validator;

use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use PcmtCoreBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtCoreBundle\Validator\AttributeValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

class AttributeValidatorTest extends TestCase
{
    /** @var ExecutionContextInterface|MockObject */
    protected $executionContextMock;

    /** @var ConstraintViolationBuilderInterface|MockObject */
    protected $constraintViolationBuilderMock;

    protected function setUp(): void
    {
        $this->executionContextMock = $this->createMock(ExecutionContextInterface::class);
    }

    /**
     * @dataProvider dataValidateConcatenatedWithViolation
     */
    public function testValidateConcatenatedWithViolation(Attribute $attribute): void
    {
        $this->constraintViolationBuilderMock = $this->createMock(ConstraintViolationBuilderInterface::class);
        $this->constraintViolationBuilderMock->expects($this->once())->method('addViolation');
        $this->executionContextMock->expects($this->once())->method('buildViolation')->willReturn($this->constraintViolationBuilderMock);

        AttributeValidator::validateConcatenated($attribute, $this->executionContextMock, null);
    }

    public function dataValidateConcatenatedWithViolation(): array
    {
        return [
            [(new AttributeBuilder())
                ->withProperties([])
                ->buildConcatenated(), ],
            [(new AttributeBuilder())
                ->withProperties([
                    'attributes' => '',
                    'separators' => '',
                ])
                ->buildConcatenated(), ],
            [(new AttributeBuilder())
                ->withProperties([
                    'attributes' => 'A1,A2',
                    'separators' => '',
                ])
                ->buildConcatenated(), ],
            [(new AttributeBuilder())
                ->withProperties([
                    'attributes' => 'A1',
                    'separators' => ':',
                ])
                ->buildConcatenated(), ],
            [(new AttributeBuilder())
                ->withProperties([
                    'attributes' => 'A1,',
                    'separators' => ':',
                ])
                ->buildConcatenated(), ],
            [(new AttributeBuilder())
                ->withProperties([
                    'attributes' => ',A1',
                    'separators' => ':',
                ])
                ->buildConcatenated(), ],
            [(new AttributeBuilder())
                ->withProperties([
                    'attributes' => 'A1,A2,A3',
                    'separators' => ':',
                ])
                ->buildConcatenated(), ],
        ];
    }

    /**
     * @dataProvider dataValidateConcatenatedNoViolation
     */
    public function testValidateConcatenatedNoViolation(Attribute $attribute): void
    {
        $this->executionContextMock->expects($this->never())->method('buildViolation');

        AttributeValidator::validateConcatenated($attribute, $this->executionContextMock, null);
    }

    public function dataValidateConcatenatedNoViolation(): array
    {
        return [
            [(new AttributeBuilder())
                ->withType(PcmtAtributeTypes::CONCATENATED_FIELDS . 'xxx')
                ->withProperties([])
                ->build(), ],
            [(new AttributeBuilder())
                ->withProperties([
                    'attributes' => 'A1,A2',
                    'separators' => ';',
                ])
                ->buildConcatenated(), ],
            [(new AttributeBuilder())
                ->withProperties([
                    'attributes' => 'A1,A2,A3',
                    'separators' => ':,:',
                ])
                ->buildConcatenated(), ],
            [(new AttributeBuilder())
                ->withProperties([
                    'attributes' => 'A1,A2,,',
                    'separators' => ':',
                ])
                ->buildConcatenated(), ],
            [(new AttributeBuilder())
                ->withProperties([
                    'attributes' => 'A1,A2,,',
                    'separators' => ':,*,&,(',
                ])
                ->buildConcatenated(), ],
        ];
    }
}
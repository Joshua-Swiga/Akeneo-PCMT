<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Structure\Bundle\Doctrine\ORM\Repository\AttributeRepository;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use PcmtDraftBundle\Normalizer\ProductViolationWithLabelNormalizer;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;

class ProductViolationWithLabelNormalizerTest extends TestCase
{
    /**
     * @dataProvider dataWithAttributeLabel
     */
    public function testNormalizeAttributeAndReturnAttributeLabelInsteadOfAttributeCode(
        ConstraintViolation $violation,
        AttributeRepositoryInterface $attributeRepositoryMock,
        string $label
    ): void {
        $productApproveViolationNormalizer = new ProductViolationWithLabelNormalizer($attributeRepositoryMock);

        $result = $productApproveViolationNormalizer->normalize($violation);
        $this->assertSame($result['attribute'], $label);
    }

    /**
     * @dataProvider dataWithFamilyOnly
     */
    public function testNormalizerWillNotCallGetLabelForFamily(
        ConstraintViolation $violation,
        AttributeRepositoryInterface $attributeRepositoryMock
    ): void {
        $productApproveViolationNormalizer = new ProductViolationWithLabelNormalizer($attributeRepositoryMock);
        $productApproveViolationNormalizer->normalize($violation);
    }

    public function dataWithAttributeLabel(): array
    {
        $label = 'Label';
        $attributeMock = $this->getAttributeMockInstance($label);
        $attributeRepositoryMock = $this->createMock(AttributeRepository::class);
        $attributeRepositoryMock->method('getIdentifierCode')->willReturn('fake_code');
        $attributeRepositoryMock->method('findOneByIdentifier')->willReturn($attributeMock);

        return [
            'family with label'     => [
                'violation'               => $this->getViolationMockInstance('family'),
                'attributeRepositoryMock' => $attributeRepositoryMock,
                'label'                   => 'family',
            ],
            'identifier with label' => [
                'violation'               => $this->getViolationMockInstance('identifier'),
                'attributeRepositoryMock' => $attributeRepositoryMock,
                'label'                   => $label,
            ],
        ];
    }

    public function dataWithFamilyOnly(): array
    {
        $attributeRepositoryMock = $this->createMock(AttributeRepository::class);
        $attributeRepositoryMock->method('getIdentifierCode')->willReturn('fake_code');
        $attributeRepositoryMock->method('findOneByIdentifier')->willReturn(null);
        $attributeRepositoryMock->expects($this->never())->method('findOneByIdentifier');

        return [
            'family with label'     => [
                'violation'               => $this->getViolationMockInstance('family'),
                'attributeRepositoryMock' => $attributeRepositoryMock,
            ],
        ];
    }

    private function getAttributeMockInstance(string $label): AttributeInterface
    {
        $attributeMock = $this->createMock(AttributeInterface::class);
        $attributeMock->method('getLabel')->willReturn($label);

        return $attributeMock;
    }

    private function getViolationMockInstance(string $propertyPath): ConstraintViolation
    {
        $violationMock = $this->createMock(ConstraintViolation::class);
        $violationMock->method('getPropertyPath')->willReturn($propertyPath);
        $violationMock->method('getMessage')->willReturn('Message from Mars');

        return $violationMock;
    }
}
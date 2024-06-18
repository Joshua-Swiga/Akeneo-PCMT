<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\Product;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtDraftBundle\Entity\AttributeChange;
use PcmtDraftBundle\Normalizer\AttributeChangeNormalizer;
use PcmtDraftBundle\Normalizer\GeneralDraftNormalizer;
use PcmtDraftBundle\Normalizer\PermissionsHelper;
use PcmtDraftBundle\Normalizer\ProductDraftNormalizer;
use PcmtDraftBundle\Service\AttributeChange\AttributeChangeService;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtDraftBundle\Tests\TestDataBuilder\AttributeChangeBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductDraftNormalizerTest extends TestCase
{
    /** @var AttributeChangeNormalizer */
    private $attributeChangeNormalizer;

    /** @var NormalizerInterface|MockObject */
    private $productNormalizerMock;

    /** @var GeneralObjectFromDraftCreator|MockObject */
    private $creatorMock;

    /** @var AttributeChangeService|MockObject */
    private $attributeChangeServiceMock;

    /** @var FormProviderInterface|MockObject */
    private $formProviderMock;

    /** @var NormalizerInterface|MockObject */
    private $valuesNormalizerMock;

    /** @var GeneralDraftNormalizer|MockObject */
    private $generalDraftNormalizerMock;

    /** @var PermissionsHelper|MockObject */
    private $permissionsHelperMock;

    /** @var UserContext|MockObject */
    private $userContextMock;

    protected function setUp(): void
    {
        $this->generalDraftNormalizerMock = $this->createMock(GeneralDraftNormalizer::class);

        $value = $this->createMock(ValueInterface::class);
        $productNew = (new ProductBuilder())->addValue($value)->build();

        $this->attributeChangeNormalizer = new AttributeChangeNormalizer();

        $this->creatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->creatorMock->method('getObjectToCompare')->willReturn($productNew);

        $this->attributeChangeServiceMock = $this->createMock(AttributeChangeService::class);

        $this->productNormalizerMock = $this->createMock(NormalizerInterface::class);

        $this->formProviderMock = $this->createMock(FormProviderInterface::class);

        $this->valuesNormalizerMock = $this->createMock(NormalizerInterface::class);
        $this->valuesNormalizerMock->method('normalize')->willReturn([]);
        $this->valuesNormalizerMock->method('supportsNormalization')->willReturn(true);

        $this->permissionsHelperMock = $this->createMock(PermissionsHelper::class);

        $this->userContextMock = $this->createMock(UserContext::class);

        parent::setUp();
    }

    public function testNormalizeNoChangesNewProduct(): void
    {
        $productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = (new NewProductDraftBuilder())->build();

        $array = $productDraftNormalizer->normalize($draft);

        $this->assertEmpty($array['changes']);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('values', $array);
        $this->assertIsArray($array['values']);

        $this->assertArrayHasKey('draftId', $array['values']);
        $this->assertArrayHasKey('identifier', $array['values']);
        $this->assertArrayHasKey('family', $array['values']);
        $this->assertArrayHasKey('parentId', $array['values']);
        $this->assertArrayHasKey('parent', $array['values']);
    }

    public function testNormalizeChangesNewProduct(): void
    {
        $changes = [
            new AttributeChange('atName', null, 'newVal'),
        ];
        $this->attributeChangeServiceMock->method('get')->willReturn($changes);

        $productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = (new NewProductDraftBuilder())->build();

        $array = $productDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
    }

    public function testNormalizeChangesExistingProduct(): void
    {
        $changes = [
            (new AttributeChangeBuilder())->build(),
        ];
        $this->attributeChangeServiceMock->method('get')->willReturn($changes);

        $productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = (new ExistingProductDraftBuilder())->build();

        $array = $productDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(1, $array['changes']);
        $this->assertCount(1, $array['values']['values']);
    }

    public function testNormalizeWhenContextIsEmptyThenShouldNotReturnProduct(): void
    {
        $productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = (new ExistingProductDraftBuilder())->build();

        $array = $productDraftNormalizer->normalize($draft);

        $this->assertArrayNotHasKey('product', $array);
    }

    public function testNormalizeWhenContextHasProductIncludedThenShouldReturnNormalizedProduct(): void
    {
        $formName = 'form-xxx';
        $this->formProviderMock->method('getForm')->willReturn($formName);

        $productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = (new ExistingProductDraftBuilder())->build();

        $array = $productDraftNormalizer->normalize($draft, null, ['include_product' => true]);

        $this->assertArrayHasKey('product', $array);
        $this->assertSame($formName, $array['product']['meta']['form']);
    }

    public function testNormalizeWhenNoProductToCompare(): void
    {
        $this->creatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->creatorMock->method('getObjectToCompare')->willReturn(null);

        $productDraftNormalizer = $this->getProductDraftNormalizerInstance();

        $draft = (new ExistingProductDraftBuilder())->build();

        $array = $productDraftNormalizer->normalize($draft);

        $this->assertIsArray($array);
        $this->assertArrayNotHasKey('label', $array);
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(object $object, bool $expectedResult): void
    {
        $normalizer = $this->getProductDraftNormalizerInstance();
        $result = $normalizer->supportsNormalization($object);
        $this->assertSame($expectedResult, $result);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [(new ExistingProductDraftBuilder())->build(), true],
            [(new NewProductDraftBuilder())->build(), true],
            [new Product(), false],
        ];
    }

    private function getProductDraftNormalizerInstance(): ProductDraftNormalizer
    {
        return new ProductDraftNormalizer(
            $this->attributeChangeServiceMock,
            $this->attributeChangeNormalizer,
            $this->formProviderMock,
            $this->productNormalizerMock,
            $this->generalDraftNormalizerMock,
            $this->creatorMock,
            $this->valuesNormalizerMock,
            $this->permissionsHelperMock,
            $this->userContextMock
        );
    }
}
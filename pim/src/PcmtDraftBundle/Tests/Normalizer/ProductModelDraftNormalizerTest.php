<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Tests\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Platform\Bundle\UIBundle\Provider\Form\FormProviderInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use PcmtDraftBundle\Entity\ProductModelDraftInterface;
use PcmtDraftBundle\Normalizer\AttributeChangeNormalizer;
use PcmtDraftBundle\Normalizer\GeneralDraftNormalizer;
use PcmtDraftBundle\Normalizer\PermissionsHelper;
use PcmtDraftBundle\Normalizer\ProductModelDraftNormalizer;
use PcmtDraftBundle\Service\AttributeChange\AttributeChangeService;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use PcmtDraftBundle\Tests\TestDataBuilder\AttributeChangeBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ExistingProductModelDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\NewProductModelDraftBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PcmtDraftBundle\Tests\TestDataBuilder\UserBuilder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ProductModelDraftNormalizerTest extends TestCase
{
    /** @var AttributeChangeNormalizer */
    private $attributeChangeNormalizer;

    /** @var NormalizerInterface|MockObject */
    private $productModelNormalizerMock;

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

        $productModelNew = (new ProductModelBuilder())->build();

        $value = $this->createMock(ValueInterface::class);
        $productModelNew->addValue($value);

        $this->attributeChangeNormalizer = new AttributeChangeNormalizer();

        $this->creatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->creatorMock->method('getObjectToCompare')->willReturn($productModelNew);

        $this->attributeChangeServiceMock = $this->createMock(AttributeChangeService::class);
        $this->productModelNormalizerMock = $this->createMock(NormalizerInterface::class);

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
        $productModelDraftNormalizer = $this->getProductModelDraftNormalizerInstance();

        $draft = (new NewProductModelDraftBuilder())->build();

        $array = $productModelDraftNormalizer->normalize($draft);

        $this->assertEmpty($array['changes']);
        $this->assertArrayHasKey('label', $array);
        $this->assertArrayHasKey('values', $array);
        $this->assertIsArray($array['values']);

        $this->assertArrayHasKey('draftId', $array['values']);
        $this->assertArrayHasKey('code', $array['values']);
        $this->assertArrayHasKey('family', $array['values']);
        $this->assertArrayHasKey('family_variant', $array['values']);
        $this->assertArrayHasKey('parentId', $array['values']);
        $this->assertArrayHasKey('parent', $array['values']);
    }

    /**
     * @dataProvider dataNormalizeChanges
     */
    public function testNormalizeChanges(ProductModelDraftInterface $draft, array $changes): void
    {
        $this->attributeChangeServiceMock->method('get')->willReturn($changes);

        $productModelDraftNormalizer = $this->getProductModelDraftNormalizerInstance();

        $array = $productModelDraftNormalizer->normalize($draft);

        $this->assertNotEmpty($array['changes']);
        $this->assertCount(count($changes), $array['changes']);
    }

    public function dataNormalizeChanges(): array
    {
        return [
            [
                (new ExistingProductModelDraftBuilder())->build(),
                [
                    (new AttributeChangeBuilder())->build(),
                    (new AttributeChangeBuilder())->build(),
                ],
            ],
            [
                (new NewProductModelDraftBuilder())->build(),
                [
                    (new AttributeChangeBuilder())->build(),
                ],
            ],
        ];
    }

    public function testNormalizeWhenContextIsEmptyThenShouldNotReturnProduct(): void
    {
        $productModelDraftNormalizer = $this->getProductModelDraftNormalizerInstance();

        $draft = (new ExistingProductModelDraftBuilder())->build();

        $array = $productModelDraftNormalizer->normalize($draft);

        $this->assertArrayNotHasKey('product', $array);
    }

    public function testNormalizeWhenContextHasProductIncludedThenShouldReturnNormalizedProduct(): void
    {
        $formName = 'form-xxx';
        $this->formProviderMock->method('getForm')->willReturn($formName);

        $productModelDraftNormalizer = $this->getProductModelDraftNormalizerInstance();

        $draft = (new ExistingProductModelDraftBuilder())->build();

        $array = $productModelDraftNormalizer->normalize($draft, null, ['include_product' => true]);

        $this->assertArrayHasKey('product', $array);
        $this->assertSame($formName, $array['product']['meta']['form']);
    }

    public function testNormalizeWhenNoProductModelToCompare(): void
    {
        $this->creatorMock = $this->createMock(GeneralObjectFromDraftCreator::class);
        $this->creatorMock->method('getObjectToCompare')->willReturn(null);

        $productModelDraftNormalizer = $this->getProductModelDraftNormalizerInstance();

        $draft = (new ExistingProductModelDraftBuilder())->build();

        $array = $productModelDraftNormalizer->normalize($draft);

        $this->assertIsArray($array);
        $this->assertArrayNotHasKey('label', $array);
    }

    /**
     * @dataProvider dataSupportsNormalization
     */
    public function testSupportsNormalization(object $object, bool $expectedResult): void
    {
        $normalizer = $this->getProductModelDraftNormalizerInstance();
        $result = $normalizer->supportsNormalization($object);
        $this->assertSame($expectedResult, $result);
    }

    public function dataSupportsNormalization(): array
    {
        return [
            [(new ExistingProductModelDraftBuilder())->build(), true],
            [(new NewProductModelDraftBuilder())->build(), true],
            [(new UserBuilder())->build(), false],
        ];
    }

    private function getProductModelDraftNormalizerInstance(): ProductModelDraftNormalizer
    {
        return new ProductModelDraftNormalizer(
            $this->attributeChangeServiceMock,
            $this->attributeChangeNormalizer,
            $this->formProviderMock,
            $this->productModelNormalizerMock,
            $this->generalDraftNormalizerMock,
            $this->creatorMock,
            $this->valuesNormalizerMock,
            $this->permissionsHelperMock,
            $this->userContextMock
        );
    }
}
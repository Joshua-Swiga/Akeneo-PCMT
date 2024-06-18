<?php
/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Tests\Service\CopyProductsRule;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PcmtRulesBundle\Service\CopyProductsRule\SubEntityFinder;
use PcmtRulesBundle\Tests\TestDataBuilder\AttributeBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductBuilder;
use PcmtRulesBundle\Tests\TestDataBuilder\ProductModelBuilder;
use PHPUnit\Framework\TestCase;

class SubEntityFinderTest extends TestCase
{
    protected function setUp(): void
    {
    }

    public function dataFindByAxisAttribute(): array
    {
        $attribute1 = (new AttributeBuilder())->withCode('A1')->build();
        $attribute2 = (new AttributeBuilder())->withCode('A2')->build();

        $value1 = ScalarValue::value($attribute1->getCode(), 'xxx');
        $value2 = ScalarValue::value($attribute2->getCode(), 'xxx');

        $axisAttributes = new ArrayCollection();
        $axisAttributes->add($attribute1);

        $productVariantWithValue = (new ProductBuilder())
            ->addValue($value1)
            ->build();

        $productVariantWithDifferentValue = (new ProductBuilder())
            ->addValue($value2)
            ->build();

        $subProductModelWithValue = (new ProductModelBuilder())
            ->addValue($value1)
            ->build();

        $productModel2 = (new ProductModelBuilder())
            ->addProductVariant($productVariantWithValue)
            ->build();

        $productModel3 = (new ProductModelBuilder())
            ->addProductVariant($productVariantWithDifferentValue)
            ->build();

        $productModel1 = (new ProductModelBuilder())
            ->addSubProductModel($subProductModelWithValue)
            ->build();

        $sourceProductWithValue = (new ProductBuilder())
            ->addValue($value1)
            ->build();
        $sourceProductWithoutValue = (new ProductBuilder())
            ->addValue($value2)
            ->build();

        return [
            [$productModel1, $axisAttributes, $sourceProductWithValue, $subProductModelWithValue],
            [$productModel1, $axisAttributes, $sourceProductWithoutValue, null],
            [$productModel2, $axisAttributes, $sourceProductWithValue, $productVariantWithValue],
            [$productModel2, $axisAttributes, $sourceProductWithoutValue, null],
            [$productModel3, $axisAttributes, $sourceProductWithValue, null],
            [$productModel3, new ArrayCollection(), $sourceProductWithoutValue, $productVariantWithDifferentValue],
        ];
    }

    /**
     * @dataProvider dataFindByAxisAttribute
     */
    public function testFindByAxisAttributes(
        ProductModelInterface $productModel,
        Collection $axisAttributes,
        ProductInterface $sourceProduct,
        ?EntityWithValuesInterface $expectedResult
    ): void {
        $finder = $this->getFinderInstance();
        $result = $finder->findByAxisAttributes($productModel, $axisAttributes, $sourceProduct);
        $this->assertEquals($expectedResult, $result);
    }

    private function getFinderInstance(): SubEntityFinder
    {
        return new \PcmtRulesBundle\Service\CopyProductsRule\SubEntityFinder();
    }
}
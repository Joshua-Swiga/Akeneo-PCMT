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

namespace PcmtRulesBundle\Service\CopyProductsRule;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use PcmtRulesBundle\Service\AttributeMappingGenerator;

class CopyProductsRuleProcessor
{
    /** @var CopyProductToProductModel */
    private $copyProductToProductModel;

    /** @var AttributeMappingGenerator */
    private $attributeMappingGenerator;

    public function __construct(
        CopyProductToProductModel $copyProductToProductModel,
        AttributeMappingGenerator $attributeMappingGenerator
    ) {
        $this->copyProductToProductModel = $copyProductToProductModel;
        $this->attributeMappingGenerator = $attributeMappingGenerator;
    }

    public function process(
        StepExecution $stepExecution,
        FamilyInterface $destinationFamily,
        ProductInterface $sourceProduct
    ): void {
        $mappings = $this->attributeMappingGenerator->get(
            $sourceProduct->getFamily(),
            $destinationFamily,
            $stepExecution->getJobParameters()->get('attributeMapping')
        );

        $associations = $sourceProduct->getAssociations();
        foreach ($associations as $association) {
            $models = $association->getProductModels();
            foreach ($models as $model) {
                /** @var ProductModelInterface $model */
                $stepExecution->incrementSummaryInfo('associated_product_models_found', 1);
                if ($model->getFamily()->getCode() === $destinationFamily->getCode()) {
                    $stepExecution->incrementSummaryInfo('associated_product_models_found_in_correct_family', 1);
                    $this->copyProductToProductModel->process($stepExecution, $sourceProduct, $model, $mappings);
                }
            }
        }
    }
}

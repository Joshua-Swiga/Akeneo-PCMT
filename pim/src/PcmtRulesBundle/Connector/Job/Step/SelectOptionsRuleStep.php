<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtRulesBundle\Connector\Job\Step;

use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\AbstractStep;
use PcmtRulesBundle\Service\SelectOptionsCreator;
use PcmtRulesBundle\Service\SelectOptionsRemover;

class SelectOptionsRuleStep extends AbstractStep
{
    public const PARAM_ATTRIBUTE_CODE = 'attributeCode';

    public const PARAM_ATTRIBUTE_VALUE = 'attributeValue';

    public const PARAM_DESTINATION_ATTRIBUTE = 'destinationAttribute';

    public const PARAM_SOURCE_FAMILY = 'sourceFamily';

    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var SelectOptionsCreator */
    private $selectOptionsCreator;

    /** @var SelectOptionsRemover */
    private $selectOptionsRemover;

    public function setAttributeRepository(AttributeRepositoryInterface $attributeRepository): void
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function setSelectOptionsCreator(SelectOptionsCreator $selectOptionsCreator): void
    {
        $this->selectOptionsCreator = $selectOptionsCreator;
    }

    public function setSelectOptionsRemover(SelectOptionsRemover $selectOptionsRemover): void
    {
        $this->selectOptionsRemover = $selectOptionsRemover;
    }

    protected function doExecute(StepExecution $stepExecution): void
    {
        $jobParameters = $stepExecution->getJobParameters();

        $text = [];
        foreach ($jobParameters->all() as $key => $value) {
            $text[] = $key.' : '. $value;
        }
        $stepExecution->addSummaryInfo('parameters', implode(', ', $text));

        $destinationAttribute = $this->attributeRepository->findOneByIdentifier(
            $jobParameters->get(self::PARAM_DESTINATION_ATTRIBUTE)
        );
        $sourceFamilyCode = $jobParameters->get(self::PARAM_SOURCE_FAMILY);
        $attributeValue = $this->attributeRepository->findOneByIdentifier(
            $jobParameters->get(self::PARAM_ATTRIBUTE_VALUE)
        );

        // validating parameters

        if (!$destinationAttribute) {
            throw new \Exception(
                'Destination attribute not found for: '. $jobParameters->get(self::PARAM_DESTINATION_ATTRIBUTE)
            );
        }
        $types = [
            'pim_catalog_simpleselect',
            'pim_catalog_multiselect',
        ];
        if (!in_array($destinationAttribute->getType(), $types)) {
            throw new \Exception('Destination attribute has incorrect type.');
        }

        if (!$attributeValue) {
            throw new \Exception(
                'Attribute value not found for: '. $jobParameters->get(self::PARAM_ATTRIBUTE_VALUE)
            );
        }

        if (!$sourceFamilyCode) {
            throw new \Exception('Source family not chosen.');
        }

        $this->selectOptionsRemover->remove($stepExecution, $destinationAttribute);

        $this->selectOptionsCreator->create(
            $stepExecution,
            $sourceFamilyCode,
            $jobParameters->get(self::PARAM_ATTRIBUTE_CODE),
            $destinationAttribute,
            $attributeValue
        );
    }
}
<?php

declare(strict_types=1);

/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Service;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UpdateImageService
{
    /** @var string */
    private $destinationAttributeCode;

    /** @var ObjectUpdaterInterface */
    private $updater;

    /** @var SaverInterface */
    private $saver;

    /** @var ValidatorInterface */
    private $validator;

    public function __construct(
        ObjectUpdaterInterface $updater,
        SaverInterface $saver,
        ValidatorInterface $validator
    ) {
        $this->updater = $updater;
        $this->saver = $saver;
        $this->validator = $validator;
    }

    public function setDestinationAttributeCode(string $destinationAttributeCode): void
    {
        $this->destinationAttributeCode = $destinationAttributeCode;
    }

    public function processEntity(EntityWithValuesInterface $entity, FileInfoInterface $file): void
    {
        $values = [
            $this->destinationAttributeCode => [
                [
                    'data'   => $file->getKey(),
                    'locale' => null,
                    'scope'  => null,
                ],
            ],
        ];

        $this->updater->update($entity, ['values' => $values]);
        $violations = $this->validator->validate($entity);
        if (0 !== $violations->count()) {
            throw new \Exception('Constraints violations: '. $this->getTextFromViolations($violations));
        }
        $this->saver->save($entity);
    }

    private function getTextFromViolations(ConstraintViolationListInterface $violations): ?string
    {
        $txt = [];
        foreach ($violations as $violation) {
            /** @var ConstraintViolationInterface $violation */
            $txt[] = $violation->getMessage();
        }

        return implode(', ', $txt);
    }
}
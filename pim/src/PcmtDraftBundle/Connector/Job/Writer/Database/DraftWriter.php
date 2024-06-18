<?php

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\Writer\Database;

use Akeneo\Pim\Enrichment\Component\Product\Converter\ConverterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\VersioningBundle\Manager\VersionManager;
use Akeneo\Tool\Component\Batch\Item\FileInvalidItem;
use Akeneo\Tool\Component\Batch\Item\InitializableInterface;
use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionableInterface;
use Akeneo\UserManagement\Component\Model\UserInterface;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Saver\DraftSaver;
use PcmtDraftBundle\Service\Draft\BaseEntityCreatorInterface;
use PcmtDraftBundle\Service\Draft\DraftCreatorInterface;
use PcmtSharedBundle\Service\Checker\CategoryPermissionsCheckerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\ConstraintViolation;

class DraftWriter implements PcmtDraftWriterInterface, InitializableInterface, StepExecutionAwareInterface
{
    /** @var UserInterface */
    protected $user;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var VersionManager */
    protected $versionManager;

    /** @var SaverInterface */
    protected $entitySaver;

    /** @var NormalizerInterface */
    protected $standardNormalizer;

    /** @var SaverInterface */
    protected $draftSaver;

    /** @var BaseEntityCreatorInterface */
    protected $baseEntityCreator;

    /** @var DraftCreatorInterface */
    protected $draftCreator;

    /** @var CategoryPermissionsCheckerInterface */
    private $accessChecker;

    /** @var ConverterInterface */
    private $valueConverter;

    public function __construct(
        VersionManager $versionManager,
        SaverInterface $entitySaver,
        NormalizerInterface $standardNormalizer,
        SaverInterface $draftSaver,
        BaseEntityCreatorInterface $baseEntityCreator,
        DraftCreatorInterface $draftCreator,
        CategoryPermissionsCheckerInterface $accessChecker,
        ConverterInterface $valueConverter
    ) {
        $this->versionManager = $versionManager;
        $this->entitySaver = $entitySaver;
        $this->standardNormalizer = $standardNormalizer;
        $this->draftSaver = $draftSaver;
        $this->baseEntityCreator = $baseEntityCreator;
        $this->draftCreator = $draftCreator;
        $this->accessChecker = $accessChecker;
        $this->valueConverter = $valueConverter;
    }

    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }

    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    public function initialize(): void
    {
        $jobParameters = $this->stepExecution->getJobParameters();
        $realTimeVersioning = $jobParameters->get('realTimeVersioning');
        $this->versionManager->setRealTimeVersioning($realTimeVersioning);
    }

    /**
     * {@inheritdoc}
     */
    public function write(array $items): void
    {
        foreach ($items as $entity) {
            try {
                // following is needed, as normalizer will throw an exception otherwise
                $entity->setCreated(new \DateTime());
                $entity->setUpdated(new \DateTime());
                $data = $this->standardNormalizer->normalize($entity, 'standard', ['import_via_drafts']);
                $data['values'] = $this->valueConverter->convert($data['values']);

                if (!$this->accessChecker->hasAccessToProduct(CategoryPermissionsCheckerInterface::EDIT_LEVEL, $entity, $this->user)) {
                    throw $this->skipItemAndReturnException($data, 'No edit access to entity category');
                }

                $baseProductModel = $this->getEntityOrCreateIfNotExists($entity);
                try {
                    $draft = $this->draftCreator->create($baseProductModel, $data, $this->user);
                    $this->draftSaver->save($draft, [DraftSaver::OPTION_NO_VALIDATION => true]);
                } catch (DraftViolationException $exception) {
                    $message = $exception->getMessage();
                    foreach ($exception->getViolations() as $violation) {
                        /** @var ConstraintViolation $violation */
                        $message .= "\n" . $violation->getMessage();
                    }
                    throw $this->skipItemAndReturnException($data, $message, $exception);
                } catch (\InvalidArgumentException $exception) {
                    throw $this->skipItemAndReturnException($data, $exception->getMessage(), $exception);
                }
                $this->incrementCount($entity);
            } catch (InvalidItemException $exception) {
                $this->stepExecution->addWarning(
                    $exception->getMessage(),
                    $exception->getMessageParameters(),
                    $exception->getItem()
                );
            }
        }
    }

    /**
     * @param ProductInterface|ProductModelInterface $item
     *
     * @return ProductInterface|ProductModelInterface
     */
    protected function getEntityOrCreateIfNotExists($item)
    {
        if ($item->getId()) {
            return $item;
        }

        $baseEntity = $this->baseEntityCreator->create($item);

        $this->entitySaver->save($baseEntity);

        return $baseEntity;
    }

    protected function incrementCount(VersionableInterface $entity): void
    {
        $action = $entity->getId() ? 'process' : 'create';
        $this->stepExecution->incrementSummaryInfo($action);
    }

    protected function skipItemAndReturnException(
        array $item,
        string $message,
        ?\Throwable $previousException = null
    ): InvalidItemException {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('skip');
        }
        $itemPosition = null !== $this->stepExecution ? $this->stepExecution->getSummaryInfo('item_position') : 0;
        $invalidItem = new FileInvalidItem($item, $itemPosition);

        return new InvalidItemException($message, $invalidItem, [], 0, $previousException);
    }
}
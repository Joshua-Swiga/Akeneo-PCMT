<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Saver;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\ORM\EntityManagerInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\ExistingObjectDraftInterface;
use PcmtDraftBundle\Exception\DraftSavingFailedException;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\Exception\DraftWithNoChangesException;
use PcmtDraftBundle\Service\Draft\ChangesChecker;
use PcmtDraftBundle\Service\Draft\DraftExistenceChecker;
use PcmtDraftBundle\Service\Draft\GeneralObjectFromDraftCreator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DraftSaver implements SaverInterface
{
    public const OPTION_NO_VALIDATION = 'no_validation';
    public const OPTION_LAST_UPDATED_AT = 'lastUpdatedAt';
    public const OPTION_DONT_SAVE_IF_NO_CHANGES = 'OPTION_DONT_SAVE_IF_NO_CHANGES';

    /** @var EntityManagerInterface */
    protected $entityManager;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var DraftExistenceChecker */
    private $draftExistenceChecker;

    /** @var ValidatorInterface */
    private $productValidator;

    /** @var ValidatorInterface */
    private $productModelValidator;

    /** @var GeneralObjectFromDraftCreator */
    private $generalObjectFromDraftCreator;

    /** @var ChangesChecker */
    private $changesChecker;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $eventDispatcher,
        DraftExistenceChecker $draftExistenceChecker,
        ValidatorInterface $productValidator,
        ValidatorInterface $productModelValidator,
        GeneralObjectFromDraftCreator $generalObjectFromDraftCreator,
        ChangesChecker $changesChecker
    ) {
        $this->entityManager = $entityManager;
        $this->eventDispatcher = $eventDispatcher;
        $this->draftExistenceChecker = $draftExistenceChecker;
        $this->productValidator = $productValidator;
        $this->productModelValidator = $productModelValidator;
        $this->generalObjectFromDraftCreator = $generalObjectFromDraftCreator;
        $this->changesChecker = $changesChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function save($draft, array $options = []): void
    {
        $this->validateDraft($draft, $options);
        if (!$draft->getId() && !empty($options[self::OPTION_DONT_SAVE_IF_NO_CHANGES])) {
            if (!$this->changesChecker->checkIfChanges($draft)) {
                throw new DraftWithNoChangesException('Draft does not contain any changes.');
            }
        }
        $this->eventDispatcher->dispatch(StorageEvents::PRE_SAVE, new GenericEvent($draft, $options));
        $this->entityManager->persist($draft);
        $this->entityManager->flush();
        $this->eventDispatcher->dispatch(StorageEvents::POST_SAVE, new GenericEvent($draft, $options));
    }

    protected function validateDraft(object $draft, array $options = []): void
    {
        $this->checkIfDraftIsInstanceOfDraftInterface($draft);
        $this->checkIfThereIsOtherDraftForThisObject($draft);
        $this->checkIfDraftCouldBeSaved($draft);
        if (empty($options[self::OPTION_NO_VALIDATION])) {
            if (isset($options[self::OPTION_LAST_UPDATED_AT])) {
                $this->checkIfDraftHasNotBeenEditedWhileCurrentSessionActive($draft, $options[self::OPTION_LAST_UPDATED_AT]);
            }
            $this->validateObjectToSave($draft);
        }
    }

    private function checkIfDraftIsInstanceOfDraftInterface(object $draft): void
    {
        if (!$draft instanceof DraftInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a %s, "%s" provided',
                    DraftInterface::class,
                    get_class($draft)
                )
            );
        }
    }

    private function checkIfDraftCouldBeSaved(DraftInterface $draft): void
    {
        if (AbstractDraft::STATUS_APPROVED === $draft->getStatus()) {
            throw DraftSavingFailedException::draftAlreadyApproved();
        }

        if (AbstractDraft::STATUS_REJECTED === $draft->getStatus()) {
            throw DraftSavingFailedException::draftAlreadyRejected();
        }
    }

    private function validateObjectToSave(DraftInterface $draft): void
    {
        $objectToSave = $this->generalObjectFromDraftCreator->getObjectToSave($draft);
        if (!$objectToSave) {
            throw DraftSavingFailedException::noCorrespondingObject();
        }

        if ($objectToSave instanceof ProductInterface) {
            $violations = $this->productValidator->validate($objectToSave, null, [
                'Default',
                'creation',
            ]);
        } elseif ($objectToSave instanceof ProductModelInterface) {
            $violations = $this->productModelValidator->validate($objectToSave, null, [
                'Default',
                'creation',
            ]);
        }

        if (0 !== $violations->count()) {
            throw new DraftViolationException($violations, $objectToSave);
        }
    }

    private function checkIfThereIsOtherDraftForThisObject(DraftInterface $draft): void
    {
        if (!$draft->getId() && $draft instanceof ExistingObjectDraftInterface) {
            if ($this->draftExistenceChecker->checkIfDraftForObjectAlreadyExists($draft)) {
                throw new \InvalidArgumentException(
                    'There is already a draft for this object'
                );
            }
        }
    }

    private function checkIfDraftHasNotBeenEditedWhileCurrentSessionActive(DraftInterface $draft, int $lastEditedDateTimestamp): void
    {
        if (!$draft->getUpdatedAt()) {
            return;
        }

        if ($draft->getUpdatedAt()->getTimestamp() !== $lastEditedDateTimestamp) {
            throw DraftSavingFailedException::draftHasBeenEditedInTheMeantime();
        }
    }
}
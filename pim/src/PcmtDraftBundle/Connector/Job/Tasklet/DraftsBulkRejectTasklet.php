<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\Tasklet;

use Akeneo\Tool\Component\Batch\Item\InvalidItemException;
use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Connector\Step\TaskletInterface;
use PcmtDraftBundle\Connector\Job\InvalidItems\DraftInvalidItem;
use PcmtDraftBundle\Connector\Job\Provider\DraftsProvider;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Exception\DraftViolationException;
use PcmtDraftBundle\MassActions\DraftsBulkActionOperation;
use PcmtDraftBundle\Service\Draft\DraftFacade;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DraftsBulkRejectTasklet implements TaskletInterface
{
    /** @var DraftFacade */
    private $draftFacade;

    /** @var StepExecution */
    protected $stepExecution;

    /** @var NormalizerInterface */
    protected $constraintViolationNormalizer;

    /** @var DraftsProvider */
    protected $draftsProvider;

    public function __construct(
        DraftFacade $draftFacade,
        NormalizerInterface $constraintViolationNormalizer,
        DraftsProvider $draftIdsPreparator
    ) {
        $this->draftFacade = $draftFacade;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->draftsProvider = $draftIdsPreparator;
    }

    /**
     * {@inheritdoc}
     */
    public function setStepExecution(StepExecution $stepExecution): void
    {
        $this->stepExecution = $stepExecution;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(): void
    {
        if (null === $this->stepExecution) {
            throw new \InvalidArgumentException(
                sprintf('In order to execute "%s" you need to set a step execution.', static::class)
            );
        }
        $jobInstance = $this->stepExecution->getJobParameters();

        $draftsToReject = $this->draftsProvider->prepare(
            (bool) ($jobInstance->get(DraftsBulkActionOperation::KEY_ALL_SELECTED)),
            $jobInstance->get(DraftsBulkActionOperation::KEY_EXCLUDED),
            $jobInstance->get(DraftsBulkActionOperation::KEY_SELECTED)
        );

        foreach ($draftsToReject as $draft) {
            $normalizedViolations = [];
            try {
                try {
                    /** @var DraftInterface $draft */
                    $this->draftFacade->rejectDraft($draft);
                    $this->stepExecution->incrementSummaryInfo('rejected');
                } catch (DraftViolationException $e) {
                    $context = $e->getContextForNormalizer();
                    foreach ($e->getViolations() as $violation) {
                        $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                            $violation,
                            'internal_api',
                            $context
                        );
                    }
                    throw $this->skipItemAndReturnException($normalizedViolations, $draft->getId());
                }
            } catch (InvalidItemException $exception) {
                $this->stepExecution->addWarning(
                    $exception->getMessage(),
                    $exception->getMessageParameters(),
                    $exception->getItem()
                );
            }
        }
    }

    private function skipItemAndReturnException(array $violations, int $draftId, ?\Throwable $previousException = null): InvalidItemException
    {
        if ($this->stepExecution) {
            $this->stepExecution->incrementSummaryInfo('failed');
        }
        $invalidItem = new DraftInvalidItem($draftId, $violations);
        $message = 'Cannot reject draft ' . $invalidItem->getDraftId();

        return new InvalidItemException($message, $invalidItem, [], 0, $previousException);
    }
}
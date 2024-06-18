<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Connector\Job\Step;

use Akeneo\Tool\Component\Batch\Model\StepExecution;
use Akeneo\Tool\Component\Batch\Step\ItemStep;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\UserRepository;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Akeneo\UserManagement\Component\Repository\UserRepositoryInterface;
use PcmtDraftBundle\Exception\UserNotFoundException;

class PcmtImportViaDraftStep extends ItemStep
{
    /** @var UserRepository */
    private $userRepository;

    /** @var UserInterface */
    private $user;

    public function setUserRepository(UserRepositoryInterface $userRepository): void
    {
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function doExecute(StepExecution $stepExecution): void
    {
        $jobExecution = $stepExecution->getJobExecution();
        $this->user = $this->userRepository->findOneByIdentifier($jobExecution->getUser());
        parent::doExecute($stepExecution);
    }

    /**
     * {@inheritdoc}
     *
     * @throws UserNotFoundException
     */
    protected function write($processedItems)
    {
        $user = $this->userRepository->find($this->user->getId());
        if (!$user) {
            throw new UserNotFoundException('No user found');
        }
        $this->writer->setUser($user);

        return parent::write($processedItems);
    }
}
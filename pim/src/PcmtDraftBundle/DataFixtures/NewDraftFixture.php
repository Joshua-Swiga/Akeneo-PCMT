<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\DataFixtures;

use Akeneo\UserManagement\Component\Model\User;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use PcmtDraftBundle\Entity\NewProductDraft;

class NewDraftFixture implements FixtureInterface
{
    /** @var string */
    private $draftIdentifier;

    public function load(
        ObjectManager $manager
    ): void {
        $userRepository = $manager->getRepository(User::class);

        $user = $userRepository->findOneBy(
            [
                'id' => 1,
            ]
        );

        $draft = new NewProductDraft(
            [
                'identifier' => $this->generateTestIdentifier(),
                'family'     => 'MD_HUB',
            ],
            new \DateTime(),
            $user
        );

        $manager->persist($draft);
        $manager->flush();
    }

    public function getDraftIdentifier(): string
    {
        return $this->draftIdentifier;
    }

    private function generateTestIdentifier(): string
    {
        $draftIdenfier = 'behat_unique_id_' . microtime();
        $this->draftIdentifier = $draftIdenfier;

        return $draftIdenfier;
    }
}
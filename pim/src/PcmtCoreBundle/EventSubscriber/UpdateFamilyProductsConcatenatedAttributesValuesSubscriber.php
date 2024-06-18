<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Query\FindAttributesForFamily;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Akeneo\Tool\Bundle\BatchBundle\Job\JobInstanceRepository;
use Akeneo\Tool\Bundle\BatchBundle\Launcher\JobLauncherInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use PcmtCoreBundle\Extension\ConcatenatedAttribute\Structure\Component\AttributeType\PcmtAtributeTypes;
use PcmtCoreBundle\Query\FindConcatenatedAttributesForFamily;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class UpdateFamilyProductsConcatenatedAttributesValuesSubscriber implements EventSubscriberInterface
{
    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var JobInstanceRepository */
    private $jobInstanceRepository;

    /** @var JobLauncherInterface */
    private $jobLauncher;

    /** @var string */
    private $jobName;

    /** @var FindConcatenatedAttributesForFamily */
    private $findConcatenatedAttributesForFamily;

    /** @var bool */
    private $runningUpdateJobRequired = false;

    /** @var mixed[] */
    private $concatenatedAttributesToUpdate = [];

    public function __construct(
        TokenStorageInterface $tokenStorage,
        JobInstanceRepository $jobInstanceRepository,
        JobLauncherInterface $jobLauncher,
        string $jobName,
        FindAttributesForFamily $findAttributesForFamily
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->jobInstanceRepository = $jobInstanceRepository;
        $this->jobLauncher = $jobLauncher;
        $this->jobName = $jobName;
        $this->findConcatenatedAttributesForFamily = $findAttributesForFamily;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::PRE_SAVE  => 'checkIfUpdateNeedsToRunBackgroundJob',
            StorageEvents::POST_SAVE => 'updateConcatenatedAttributesValuesForFamilyProducts',
        ];
    }

    public function checkIfUpdateNeedsToRunBackgroundJob(GenericEvent $event): void
    {
        $subject = $event->getSubject();

        if (!$subject instanceof FamilyInterface) {
            return;
        }

        if (count($attributesToUpdate = $this->getFamilyConcatenatedAttributesForProductUpdate($subject)) > 0) {
            $this->concatenatedAttributesToUpdate = $attributesToUpdate;
            $this->runningUpdateJobRequired = true;
        }
    }

    public function updateConcatenatedAttributesValuesForFamilyProducts(GenericEvent $event): void
    {
        $subject = $event->getSubject();

        if (!$subject instanceof FamilyInterface) {
            return;
        }

        if ($this->runningUpdateJobRequired) {
            $token = $this->tokenStorage->getToken();
            $jobInstance = $this->jobInstanceRepository->findOneByIdentifier($this->jobName);
            $this->jobLauncher->launch(
                $jobInstance,
                $token->getUser(),
                [
                    'concatenatedAttributesToUpdate' => $this->concatenatedAttributesToUpdate,
                    'family_code'                    => $subject->getCode(),
                ]
            );
        }
    }

    private function getFamilyConcatenatedAttributesForProductUpdate(FamilyInterface $family): array
    {
        $persistedConcatenatedAttributesList = $this->findConcatenatedAttributesForFamily->execute($family);
        $familyConcatenatedAttributes = $family->getAttributes();
        $attributesList = [];

        foreach ($familyConcatenatedAttributes as $attribute) {
            if (PcmtAtributeTypes::CONCATENATED_FIELDS === $attribute->getType()) {
                $attributesList[] = $attribute->getCode();
            }
        }

        return array_values(array_diff($attributesList, $persistedConcatenatedAttributesList));
    }
}
<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Updater;

use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\ORM\EntityManagerInterface;
use PcmtCISBundle\Entity\Subscription;
use PcmtCoreBundle\Entity\ReferenceData\CountryCode;
use PcmtCoreBundle\Repository\GS1CodesRepository;

class SubscriptionUpdater implements ObjectUpdaterInterface
{
    /** @var GS1CodesRepository */
    private $referenceDataRepository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->referenceDataRepository = $entityManager->getRepository(CountryCode::class);
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *     'data_recipients_g_l_n' => 'string',
     *     'data_sources_g_l_n' => 'string',
     *     'gtin' => 'string',
     *     'gpc_category_code' => 'string'
     *     'target_market_country_code' => 'string'
     * ]
     */
    public function update($subscription, array $data, array $options = [])
    {
        if (!$subscription instanceof Subscription) {
            throw InvalidObjectException::objectExpected(
                get_class($subscription),
                Attribute::class
            );
        }

        foreach ($data as $field => $item) {
            $this->setData($subscription, $field, $item);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function setData(Subscription $subscription, $field, $data): void
    {
        switch ($field) {
            case 'data_sources_g_l_n':
                $subscription->setDataSourcesGLN($data);
                break;
            case 'data_recipients_g_l_n':
                $subscription->setDataRecipientsGLN($data);
                break;
            case 'g_t_i_n':
                $subscription->setGTIN($data);
                break;
            case 'g_p_c_category_code':
                $subscription->setGPCCategoryCode($data);
                break;
            case 'target_market_country_code':
                $this->setTargetMarketCountryCode($subscription, $data);
                break;
        }
    }

    protected function setTargetMarketCountryCode(Subscription $subscription, string $identifier): void
    {
        $referenceData = $this->referenceDataRepository->findOneByIdentifier($identifier);

        if (null === $referenceData) {
            throw InvalidPropertyException::validEntityCodeExpected(
                'target_market_country_code',
                'target market country code',
                'Referenced data for the code does not exist',
                static::class,
                $identifier
            );
        }

        $subscription->setTargetMarketCountryCode($referenceData);
    }
}
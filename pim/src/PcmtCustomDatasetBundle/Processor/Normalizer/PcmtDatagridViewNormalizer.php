<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCustomDatasetBundle\Processor\Normalizer;

use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Structured normalizer for DatagridView
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PcmtDatagridViewNormalizer implements NormalizerInterface
{
    /** @var string[] */
    protected $supportedFormat = ['internal_api'];

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = []): array
    {
        return [
            'owner'          => (string) $object->getOwner()->getUsername(),
            'label'          => (string) $object->getLabel(),
            'type'           => (string) $object->getType(),
            'datagrid_alias' => (string) $object->getDatagridAlias(),
            'columns'        => (string) $object->getOrder(),
            'filters'        => (string) $object->getFilters(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof DatagridView && in_array($format, $this->supportedFormat);
    }
}

<?php

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\ProductViolationNormalizer as OriginalProductViolationNormalizer;

class ProductViolationNormalizer extends OriginalProductViolationNormalizer
{
    /**
     * {@inheritdoc}
     */
    public function normalize($violation, $format = null, array $context = []): array
    {
        $propertyPath = $violation->getPropertyPath();

        if ('family' === $propertyPath) {
            return [
                'attribute' => $propertyPath,
                'global'    => false,
                'message'   => $violation->getMessage(),
            ];
        }

        return parent::normalize($violation, $format, $context);
    }
}

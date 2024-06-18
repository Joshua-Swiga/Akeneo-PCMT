<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Exception;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class DraftViolationException extends UnprocessableEntityHttpException
{
    /** @var ConstraintViolationListInterface */
    protected $violations;

    /** @var ProductInterface */
    private $product;

    /** @var ProductModelInterface */
    private $productModel;

    public function __construct(
        ConstraintViolationListInterface $violations,
        ?object $object,
        string $message = 'Validation failed.',
        ?\Throwable $previous = null,
        int $code = 0
    ) {
        parent::__construct($message, $previous, $code);

        $this->violations = $violations;
        if ($object instanceof ProductInterface) {
            $this->product = $object;
        }
        if ($object instanceof ProductModelInterface) {
            $this->productModel = $object;
        }
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    public function getProduct(): ?ProductInterface
    {
        return $this->product;
    }

    public function getProductModel(): ?ProductModelInterface
    {
        return $this->productModel;
    }

    public function getContextForNormalizer(): array
    {
        $context = [];
        if ($this->getProduct()) {
            $context['product'] = $this->getProduct();
        }
        if ($this->getProductModel()) {
            $context['productModel'] = $this->getProductModel();
        }

        return $context;
    }
}
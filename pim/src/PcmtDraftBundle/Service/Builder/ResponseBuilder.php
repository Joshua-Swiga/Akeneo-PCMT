<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Service\Builder;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ResponseBuilder
{
    public const FIRST_PAGE = 1;
    public const PER_PAGE = 25;

    /** @var bool */
    protected $isPaginated = false;

    /** @var int */
    protected $total = 0;

    /** @var int */
    protected $page = 1;

    /** @var mixed */
    protected $data = null;

    /** @var mixed[] */
    protected $context = [];

    /** @var ?string */
    protected $format = null;

    /** @var NormalizerInterface */
    protected $serializer;

    public function __construct(NormalizerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param int|float|bool|array|object|string $data
     *
     * @return $this
     */
    public function setData($data): self
    {
        $this->data = $data;

        return $this;
    }

    public function setPagination(int $total, ?int $page): self
    {
        $this->isPaginated = true;
        $this->total = $total;
        $this->page = $page;

        return $this;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function setFormat(?string $format): self
    {
        $this->format = $format;

        return $this;
    }

    public function build(): JsonResponse
    {
        if ($this->isPaginated) {
            $response = [
                'objects' => $this->data,
                'params'  => [
                    'total'       => $this->total,
                    'firstPage'   => self::FIRST_PAGE,
                    'currentPage' => $this->page,
                    'lastPage'    => $this->getLastPage($this->total),
                    'pageSize'    => self::PER_PAGE,
                ],
            ];
        } else {
            $response = $this->data;
        }

        return new JsonResponse($this->serializer->normalize($response, $this->format, $this->context));
    }

    public function getLastPage(int $total): int
    {
        $lastPage = (int) ceil($total / self::PER_PAGE);

        return $lastPage > 0 ? $lastPage : 1;
    }

    public function buildPaginatedResponse(array $result, int $total, ?int $page): JsonResponse
    {
        return $this
            ->setData($result)
            ->setPagination($total, $page)
            ->build();
    }
}
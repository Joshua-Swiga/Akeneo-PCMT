<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Widget;

use Akeneo\Platform\Bundle\DashboardBundle\Widget\WidgetInterface;
use PcmtDraftBundle\Entity\AbstractDraft;
use PcmtDraftBundle\Repository\DraftRepository;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DraftsWidget implements WidgetInterface
{
    /** @var DraftRepository */
    protected $draftRepository;

    /** @var NormalizerInterface */
    private $serializer;

    public function __construct(
        DraftRepository $draftRepository,
        NormalizerInterface $serializer
    ) {
        $this->draftRepository = $draftRepository;
        $this->serializer = $serializer;
    }

    public function getAlias(): string
    {
        return 'draft_products_overview';
    }

    public function getTemplate(): string
    {
        return 'PcmtDraftBundle:Widget:draft_products_overview.html.twig';
    }

    public function getParameters(): array
    {
        return [];
    }

    public function getData(): array
    {
        $criteria = [
            'status' => AbstractDraft::STATUS_NEW,
        ];
        $drafts = $this->draftRepository->findBy($criteria, null, 20, 0);

        return $this->serializer->normalize($drafts);
    }
}
<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Normalizer;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Akeneo\UserManagement\Bundle\Context\UserContext;
use Akeneo\UserManagement\Component\Model\User;
use PcmtDraftBundle\Entity\DraftInterface;
use PcmtDraftBundle\Entity\DraftStatus;
use PcmtDraftBundle\Entity\ExistingProductDraft;
use PcmtDraftBundle\Entity\ExistingProductModelDraft;
use PcmtDraftBundle\Entity\NewProductDraft;
use PcmtDraftBundle\Entity\NewProductModelDraft;
use Symfony\Component\Translation\TranslatorInterface;

class GeneralDraftNormalizer
{
    /** @var DraftStatusNormalizer */
    private $statusNormalizer;

    /** @var PresenterInterface */
    private $datetimePresenter;

    /** @var UserContext */
    private $userContext;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        DraftStatusNormalizer $statusNormalizer,
        PresenterInterface $datetimePresenter,
        UserContext $userContext,
        TranslatorInterface $translator
    ) {
        $this->statusNormalizer = $statusNormalizer;
        $this->datetimePresenter = $datetimePresenter;
        $this->userContext = $userContext;
        $this->translator = $translator;
    }

    public function normalize(DraftInterface $draft, ?string $format = null, array $context = []): array
    {
        if ($this->translator) {
            $context = array_merge($context, ['locale' => $this->translator->getLocale()]);
        }

        try {
            if ($this->userContext) {
                $timezone = $this->userContext->getUserTimezone();
                $datetimeContext = array_merge($context, ['timezone' => $timezone]);
            } else {
                $datetimeContext = $context;
            }
        } catch (\RuntimeException $exception) {
            $datetimeContext = $context;
        }

        $data = [];
        $data['id'] = $draft->getId();

        $data['createdAt'] = $this->datetimePresenter->present($draft->getCreatedAt(), $datetimeContext);
        $data['updatedAt'] = $this->datetimePresenter->present($draft->getUpdatedAt(), $datetimeContext);
        $data['lastUpdatedAtTimestamp'] = $draft->getUpdatedAt() ? $draft->getUpdatedAt()->getTimestamp() : 0;
        $author = $draft->getAuthor();
        $data['author'] = $author ? $author->getFirstName() . ' ' . $author->getLastName() : User::SYSTEM_USER_NAME;
        $draftStatus = new DraftStatus($draft->getStatus());
        $data['status'] = $this->statusNormalizer->normalize($draftStatus);
        $data['type'] = ucfirst($draft->getType());
        $data['typeName'] = $this->getTypeName($draft->getType());
        $data['meta'] = [
            'id'                => $draft->getId(),
            'model_type'        => 'draft',
            'structure_version' => null,
        ];

        return $data;
    }

    private function getTypeName(string $type): string
    {
        switch ($type) {
            case NewProductDraft::TYPE:
                return 'pcmt.entity.draft.type.new_product_draft';
            case ExistingProductDraft::TYPE:
                return 'pcmt.entity.draft.type.existing_product_draft';
            case NewProductModelDraft::TYPE:
                return 'pcmt.entity.draft.type.new_product_model_draft';
            case ExistingProductModelDraft::TYPE:
                return 'pcmt.entity.draft.type.existing_product_model_draft';
        }
        throw new \InvalidArgumentException('Unknown draft type: '. $type);
    }
}
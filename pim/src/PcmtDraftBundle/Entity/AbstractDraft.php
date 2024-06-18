<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtDraftBundle\Entity;

use Akeneo\UserManagement\Component\Model\UserInterface;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use PcmtDraftBundle\Exception\DraftApproveFailedException;
use PcmtDraftBundle\Exception\DraftRejectFailedException;

abstract class AbstractDraft implements DraftInterface
{
    public const STATUS_APPROVED = 2;
    public const STATUS_NEW = 1;
    public const STATUS_REJECTED = 4;

    public const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_APPROVED,
        self::STATUS_REJECTED,
    ];

    // keep product or product-model related data here.
    // like family, groups etc. all the fields. - it helps rebuild product from new draft.
    /** @var mixed[] */
    protected $productData = [];

    /** @var Collection */
    protected $categories;

    /** @var int */
    protected $id = 0;

    /** @var DateTime */
    protected $created;

    /** @var DateTime */
    protected $updated;

    /** @var ?DateTime */
    protected $approved;

    /** @var int */
    protected $version;

    /** @var int */
    protected $status;

    /** @var UserInterface */
    protected $author;

    /** @var UserInterface */
    protected $updatedBy;

    /** @var UserInterface */
    protected $approvedBy;

    /**
     * Has to be on this level, becase we keep product and product_model drafts in one table and ORM requires it.
     *
     * @var ?ProductModelInterface
     */
    protected $productModel;

    /**
     * Has to be on this level, becase we keep product and product_model drafts in one table and ORM requires it.
     *
     * @var ?ProductInterface
     */
    protected $product;

    public function getCategories(): Collection
    {
        return $this->categories;
    }

    public function setCategories(Collection $categories): void
    {
        $this->categories = new ArrayCollection();
        foreach ($categories as $category) {
            $this->categories->add($category);
        }
    }

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->created;
    }

    public function getUpdatedAt(): ?DateTime
    {
        return $this->updated;
    }

    public function getAuthor(): ?UserInterface
    {
        return $this->author;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $statusId): void
    {
        $this->status = $statusId;
    }

    public function setApproved(DateTime $approved): void
    {
        $this->approved = $approved;
    }

    public function getApproved(): ?DateTime
    {
        return $this->approved;
    }

    public function setApprovedBy(UserInterface $approvedBy): void
    {
        $this->approvedBy = $approvedBy;
    }

    public function getApprovedBy(): ?UserInterface
    {
        return $this->approvedBy;
    }

    public function getType(): string
    {
        return static::TYPE;
    }

    public function getProductData(): ?array
    {
        return $this->productData;
    }

    public function setProductData(array $productData): void
    {
        $this->productData = $productData;
    }

    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    public function setUpdated(DateTime $updated): void
    {
        $this->updated = $updated;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function updateTimestamps(): void
    {
        $this->setUpdated(new DateTime('now'));
        if (null === $this->getCreated()) {
            $this->setCreated(new DateTime('now'));
        }
    }

    public function approve(UserInterface $approver): void
    {
        if (self::STATUS_NEW !== $this->getStatus()) {
            throw DraftApproveFailedException::createWithDefaultMessage();
        }

        $this->setStatus(self::STATUS_APPROVED);
        $this->setApproved(Carbon::now());
        $this->setApprovedBy($approver);
    }

    public function reject(): void
    {
        if (self::STATUS_NEW !== $this->getStatus()) {
            throw DraftRejectFailedException::createWithDefaultMessage();
        }

        $this->status = self::STATUS_REJECTED;
    }
}
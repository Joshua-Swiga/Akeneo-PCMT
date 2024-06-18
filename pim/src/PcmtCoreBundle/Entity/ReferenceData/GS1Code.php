<?php
/**
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtCoreBundle\Entity\ReferenceData;

use Pim\Bundle\CustomEntityBundle\Entity\AbstractCustomEntity;

abstract class GS1Code extends AbstractCustomEntity
{
    /** @var string */
    protected $listName;

    /** @var string */
    protected $name;

    /** @var string */
    protected $definition;

    /** @var int */
    protected $version;

    /** @var \DateTime */
    protected $changeDate;

    /** @var int */
    protected $status = 1;

    public function __construct()
    {
        $this->setListName(static::getClass());
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getListName(): string
    {
        return $this->listName;
    }

    protected function setListName(string $listName): void
    {
        $this->listName = $listName;
    }

    /**
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $codeName
     */
    public function setName(?string $codeName): void
    {
        $this->name = $codeName;
    }

    public function getDefinition(): string
    {
        return $this->definition;
    }

    /**
     * @param string $definition
     */
    public function setDefinition(?string $definition): void
    {
        $this->definition = $definition;
    }

    public function setChangeDate(\DateTime $changeDate): void
    {
        $this->changeDate = $changeDate;
    }

    public function getChangeDate(): \DateTime
    {
        return $this->changeDate;
    }

    public function getVersion(): int
    {
        return $this->version;
    }

    public function setVersion(int $version): void
    {
        $this->version = $version;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    public function getType(): string
    {
        return 'gs1_code';
    }

    public function getCustomEntityName(): string
    {
        return static::getClass();
    }

    /**
     * {@inheritdoc}
     */
    public static function getLabelProperty(): string
    {
        return 'name';
    }

    abstract public function getReferenceDataEntityType(): string;

    abstract protected static function getClass(): string;
}
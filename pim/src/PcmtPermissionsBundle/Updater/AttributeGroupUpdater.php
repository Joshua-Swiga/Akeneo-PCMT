<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace PcmtPermissionsBundle\Updater;

use Akeneo\Pim\Structure\Component\Updater\AttributeGroupUpdater as AkeneoAttributeGroupUpdater;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyTypeException;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Repository\GroupRepositoryInterface;
use PcmtPermissionsBundle\Entity\AttributeGroupAccess;
use PcmtPermissionsBundle\Entity\AttributeGroupWithAccess;

class AttributeGroupUpdater extends AkeneoAttributeGroupUpdater
{
    /** @var GroupRepositoryInterface */
    private $userGroupRepository;

    public function setUserGroupRepository(GroupRepositoryInterface $userGroupRepository): void
    {
        $this->userGroupRepository = $userGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function validateDataType($field, $data): void
    {
        if (in_array(
            $field,
            [
                'permission[allowed_to_edit]',
                'permission[allowed_to_own]',
                'permission[allowed_to_view]',
            ]
        )) {
            if (null !== $data && !is_string($data)) {
                throw InvalidPropertyTypeException::stringExpected($field, static::class, $data);
            }
        } else {
            parent::validateDataType($field, $data);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function setData($attributeGroup, $field, $data): void
    {
        if ('permission[allowed_to_edit]' === $field) {
            $this->setAccesses(
                $attributeGroup,
                explode(',', $data),
                AttributeGroupAccess::EDIT_LEVEL
            );
        } elseif ('permission[allowed_to_view]' === $field) {
            $this->setAccesses(
                $attributeGroup,
                explode(',', $data),
                AttributeGroupAccess::VIEW_LEVEL
            );
        } elseif ('permission[allowed_to_own]' === $field) {
            $this->setAccesses(
                $attributeGroup,
                explode(',', $data),
                AttributeGroupAccess::OWN_LEVEL
            );
        } else {
            parent::setData($attributeGroup, $field, $data);
        }
    }

    private function setAccesses(
        AttributeGroupWithAccess $attributeGroup,
        array $ids,
        string $level
    ): void {
        $accesses = $attributeGroup->getAccesses();

        $userGroupsIds = $accesses
            ->filter(
                function (AttributeGroupAccess $access) use ($level) {
                    return $level === $access->getLevel();
                }
            )
            ->map(
                function (AttributeGroupAccess $access) {
                    return $access->getUserGroup()->getId();
                }
            )
            ->toArray();

        $groups = $this->userGroupRepository->findBy(['id' => $ids]);

        foreach ($groups as $group) {
            /** @var Group $group */
            if (!in_array($group->getId(), $userGroupsIds)) {
                $attributeGroup->addAccess(
                    new AttributeGroupAccess($attributeGroup, $group, $level)
                );
            }
        }

        foreach ($userGroupsIds as $id) {
            if (!in_array($id, $ids)) {
                $accessesToRemove = $accesses
                    ->filter(
                        function (AttributeGroupAccess $access) use ($id, $level) {
                            return $id === $access->getUserGroup()->getId()
                                && $level === $access->getLevel();
                        }
                    )
                    ->toArray();

                foreach ($accessesToRemove as $accessToRemove) {
                    $attributeGroup->removeAccess($accessToRemove);
                }
            }
        }
    }
}

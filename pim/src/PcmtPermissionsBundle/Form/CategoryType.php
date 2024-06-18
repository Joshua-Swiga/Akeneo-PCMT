<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Form;

use Akeneo\Pim\Enrichment\Bundle\Form\Type\CategoryType as BaseCategoryType;
use Akeneo\UserManagement\Component\Model\Group;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;

class CategoryType extends BaseCategoryType
{
    /**
     * {@inheritdoc}
     *
     * * this can be extended to include further fields *
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $this->addAccessField($builder, 'viewAccess', 'pcmt_permissions.form.allowed_to_view_products');

        $this->addAccessField($builder, 'editAccess', 'pcmt_permissions.form.allowed_to_edit_products');

        $this->addAccessField($builder, 'ownAccess', 'pcmt_permissions.form.allowed_to_own_products');

        $builder->add('applyOnChildren', CheckboxType::class, [
            'label'    => 'pcmt_permissions.form.apply_changes_on_children',
            'required' => false,
            'mapped'   => false,
        ]);
    }

    private function addAccessField(FormBuilderInterface $builder, string $name, string $label): void
    {
        $builder->add($name, EntityType::class, [
            'class'        => Group::class,
            'choice_label' => function (Group $group) {
                return $group ? $group->getName() : '';
            },
            'label'    => $label,
            'expanded' => false,
            'multiple' => true,
            'required' => true,
        ]);
    }
}
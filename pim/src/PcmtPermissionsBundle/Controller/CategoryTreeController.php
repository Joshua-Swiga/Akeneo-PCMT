<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\Controller;

use Akeneo\Pim\Enrichment\Bundle\Controller\Ui\CategoryTreeController as OriginalCategoryTreeController;
use Akeneo\Platform\Bundle\UIBundle\Flash\Message;
use Akeneo\Tool\Component\Classification\Model\CategoryInterface;
use PcmtPermissionsBundle\Service\CategoryPermissionsDefaultProvider;
use PcmtPermissionsBundle\Updater\CategoryChildrenPermissionsUpdater;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CategoryTreeController extends OriginalCategoryTreeController
{
    /** @var CategoryChildrenPermissionsUpdater */
    private $categoryChildrenPermissionsUpdater;

    /** @var CategoryPermissionsDefaultProvider */
    private $categoryPermissionsDefaultProvider;

    public function setCategoryPermissionsDefaultProvider(CategoryPermissionsDefaultProvider $provider): void
    {
        $this->categoryPermissionsDefaultProvider = $provider;
    }

    /**
     * {@inheritdoc}
     */
    public function editAction(Request $request, $id): Response
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_edit'))) {
            throw new AccessDeniedException();
        }

        $category = $this->findCategory($id);
        $this->categoryPermissionsDefaultProvider->fill($category);

        $form = $this->createForm($this->rawConfiguration['form_type'], $category, $this->getFormOptions($category));
        if ($request->isMethod('POST')) {
            $category->clearAccesses();
            $form->handleRequest($request);
            if ($form->isValid()) {
                $this->categoryPermissionsDefaultProvider->remove($category);
                $this->categorySaver->save($category);
                $message = new Message(sprintf('flash.%s.updated', $category->getParent() ? 'category' : 'tree'));
                $this->addFlash('success', $message);
                $applyOnChildren = $form->get('applyOnChildren')->getData();
                if ($applyOnChildren) {
                    $this->categoryChildrenPermissionsUpdater->update($category);
                }
            }
        }

        $template = 'PcmtPermissionsBundle:CategoryTree:%s.html.twig';

        return $this->render(
            sprintf($template, $request->get('content', 'edit')),
            [
                'form'           => $form->createView(),
                'related_entity' => $this->rawConfiguration['related_entity'],
                'acl'            => $this->rawConfiguration['acl'],
                'route'          => $this->rawConfiguration['route'],
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request, $parent = null): Response
    {
        if (false === $this->securityFacade->isGranted($this->buildAclName('category_create'))) {
            throw new AccessDeniedException();
        }

        $category = $this->categoryFactory->create();
        if ($parent) {
            $parent = $this->findCategory($parent);
            $category->setParent($parent);
        }

        $category->setCode($request->get('label'));
        $this->categoryPermissionsDefaultProvider->fill($category);

        $form = $this->createForm($this->rawConfiguration['form_type'], $category, $this->getFormOptions($category));

        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->categorySaver->save($category);
                $message = new Message(sprintf('flash.%s.created', $category->getParent() ? 'category' : 'tree'));
                $this->addFlash('success', $message);

                return new JsonResponse(
                    [
                        'route'  => $this->buildRouteName('categorytree_edit'),
                        'params' => [
                            'id' => $category->getId(),
                        ],
                    ]
                );
            }
        }

        $template = 'PcmtPermissionsBundle:CategoryTree:%s.html.twig';

        return $this->render(
            sprintf($template, $request->get('content', 'edit')),
            [
                'form'           => $form->createView(),
                'related_entity' => $this->rawConfiguration['related_entity'],
                'acl'            => $this->rawConfiguration['acl'],
                'route'          => $this->rawConfiguration['route'],
            ]
        );
    }

    public function setCategoryChildrenPermissionsUpdater(CategoryChildrenPermissionsUpdater $categoryChildrenPermissionsUpdater): void
    {
        $this->categoryChildrenPermissionsUpdater = $categoryChildrenPermissionsUpdater;
    }

    protected function getFormOptions(CategoryInterface $category): array
    {
        return [
            'validation_groups' => ['Default', 'uiForm'],
        ];
    }
}
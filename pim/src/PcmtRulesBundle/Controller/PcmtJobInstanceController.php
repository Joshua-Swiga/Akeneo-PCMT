<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\Controller;

use Akeneo\Platform\Bundle\ImportExportBundle\Controller\InternalApi\JobInstanceController;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PcmtJobInstanceController extends JobInstanceController
{
    /**
     * Get an rules job profile
     *
     * @AclAncestor("pcmt_permission_rules_view")
     */
    public function getRulesAction(string $identifier): Response
    {
        return $this->getAction($identifier);
    }

    /**
     * Create an rules profile
     *
     * @AclAncestor("pcmt_permission_rules_create")
     */
    public function createRulesAction(Request $request): Response
    {
        return $this->createAction($request, 'rules');
    }

    /**
     * Edit an rules job profile
     *
     * @AclAncestor("pcmt_permission_rules_edit")
     */
    public function putRulesAction(Request $request, string $identifier): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->putAction($request, $identifier);
    }

    /**
     * Delete an rules job profile
     *
     * @AclAncestor("pcmt_permission_rules_delete")
     */
    public function deleteRulesAction(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->deleteAction($code);
    }

    /**
     * Launch an rules job
     *
     * @AclAncestor("pcmt_permission_rules_launch")
     */
    public function launchRulesAction(Request $request, string $code): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        return $this->launchAction($request, $code);
    }
}

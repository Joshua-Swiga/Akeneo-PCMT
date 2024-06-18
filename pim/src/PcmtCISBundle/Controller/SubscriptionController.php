<?php

declare(strict_types=1);

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCISBundle\Controller;

use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use PcmtCISBundle\Entity\Subscription;
use PcmtCISBundle\Exception\FileIsWaitingForUploadException;
use PcmtCISBundle\Service\FileService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SubscriptionController
{
    /** @var NormalizerInterface */
    private $normalizer;

    /** @var ObjectUpdaterInterface */
    private $updater;

    /** @var ValidatorInterface */
    private $validator;

    /** @var SaverInterface */
    private $saver;

    /** @var NormalizerInterface */
    private $constraintViolationNormalizer;

    /** @var RemoverInterface */
    private $remover;

    /** @var FileService */
    private $fileService;

    public function __construct(
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        SaverInterface $saver,
        NormalizerInterface $constraintViolationNormalizer,
        RemoverInterface $remover,
        FileService $fileService
    ) {
        $this->normalizer = $normalizer;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->saver = $saver;
        $this->constraintViolationNormalizer = $constraintViolationNormalizer;
        $this->remover = $remover;
        $this->fileService = $fileService;
    }

    /**
     * @AclAncestor("pcmt_permission_cis")
     */
    public function createAction(Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        $data = json_decode($request->getContent(), true);
        $subscription = new Subscription();
        $this->updater->update($subscription, (array) $data);
        $violations = $this->validator->validate($subscription);

        $normalizedViolations = [];
        foreach ($violations as $violation) {
            $normalizedViolations[] = $this->constraintViolationNormalizer->normalize(
                $violation,
                'internal_api',
                ['group' => $subscription]
            );
        }

        if (count($normalizedViolations) > 0) {
            return new JsonResponse(['values' => $normalizedViolations], 400);
        }

        try {
            $this->fileService->createFileCommandAdd($subscription);

            $this->saver->save($subscription);
        } catch (FileIsWaitingForUploadException $e) {
            return $this->getGlobalErrorJsonResponse('pcmt.entity.subscription.flash.create.file_is_waiting_for_upload');
        } catch (\Throwable $e) {
            return $this->getGlobalErrorJsonResponse('pcmt.entity.subscription.flash.create.fail');
        }

        return new JsonResponse(
            $this->normalizer->normalize(
                $subscription,
                'internal_api'
            )
        );
    }

    private function getGlobalErrorJsonResponse(string $message): JsonResponse
    {
        return new JsonResponse(
            [
                'values' => [
                    [
                        'global'  => true,
                        'message' => $message,
                    ],
                ],
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    /**
     * @AclAncestor("pcmt_permission_cis")
     */
    public function reSubmitAction(Subscription $subscription): Response
    {
        try {
            $this->fileService->createFileCommandAdd($subscription);
        } catch (FileIsWaitingForUploadException $e) {
            return new JsonResponse(
                [
                    'successful' => false,
                    'message'    => 'pcmt.entity.subscription.flash.resubmit.file_is_waiting_for_upload',
                ]
            );
        } catch (\Throwable $e) {
            return new JsonResponse(
                [
                    'successful' => false,
                    'message'    => 'pcmt.entity.subscription.flash.resubmit.fail',
                ]
            );
        }

        return new JsonResponse(
            [
                'successful' => true,
                'message'    => 'pcmt.entity.subscription.flash.resubmit.success',
            ]
        );
    }

    /**
     * @AclAncestor("pcmt_permission_cis")
     */
    public function deleteAction(Subscription $subscription, Request $request): Response
    {
        if (!$request->isXmlHttpRequest()) {
            return new RedirectResponse('/');
        }

        try {
            $this->fileService->createFileCommandDelete($subscription);

            $this->remover->remove($subscription);
        } catch (FileIsWaitingForUploadException $e) {
            return $this->getGlobalErrorJsonResponse('pcmt.entity.subscription.flash.delete.file_is_waiting_for_upload');
        } catch (\Throwable $e) {
            return $this->getGlobalErrorJsonResponse('pcmt.entity.subscription.flash.delete.fail');
        }

        return new JsonResponse(
            [
                'successful' => true,
                'message'    => 'pcmt.entity.subscription.flash.delete.success',
            ]
        );
    }
}
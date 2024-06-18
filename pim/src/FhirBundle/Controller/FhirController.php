<?php
/**
 * Copyright (c) 2022, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

namespace FhirBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use FhirBundle\Entity\FhirMapping;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class FhirController extends Controller
{
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    public function createAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $fhir_repository = $this->getDoctrine()->getRepository(FhirMapping::class);
        $fhir_mapping = $fhir_repository->findOneByCode($data['code']);
        $success = false;
        $error = '';
        if (!$fhir_mapping) {
            try {
                //create
                $fhirMapping = new FhirMapping();
                $fhirMapping->setCode($data['code']);
                $fhirMapping->setType($data['type']);
                $fhirMapping->setMapping($data['mapping']);

                $this->entityManager->persist($fhirMapping);

                $this->entityManager->flush();
                $success = true;
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        } else {
            try {
                //update
                $fhir_mapping->setMapping($data['mapping']);
                $this->entityManager->persist($fhir_mapping);

                $this->entityManager->flush();
                $success = true;
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }
        //add logic to check if mapping exists
        return new JsonResponse([
            'success' => $success,
            'error'   => $error,
        ]);
    }

    public function currentMappingAction(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $fhir_repository = $this->getDoctrine()->getRepository(FhirMapping::class);
        $fhir_mapping = $fhir_repository->findOneBy([
            'code' => $data['code'],
            'type' => $data['type'],
        ]);
        $mapping = '';
        $found = false;
        if ($fhir_mapping) {
            $mapping = $fhir_mapping->getMapping();
            $found = true;
        }

        return new JsonResponse([
            'found'   => $found,
            'mapping' => $mapping,
        ]);
    }
}

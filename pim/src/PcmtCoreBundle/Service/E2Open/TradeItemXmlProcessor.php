<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
declare(strict_types=1);

namespace PcmtCoreBundle\Service\E2Open;

use PcmtCoreBundle\Connector\Mapping\E2OpenMapping;
use PcmtCoreBundle\Entity\E2OpenAttributeData;

class TradeItemXmlProcessor
{
    /** @var E2OpenAttributeData[] */
    private $foundAttributes = [];

    public function processNode(array $element, string $parent = ''): void
    {
        if (!empty($element['attributes'])) {
            // there are some additional attributes in node, process them individually
            foreach ($element['attributes'] as $name => $value) {
                $newElement = [
                    'name'  => $name,
                    'value' => $value,
                ];
                $this->processNode($newElement, $element['name']);
            }
            // but don't finish processing here, process also whole node.
        }

        if (is_array($element['value'])) {
            // there are still further nodes
            foreach ($element['value'] as $subElement) {
                $this->processNode($subElement, $element['name']);
            }
            // finish processing node in such case
            return;
        }

        $name = $parent . $element['name'];
        if (!$mappedAttributeCode = E2OpenMapping::findMappingForKey($name)) {
            $name = $element['name'];
            $mappedAttributeCode = E2OpenMapping::findMappingForKey($name);
        }

        if (!$mappedAttributeCode) {
            // no mapping defined for this node
            return;
        }

        $this->foundAttributes[$mappedAttributeCode] = new E2OpenAttributeData($name, $mappedAttributeCode, $element['value'], $element['attributes'] ?? []);
    }

    public function getFoundAttributes(): array
    {
        return $this->foundAttributes;
    }

    public function setFoundAttributes(array $foundAttributes): void
    {
        $this->foundAttributes = $foundAttributes;
    }
}
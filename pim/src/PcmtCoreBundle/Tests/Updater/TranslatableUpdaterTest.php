<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\Tests\Updater;

use PcmtCoreBundle\Entity\Attribute;
use PcmtCoreBundle\Entity\AttributeTranslation;
use PcmtCoreBundle\Updater\TranslatableUpdater;
use PHPUnit\Framework\TestCase;

class TranslatableUpdaterTest extends TestCase
{
    /**
     * @dataProvider dataUpdate
     */
    public function testUpdate(array $data): void
    {
        $attribute = new Attribute();
        $updater = new TranslatableUpdater();
        $updater->update($attribute, $data);

        $translations = $attribute->getTranslations();
        $this->assertCount(count($data), $translations);
        /** @var AttributeTranslation $translation */
        $translation = $translations->get(0);
        $this->assertSame(reset($data), $translation->getLabel());
        $keys = array_keys($data);
        $this->assertSame(reset($keys), $translation->getLocale());
    }

    public function dataUpdate(): array
    {
        return [
            [[
                'en_US' => 'english',
                'pl_PL' => 'polski',
            ]],
            [[
                'pl_PL' => 'polski',
                'en_US' => 'english',
                'fr'    => 'frenc',
            ]],
        ];
    }

    /**
     * @dataProvider dataUpdateWithEmptyLabels
     */
    public function testUpdateWithEmptyLabels(array $data): void
    {
        $attribute = new Attribute();
        $updater = new TranslatableUpdater();
        $updater->update($attribute, $data);

        $translations = $attribute->getTranslations();
        $this->assertCount(count($data) - 1, $translations);
        /** @var AttributeTranslation $translation */
        $translation = $translations->get(0);
        $this->assertSame(reset($data), $translation->getLabel());
        $keys = array_keys($data);
        $this->assertSame(reset($keys), $translation->getLocale());
    }

    public function dataUpdateWithEmptyLabels(): array
    {
        return [
            [[
                'en_US' => 'english',
                'pl_PL' => '',
            ]],
            [[
                'pl_PL' => 'polski',
                'en_US' => 'english',
                'fr'    => '',
            ]],
        ];
    }

    /**
     * @dataProvider dataUpdateDescription
     */
    public function testUpdateDescription(array $data): void
    {
        $attribute = new Attribute();
        $updater = new TranslatableUpdater();
        $updater->updateDescription($attribute, $data);

        $translations = $attribute->getTranslations();
        $this->assertCount(count($data), $translations);
        /** @var AttributeTranslation $translation */
        $translation = $translations->get(0);
        $this->assertSame(reset($data), $translation->getDescription());
        $keys = array_keys($data);
        $this->assertSame(reset($keys), $translation->getLocale());
    }

    public function dataUpdateDescription(): array
    {
        return [
            [[
                'en_US' => 'english',
                'pl_PL' => 'polski',
            ]],
            [[
                'pl_PL' => 'polski',
                'en_US' => 'english',
                'fr'    => 'frenc',
            ]],
        ];
    }

    /**
     * @dataProvider dataUpdateDescriptionWithEmptyLabels
     */
    public function testUpdateDescriptionWithEmptyLabels(array $data): void
    {
        $attribute = new Attribute();
        $updater = new TranslatableUpdater();
        $updater->updateDescription($attribute, $data);

        $translations = $attribute->getTranslations();
        $this->assertCount(count($data) - 1, $translations);
        /** @var AttributeTranslation $translation */
        $translation = $translations->get(0);
        $this->assertSame(reset($data), $translation->getDescription());
        $keys = array_keys($data);
        $this->assertSame(reset($keys), $translation->getLocale());
    }

    public function dataUpdateDescriptionWithEmptyLabels(): array
    {
        return [
            [[
                'en_US' => 'english',
                'pl_PL' => '',
            ]],
            [[
                'pl_PL' => 'polski',
                'en_US' => 'english',
                'fr'    => '',
            ]],
        ];
    }
}
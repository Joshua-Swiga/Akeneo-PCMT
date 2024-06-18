<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\FunctionalTests;

class WebContentFinder
{
    public static function getSaveButtonLocatorOnEditForm(): string
    {
        return 'button.AknButton.AknButton--apply.save';
    }

    public static function getLaunchRuleButtonLocator(): string
    {
        return 'button.AknButton.AknButton--apply';
    }

    public static function getSaveButtonLocatorOnCreateForm(): string
    {
        return 'div.AknButton.AknButton--apply.ok';
    }

    public static function getConfirmationButtonLocator(): string
    {
        return 'div.AknButton.AknButtonList-item.AknButton--apply.ok.ok';
    }

    public static function getFilterInput(): string
    {
        return 'input.AknFilterBox-search';
    }

    public static function getIconLocator(string $type): string
    {
        return 'a.AknIconButton.AknIconButton--'. $type;
    }

    public static function getNumberOfResultsLocator(): string
    {
        return 'div.AknTitleContainer-title > div';
    }

    public static function getFlashSuccessMessageLocator(): string
    {
        return '.alert-success.AknFlash--success';
    }

    public static function getFlashErrorMessageLocator(): string
    {
        return '.alert-error.AknFlash--error';
    }

    public static function getSelectFieldLocator(): string
    {
        return '.select2-choice';
    }

    public static function getGridRowsLocator(): string
    {
        return '.AknGrid-body > .AknGrid-bodyRow';
    }

    public static function getGridCellLocator(): string
    {
        return '.AknGrid-bodyRow > .AknGrid-bodyCell';
    }

    public static function getSelectorForLocator(string $locator): string
    {
        return sprintf(
            "document.querySelector('%s')",
            $locator
        );
    }

    public static function getElementWithIdLocatorExistsExpression(string $id): string
    {
        return "document.querySelectorAll('#" . $id . "').length == 1";
    }
}
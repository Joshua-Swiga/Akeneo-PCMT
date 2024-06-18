<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtPermissionsBundle\FunctionalTests;

use Behat\Behat\Context\Context;
use WebContentFinder;

class WebContext extends \SeleniumBaseContext implements Context
{
    /** @var string[] */
    private $chosenPermissions = [];

    /**
     * @When I go to :categoryName category tree child
     */
    public function goToCategoryTreeChild(string $categoryName): void
    {
        $locator = '#node_1 > ul > li > a';
        $result = $this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $locator);
        if (!$result) {
            throw new \Exception('Link not found.');
        }
        $tree = $this->getSession()->getPage()->findAll('css', $locator);

        foreach ($tree as $element) {
            if ($element->getText() === $categoryName) {
                $this->chosenCategory = $element->getText();
                $element->click();

                return;
            }
        }

        throw new \InvalidArgumentException('Link ' . $categoryName . ' does not exist.');
    }

    /**
     * @And I clear default permissions
     * @When I clear default permissions
     */
    public function iClearDefaultPermissions(): void
    {
        $this->clearAllPermissionInputs();
    }

    /**
     * @When I edit permissions with parameters :viewAccess :editAccess :ownProducts
     * @And I edit permissions with parameters :viewAccess :editAccess :ownProducts
     */
    public function editPermissionsWithParameters(
        string $viewAccessGroup,
        string $editAccessGroup,
        string $ownAccessGroup
    ): void {
        $accessGroups = [
            '#s2id_pim_category_viewAccess > ul' => $viewAccessGroup,
            '#s2id_pim_category_editAccess > ul' => $editAccessGroup,
            '#s2id_pim_category_ownAccess > ul'  => $ownAccessGroup,
        ];
        $this->clearAllPermissionInputs();
        array_walk(
            $accessGroups,
            function ($group, $select): void {
                $this->clickOnSelect($select);
                $this->selectAccessOption($group);
            }
        );

        $this->chosenPermissions = [$viewAccessGroup, $editAccessGroup, $ownAccessGroup];
    }

    /**
     * @And I save
     * @When I save
     */
    public function iSave(): void
    {
        $locator = 'div.AknTitleContainer-actionsContainer.AknButtonList > button';
        $this->waitUntil(\WebContentFinder::SAVE_BUTTON_EXISTS, $locator);
        $saveBtn = $this->getSession()->getPage()->find('css', $locator);
        $saveBtn->click();
    }

    /**
     * @Then I should see correct permissions set
     */
    public function iShouldSeeCorrectPermissionsSet(): void
    {
        $selectors = $this->getAllPermissionInputValues();
        array_walk($selectors, function ($selector): void {
            if (!in_array($selector->getText(), $this->chosenPermissions)) {
                throw new \Exception('Permissions are not correctly set.');
            }
        });
    }

    /**
     * @When I go to the products list for :categoryName category
     */
    public function iGoToTheProductsListForCategory(string $categoryName): void
    {
        $this->waitAndFollowLink('Products');
        $locator = '#container > div > div > div.AknColumn > div.AknColumn-inner.column-inner > div.AknColumn-innerTop > div > div:nth-child(2) > div.AknDropdown.AknColumn-block.category-switcher > div:nth-child(1)';
        $result = $this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $locator);
        if (!$result) {
            throw new \Exception('Element not found.');
        }
        $selector = $this->getSession()->getPage()->find('css', $locator);
        $selector->click();

        $locator = '#node_1 > ul > li > a';
        $selectors = $this->getSession()->getPage()->findAll('css', $locator);

        array_walk($selectors, function ($selector) use ($categoryName): void {
            $position = mb_strpos($selector->getText(), '(');
            $currentCategoryName = is_bool($position) ? trim($selector->getText()) : trim(mb_substr($selector->getText(), 0, $position));
            if ($currentCategoryName === $categoryName) {
                $selector->click();

                return;
            }
        });

        $this->getSession()->wait(4000);
    }

    /**
     * @Then I should see :count products on the list
     */
    public function iShouldSeeProductsOnTheList(int $count): void
    {
        $locator = '#container > div > div > div.AknDefault-contentWithBottom > div.AknDefault-mainContent.entity-edit-form.edit-form > header > div:nth-child(1) > div.AknTitleContainer-mainContainer > div:nth-child(1) > div:nth-child(2) > div.AknTitleContainer-title > div';
        $result = $this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $locator);
        if (!$result) {
            throw new \Exception('Element not found.');
        }
        $selector = $this->getSession()->getPage()->find('css', $locator);
        $numProducts = (int) explode(' ', $selector->getText())[0];
        if ($numProducts !== $count) {
            throw new \Exception('Expected products (' . $numProducts . ') does not match.');
        }
    }

    /**
     * @Then I should see more than zero products
     */
    public function iShouldSeeMoreThanZeroProducts(): void
    {
        $productsOnThePage = $this->getNumberOfResultsFromResultsPage();
        if ($productsOnThePage <= 0) {
            throw new \Exception('The number of results should be more than 0.');
        }
    }

    private function getNumberOfResultsFromResultsPage(): int
    {
        $resultsText = $this->getSession()
            ->getPage()
            ->find('css', 'div.AknTitleContainer-title > div')
            ->getText();

        $resultsCount = explode(' ', $resultsText);

        return (int) $resultsCount[0];
    }

    private function clickOnSelect(string $selectLocator): void
    {
        $selector = $this->getSession()->getPage()->find('css', $selectLocator);
        $selector->click();
    }

    private function selectAccessOption(string $accessGroup): void
    {
        $locators = '#select2-drop > ul > li';
        $selectors = $this->getSession()->getPage()->findAll('css', $locators);
        foreach ($selectors as $selector) {
            if ($selector->getText() === $accessGroup) {
                $selector->click();

                return;
            }
        }
    }

    private function getAllPermissionInputValues(): array
    {
        $locators = [
            '#s2id_pim_category_viewAccess > ul > li.select2-search-choice > div',
            '#s2id_pim_category_editAccess > ul > li.select2-search-choice > div',
            '#s2id_pim_category_ownAccess > ul > li.select2-search-choice > div',
        ];

        return array_map(
            function ($locator) {
                return $this->getSession()->getPage()->find('css', $locator);
            },
            $locators
        );
    }

    private function getAllPermissionInputs(): array
    {
        $locators = [
            '#s2id_pim_category_viewAccess > ul > li.select2-search-choice > a',
            '#s2id_pim_category_editAccess > ul > li.select2-search-choice > a',
            '#s2id_pim_category_ownAccess > ul > li.select2-search-choice > a',
        ];

        return array_map(
            function ($locator) {
                return $this->getSession()->getPage()->find('css', $locator);
            },
            $locators
        );
    }

    private function clearAllPermissionInputs(): void
    {
        $inputs = $this->getAllPermissionInputs();
        array_walk(
            $inputs,
            function ($input): void {
                $input->click();
            }
        );
    }
}
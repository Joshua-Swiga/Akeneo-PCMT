<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtDraftBundle\FunctionalTests;

use WebContentFinder;

class WebContext extends \SeleniumBaseContext
{
    public const WAIT_TIME_LONG = 4000;

    public const WAIT_TIME_MEDIUM = 2000;

    /** @var int */
    private $numberOfResults;

    /**
     * @When I wait and click edit on first product
     */
    public function iWaitAndClickEditOnFirstProduct(): void
    {
        $cssLocator = 'a.AknIconButton.AknIconButton--small.AknIconButton--edit.AknButtonList-item';
        $this->waitToLoadPage('PRODUCTS');
        if (!$this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $cssLocator)) {
            throw new \Exception('No product to edit found.');
        }
        $link = $this->getSession()->getPage()->find('css', $cssLocator);
        $link->mouseOver();
        $this->iWaitSecond(1);
        // find again to avoid error: stale element reference: element is not attached to the page document
        $link = $this->getSession()->getPage()->find('css', $cssLocator);
        $link->click();
    }

    /**
     * @When I wait and click button "Edit as a draft"
     */
    public function iWaitAndClickButton(): void
    {
        $cssLocator = 'div.AknTitleContainer-rightButton button.AknButton.AknButton--apply.save';
        $result = $this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $cssLocator);
        if (!$result) {
            throw new \Exception('No button "Edit as a draft" found');
        }

        $link = $this->getSession()->getPage()->find('css', $cssLocator);
        $link->click();
    }

    /**
     * @When I wait and click approve on first draft
     */
    public function iWaitAndClickApproveOnFirstDraft(): void
    {
        $locator = 'a.AknIconButton.draft-approve';
        $result = $this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $locator);
        if (!$result) {
            throw new \Exception('No draft to approve found.');
        }

        $links = $this->getSession()->getPage()->findAll('css', $locator);
        $firstLink = reset($links);
        $firstLink->click();
    }

    /**
     * @When I confirm approval
     */
    public function iConfirmApproval(): void
    {
        $cssLocator = 'div.AknButton.AknButtonList-item.AknButton--apply.ok.ok';
        if (!$this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $cssLocator)) {
            throw new \Exception('Approval button not found.');
        }
        $buttonDiv = $this->getSession()->getPage()->find('css', $cssLocator);
        $buttonDiv->click();
    }

    /**
     * @When I select :num draft checkboxes for mass action
     */
    public function iSelectDraftCheckboxesForMassAction(int $num): void
    {
        $checkboxes = $this->getSession()->getPage()->findAll(
            'css',
            'td.AknGrid-bodyCell.input-cell.draft-checkbox-bodyCell.AknGrid-bodyCell--actions > label > input'
        );

        //always select last added drafts
        for ($i = count($checkboxes) - 1; $i >= (count($checkboxes) - $num); $i--) {
            $checkbox = $checkboxes[$i];
            if ($checkbox instanceof \Behat\Mink\Element\NodeElement) {
                $checkbox->check();
            }
        }

        $this->getSession()->wait(self::WAIT_TIME_MEDIUM);
    }

    /**
     * @Given I read number of drafts
     */
    public function iReadNumberOfDrafts(): void
    {
        $this->waitToLoadPage('DRAFTS');
        $this->numberOfResults = $this->getNumberOfResultsFromDraftsPage();
    }

    public function getNumberOfResultsFromDraftsPage(): int
    {
        $this->getSession()->wait(1000);

        $locator = 'div.AknTitleContainer-title > div';
        $this->waitUntil(WebContentFinder::LOCATOR_EXISTS, $locator);

        $resultsText = $this->getSession()
            ->getPage()
            ->find('css', $locator)
            ->getText();

        $resultsCount = explode(' ', $resultsText);

        return (int) $resultsCount[0];
    }

    /**
     * @Then the number of drafts should be lower by :quantity, try :attempts times
     */
    public function theNumberOfResultsShouldBeLowerBy(int $quantity, int $attempts): void
    {
        $previous = $this->numberOfResults;
        if (!$previous) {
            throw new \Exception('Previous number of drafts is 0.');
        }

        // we make a number of attempts, as the job in background may last for some time
        for ($i = 0; $i < $attempts; $i++) {
            $this->waitAndFollowLink('Activity');
            $this->waitToLoadPage('DASHBOARD');
            $this->waitAndFollowLink('Drafts');
            $this->waitToLoadPage('DRAFTS');

            $newNumber = $this->getNumberOfResultsFromDraftsPage();
            if ($previous - $quantity === $newNumber) {
                break;
            }
        }

        if ($previous - $quantity !== $newNumber) {
            throw new \Exception(
                'Wrong number of drafts. Should be: ' . round($previous - $quantity) . ', is: ' . $newNumber
            );
        }
    }

    /**
     * @Given I wait :arg1 seconds
     */
    public function iWaitSecond(int $arg1): void
    {
        $this->getSession()->wait($arg1 * 1000);
    }
}
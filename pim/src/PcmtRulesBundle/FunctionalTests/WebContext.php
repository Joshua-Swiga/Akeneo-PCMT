<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtRulesBundle\FunctionalTests;

use Behat\Behat\Context\Context;
use Behat\Mink\Element\NodeElement;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;

class WebContext extends \SeleniumBaseContext implements Context
{
    /** @var int */
    private $numberOfResults;

    /**
     * @When I save create form
     */
    public function iSaveCreateForm(): void
    {
        $locator = WebContentFinder::getSaveButtonLocatorOnCreateForm();
        if (!$this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator))) {
            throw new \Exception('Save element not found');
        }
        $saveBtn = $this->getSession()->getPage()->find('css', $locator);
        $saveBtn->click();
    }

    /**
     * @When I save edit form
     */
    public function iSaveEditForm(): void
    {
        $locator = WebContentFinder::getSaveButtonLocatorOnEditForm();
        $this->waitForThePageToLoad();
        if (!$this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator))) {
            throw new \Exception('Save element not found');
        }
        $saveBtn = $this->getSession()->getPage()->find('css', $locator);
        $saveBtn->click();
    }

    /**
     * @When I fill in :field with timestamp
     */
    public function fillFieldWithTimestamp(string $field): void
    {
        $this->fillField($field, (new \DateTime())->getTimestamp());
    }

    /**
     * @Then I click on create rule
     */
    public function iClickOnCreateRule(): void
    {
        $this->waitUntilExpression(WebContentFinder::getElementWithIdLocatorExistsExpression('create-button-extension'));
        $buttonDiv = $this->getSession()->getPage()->findById('create-button-extension');
        if (!$buttonDiv) {
            throw new \Exception('Create rule button not found');
        }
        $buttonDiv->click();
        $this->waitForThePageToLoad();
    }

    /**
     * @When I click launch button
     */
    public function iClickLaunchButton(): void
    {
        $locator = WebContentFinder::getLaunchRuleButtonLocator();
        if (!$this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator))) {
            throw new \Exception('Launch button not found.');
        }
        $saveBtn = $this->getSession()->getPage()->find('css', $locator);
        $saveBtn->click();
    }

    /**
     * @When I click on select field no :number
     */
    public function iClickOnSelectFieldNumber(int $number): void
    {
        $elements = $this->getAllSelectFields();
        if (!$elements) {
            throw new \Exception('Select field no ' . $number . ' not found.');
        }
        $elements = array_slice($elements, $number - 1, 1);
        $element = reset($elements);
        $element->click();
    }

    /**
     * @When I choose the first option
     */
    public function iChooseTheFirstOption(): void
    {
        $attributeGroupSelect = $this->getSession()->getPage()->findAll('css', '#select2-drop > ul > li');
        foreach ($attributeGroupSelect as $selectOption) {
            $selectOption->click();

            return;
        }
        throw new \Exception('Did not find any option.');
    }

    /**
     * @When I choose :option option
     */
    public function iChooseOption(string $option): void
    {
        $attributeGroupSelect = $this->getSession()->getPage()->findAll('css', '#select2-drop  ul > li > div.select2-result-label');
        foreach ($attributeGroupSelect as $selectOption) {
            if (false !== mb_strpos($selectOption->getText(), $option)) {
                $selectOption->click();

                return;
            }
        }
        throw new \Exception('Did not find option matching to: ' . $option);
    }

    private function getAllSelectFields(): array
    {
        $locator = WebContentFinder::getSelectFieldLocator();
        $this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator));

        return $this->getSession()->getPage()->findAll('css', $locator);
    }

    /**
     * @Then I should get :count errors
     */
    public function iShouldGetErrors(int $count): void
    {
        $this->waitForThePageToLoad();
        $locators = 'span.error-message';
        $selectors = $this->getSession()->getPage()->findAll('css', $locators);
        if ($count !== count($selectors)) {
            throw new \Exception(
                sprintf('Wrong number of errors. Should be %d, in test: %d', $count, count($selectors))
            );
        }
    }

    /**
     * @Given I wait
     */
    public function iWait(): void
    {
        $this->waitForThePageToLoad();
    }

    /**
     * @Given I should get success message
     */
    public function iShouldGetSuccessMessage(): void
    {
        $locator = WebContentFinder::getFlashSuccessMessageLocator();
        $result = $this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator));
        if (!$result) {
            throw new \Exception('Success message not found');
        }
    }

    /**
     * @Given I should get error message
     */
    public function iShouldGetErrorMessage(): void
    {
        $locator = WebContentFinder::getFlashErrorMessageLocator();
        $result = $this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator));
        if (!$result) {
            throw new \Exception('Error message not found');
        }
    }

    /**
     * @Given I read number of rules
     */
    public function iReadNumberOfRules(): void
    {
        $this->waitToLoadPage('RULES');
        $this->numberOfResults = $this->getNumberOfResultsOnList();
    }

    public function getNumberOfResultsOnList(): int
    {
        $this->getSession()->wait(1000);

        $locator = WebContentFinder::getNumberOfResultsLocator();
        $this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator));

        $resultsText = $this->getSession()
            ->getPage()
            ->find('css', $locator)
            ->getText();

        $resultsCount = explode(' ', $resultsText);

        return (int) $resultsCount[0];
    }

    /**
     * @When I wait and click delete on last rule
     */
    public function iWaitAndClickDeleteOnLastRule(): void
    {
        $this->iWaitAndClickIconOnLastRule('trash');
    }

    /**
     * @When I wait and click edit on last rule
     */
    public function iWaitAndClickEditOnLastRule(): void
    {
        $this->iWaitAndClickIconOnLastRule('edit');
    }

    /**
     * @When I filter rules to :text
     */
    public function iFilterRulesTo(string $text): void
    {
        $locator = WebContentFinder::getFilterInput();
        if (!$this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator))) {
            throw new \Exception('Filter input not found.');
        }
        $buttonDiv = $this->getSession()->getPage()->find('css', $locator);
        $buttonDiv->click();

        $buttonDiv->setValue($text);

        $this->getSession()->wait(self::WAIT_TIME_SHORT);
    }

    /**
     * @When I wait and click view on last rule
     */
    public function iWaitAndClickViewOnLastRule(): void
    {
        $this->iWaitAndClickIconOnLastRule('view');
    }

    private function iWaitAndClickIconOnLastRule(string $type): void
    {
        $locator = WebContentFinder::getIconLocator($type);
        $result = $this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator));
        if (!$result) {
            throw new \Exception('No rules found.');
        }

        $links = $this->getSession()->getPage()->findAll('css', $locator);
        $lastLink = end($links);
        $lastLink->click();
    }

    /**
     * @When I confirm delete
     */
    public function iConfirmDelete(): void
    {
        $locator = WebContentFinder::getConfirmationButtonLocator();
        if (!$this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator))) {
            throw new \Exception('Delete button not found.');
        }
        $buttonDiv = $this->getSession()->getPage()->find('css', $locator);
        $buttonDiv->click();
    }

    /**
     * @Then the number of rules should be lower by :quantity
     */
    public function theNumberOfResultsShouldBeLowerBy(int $quantity): void
    {
        $previous = $this->numberOfResults;
        if (!$previous) {
            throw new \Exception('Previous number of rules is 0.');
        }

        $this->waitToLoadPage('RULES');
        $newNumber = $this->getNumberOfResultsOnList();

        if ($previous - $quantity !== $newNumber) {
            throw new \Exception(
                'Wrong number of rules. Should be: ' . round($previous - $quantity) . ', is: ' . $newNumber
            );
        }
    }

    protected function waitUntilExpression(string $expression): bool
    {
        return $this->getSession()->wait(
            self::WAIT_TIME_MAX,
            $expression
        );
    }

    /**
     * @Then first job on the list should be :text with status :status
     */
    public function firstJobOnTheListShouldBe(string $text, string $status): void
    {
        $attempts = 5;
        // we make a number of attempts, as the job in background may last for some time
        for ($i = 1; $i <= $attempts; $i++) {
            if ($this->firstJobOnTheListShouldBeMakeAttempt($text, $status)) {
                break;
            }
            if ($i === $attempts) {
                throw new ExpectationFailedException('First job on the process tracker does not match criteria.');
            }
            $this->waitForThePageToLoad();
        }
    }

    private function firstJobOnTheListShouldBeMakeAttempt(string $text, string $status): bool
    {
        $this->waitAndFollowLink('Activity');
        $this->waitAndFollowLink('Process tracker');
        $row = $this->getFirstGridRow();
        try {
            Assert::assertStringContainsString($text, $row->getHtml());
            Assert::assertStringContainsString($status, $row->getHtml());

            return true;
        } catch (ExpectationFailedException $e) {
            return false;
        }
    }

    private function getFirstGridRow(): ?NodeElement
    {
        $locator = WebContentFinder::getGridRowsLocator();
        $result = $this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator));
        if (!$result) {
            throw new \Exception('No grid rows found.');
        }

        $rows = $this->getSession()->getPage()->findAll('css', $locator);
        if (!$rows) {
            throw new \Exception('No grid row elements found.');
        }

        return reset($rows);
    }

    /**
     * @When I click on first job
     */
    public function iClickOnFirstJob(): void
    {
        $row = $this->getFirstGridRow();
        $row->click();
        $this->waitForThePageToLoad();
    }

    /**
     * @Then I should see the :text row with value :value
     */
    public function iShouldSeeTheRow(string $text, string $value): void
    {
        if (!$this->findRowWithValue($text, $value)) {
            throw new \Exception('Row with value not found.');
        }
    }

    private function findRowWithValue(string $text, string $value): bool
    {
        $locator = WebContentFinder::getGridCellLocator();
        $result = $this->waitUntilExpression(WebContentFinder::getSelectorForLocator($locator));
        if (!$result) {
            throw new \Exception('No grid cells found.');
        }
        $cells = $this->getSession()->getPage()->findAll('css', $locator);
        if (!$cells) {
            throw new \Exception('No grid cells found.');
        }
        foreach ($cells as $i => $cell) {
            if ($text === $cell->getText()) {
                $nextCell = $cells[$i + 1];
                if ($nextCell) {
                    if ($value === $nextCell->getText()) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
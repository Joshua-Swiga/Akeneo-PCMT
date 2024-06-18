<?php

declare(strict_types=1);

/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

namespace PcmtCoreBundle\FunctionalTests;

use Behat\Behat\Context\Context;
use Carbon\Carbon;

class WebContext extends \SeleniumBaseContext implements Context
{
    public const WAIT_TIME_LONG = 8000;

    public const WAIT_TIME_SHORT = 1000;

    /** @var int */
    private $numberOfResults = 0;

    /**
     * @When I press the "Create Attribute" button
     */
    public function iPressTheButtonAndWaitForModalToAppear(): void
    {
        $id = 'attribute-create-button';
        $this->waitUntil(\WebContentFinder::ELEMENT_WITH_ID_EXISTS, $id);
        $settingsTab = $this->getSession()->getPage()->find('css', '#' . $id);
        $settingsTab->click();
    }

    /**
     * @And I select :attributeType on modal
     * @When I select :attributeType on modal
     */
    public function iChooseAttributeType(string $attributeType): void
    {
        $this->waitUntil(\WebContentFinder::ATTRIBUTE_TYPE_EXISTS, $attributeType);
        $attributeTypeSpans = $this->getSession()->getPage()->findAll(
            'css',
            'body > div.modal.in > div.AknFullPage > div > div.modal-body > div > span'
        );
        foreach ($attributeTypeSpans as $attributeTypeSpan) {
            if ($attributeTypeSpan->getText() === $attributeType) {
                $attributeTypeSpan->click();
                $this->waitUntil(\WebContentFinder::ATTRIBUTE_EDIT_PAGE);

                return;
            }
        }
        throw new \Exception('Did not find matching attribute type to ' . $attributeType);
    }

    /**
     * @When I choose :group option
     * @And I choose :group option
     */
    public function iChooseOption(string $option): void
    {
        $attributeGroupSelect = $this->getSession()->getPage()->findAll('css', '#select2-drop > ul > li');
        foreach ($attributeGroupSelect as $selectOption) {
            if ($selectOption->getText() === $option) {
                $selectOption->click();

                return;
            }
        }
        throw new \Exception('Did not find option matching to: ' . $option);
    }

    /**
     * @When I choose :attribute1 :attribute2 :attribute3 attributes for concatenated fields
     * @And I choose :attribute1 :attribute2 :attribute3 for concatenated fields
     */
    public function iChooseAttributesForConcatenatedFields(string $attribute1, string $attribute2, string $attribute3): void
    {
        $attributesSelect['#s2id_pcmt_enrich_form_attribute1 > a'] = $attribute1;
        $attributesSelect['#s2id_pcmt_enrich_form_attribute2 > a'] = $attribute2;
        $attributesSelect['#s2id_pcmt_enrich_form_attribute3 > a'] = $attribute3;

        foreach ($attributesSelect as $selector => $option) {
            $select = $this->getSession()->getPage()->find('css', $selector);
            $select->click();
            $this->iChooseOption($option);
        }
    }

    /**
     * @And I save
     * @When I save
     */
    public function iSave(): void
    {
        $locator = 'div.AknTitleContainer-rightButton > button';
        $this->waitUntil(\WebContentFinder::SAVE_BUTTON_EXISTS, $locator);
        $settingsTab = $this->getSession()->getPage()->find('css', $locator);
        $settingsTab->click();
        $this->waitUntil(\WebContentFinder::MESSAGE_EXISTS);
    }

    /**
     * @When I check :num products
     */
    public function iCheckProducts(int $num): void
    {
        $this->waitForThePageToLoad();
        $checkboxes = $this->getSession()->getPage()
            ->findAll('css', 'td.AknGrid-bodyCell > input[type=checkbox]');
        for ($i = 0; $i < $num; $i++) {
            $checkbox = $checkboxes[$i];
            $checkbox->click();
        }
    }

    /**
     * @When I click delete
     */
    public function iClickDelete(): void
    {
        $this->getSession()->wait(self::WAIT_TIME_SHORT);
        $buttons = $this->getSession()->getPage()
            ->findAll('css', 'a.AknButton--important');
        foreach ($buttons as $button) {
            if ('Delete' === $button->getText()) {
                break;
            }
        }
        if (empty($button)) {
            throw new \Exception('No delete button.');
        }
        $button->click();
    }

    /**
     * @When I confirm delete
     */
    public function iConfirmDelete(): void
    {
        $this->getSession()->wait(self::WAIT_TIME_LONG);
        $buttons = $this->getSession()->getPage()
            ->findAll('css', 'div.AknButton--important');
        foreach ($buttons as $button) {
            if ('Delete' === $button->getText()) {
                break;
            }
        }
        if (empty($button)) {
            throw new \Exception('No delete confirmation button.');
        }
        $button->click();
    }

    /**
     * @Then the number of results should be lower by :quantity, try :attempts times
     */
    public function theNumberOfResultsShouldBeLowerBy(int $quantity, int $attempts): void
    {
        // we make a number of attempts, as the job in background may last for some time
        for ($i = 0; $i < $attempts; $i++) {
            $this->clickLink('Activity');
            $this->waitForThePageToLoad();
            $this->clickLink('Products');
            $this->waitForThePageToLoad();

            $previous = $this->numberOfResults;
            $newNumber = $this->getNumberOfResultsFromResultsPage();
            if ($previous - $quantity === $newNumber) {
                break;
            }
        }

        $expected = (int) ($previous - $quantity);
        if ($expected !== $newNumber) {
            throw new \Exception('Wrong number of results. Should be: ' . $expected.' , is: ' . $newNumber);
        }
    }

    public function getNumberOfResultsFromResultsPage(): int
    {
        $resultsText = $this->getSession()
            ->getPage()
            ->find('css', 'div.AknTitleContainer-title > div')
            ->getText();

        $resultsCount = explode(' ', $resultsText);

        return (int) $resultsCount[0];
    }

    /**
     * @Given I read number of products
     */
    public function iReadNumberOfProducts(): void
    {
        $this->numberOfResults = $this->getNumberOfResultsFromResultsPage();
    }

    /**
     * @And I should delete created attribute
     * @Then I should delete created attribute
     */
    public function iShouldDeleteCreatedAttribute(): void
    {
        $locator = 'a.AknIconButton.AknIconButton--small.AknIconButton--trash.AknButtonList-item';
        if (!$this->waitUntil(\WebContentFinder::LOCATOR_EXISTS, $locator)) {
            throw new \Exception('Delete icon not found.');
        }
        $page = $this->getSession()->getPage();
        $deleteSelector = $page->find('css', $locator);
        $deleteSelector->click();

        // once modal appears
        $moduleLocator = 'div.AknButton.AknButtonList-item.AknButton--apply.AknButton--important.ok';
        if (!$this->waitUntil(\WebContentFinder::LOCATOR_EXISTS, $moduleLocator)) {
            throw new \Exception('Confirmation button not found.');
        }
        $deleteButton = $page->find('css', $moduleLocator);
        $deleteButton->click();
    }

    /**
     * @Given I go to the Settings page
     */
    public function goToTheSettingsPage(): void
    {
        $this->clickLink('Settings');

        if (!$this->waitUntil(\WebContentFinder::BREADCRUMB_EXISTS)) {
            throw new \RuntimeException('Can not load the Settings page.');
        }
    }

    /**
     * @Given I go to the Family page
     */
    public function goToTheFamilyPage(): void
    {
        $this->clickLink('Families');

        if (!$this->waitUntil(\WebContentFinder::BREADCRUMB_EXISTS)) {
            throw new \RuntimeException('Can not load the Families page.');
        }
    }

    /**
     * @Given I click on create family
     */
    public function iClickOnCreateFamily(): void
    {
        $locator = '//*[@id="create-button-extension"]';
        $button = $this->getSession()->getPage()->find('xpath', $locator);

        $button->click();

        if (!$this->waitUntil(\WebContentFinder::LOCATOR_EXISTS, '#creation_code')) {
            throw new \RuntimeException('Can not open form for creating the family.');
        }
    }

    /**
     * @When I fill in the family code with the value
     */
    public function iFillTheFamilyCodeWithTheValue(): void
    {
        $fieldLocator = 'creation_code';
        $value = 'Family_' . Carbon::now()->getTimestamp();

        $this->getSession()->getPage()->fillField($fieldLocator, $value);
    }

    /**
     * @When I click save button
     */
    public function iClickSaveButton(): void
    {
        $locator = 'div.AknButtonList > div.AknButton.AknButtonList-item.AknButton--apply.ok';

        $this->waitUntil(\WebContentFinder::SAVE_BUTTON_EXISTS, $locator);
        $button = $this->getSession()->getPage()->find('css', $locator);

        $button->click();
    }

    /**
     * @Then I should see the flash message with success
     */
    public function iShouldSeeTheFlashMessageWithSuccess(): void
    {
        if (!$this->waitUntil(\WebContentFinder::FLASH_SUCCESS_MESSAGE_EXISTS)) {
            throw new \RuntimeException('The form has not been saved.');
        }
    }

    /**
     * @Given I click on the MD_HUB family
     */
    public function iClickOnTheFirstFamily(): void
    {
        $locator = '.AknGrid';
        if (!$this->waitUntil(\WebContentFinder::LOCATOR_EXISTS, $locator)) {
            throw new \RuntimeException('The Datagrid has not been loaded yet.');
        }

        $locator = '.AknGrid > tbody > tr:nth-child(1)';

        $row = $this->getSession()->getPage()->find('css', $locator);

        $row->click();

        if (!$this->waitUntil(\WebContentFinder::BREADCRUMB_EXISTS)) {
            throw new \RuntimeException('Can not load the form.');
        }
    }

    /**
     * @When I add a French translation
     */
    public function iAddAFrenchTranslation(): void
    {
        $locator = '#pim_enrich_family_form_label_fr_FR';
        if (!$this->waitUntil(\WebContentFinder::LOCATOR_EXISTS, $locator)) {
            throw new \RuntimeException('Can not load the form.');
        }

        $fieldLocator = 'pim_enrich_family_form_label_fr_FR';
        $value = 'FR Test';

        $this->getSession()->getPage()->fillField($fieldLocator, $value);
    }

    /**
     * @Given /^I click the Save button$/
     */
    public function iClickTheSaveButton(): void
    {
        $locator = '.save';
        $button = $this->getSession()->getPage()->find('css', $locator);

        $button->click();
    }
}

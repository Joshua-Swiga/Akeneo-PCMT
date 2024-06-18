<?php
/**
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

declare(strict_types=1);

/**
 * Class WebContentFinder
 * @deprecated
 *
 * The contents of this class should rather be in specific bundles.
 * The example can be found in PcmtRulesBundle.
 */
class WebContentFinder implements WebContentFinderInterface
{
    public const LOGIN_FORM_EXISTS = "login-form-exists";

    public const BREADCRUMB_EXISTS = "breadcrumb-exists";

    public const BREADCRUMB_ENDS_WITH = "breadcrumb-ends-with";

    public const LINK_TO_FOLLOW_EXISTS = "link-to-follow-exists";

    public const ELEMENT_WITH_ID_EXISTS = "element-with-id-exists";

    public const ATTRIBUTE_TYPE_EXISTS = "attribute-type-exists";

    public const ATTRIBUTE_EDIT_PAGE = "attribute-edit-page";

    public const SAVE_BUTTON_EXISTS = "save-button-exists";

    public const MESSAGE_EXISTS = "message-exists";

    public const FLASH_SUCCESS_MESSAGE_EXISTS = "flash-success-message-exists";

    public const FLASH_SUCCESS_MESSAGE_CONTAINS = "flash-success-message-contains";

    public const LOCATOR_EXISTS = "locator-exists";

    public static function getContentCondition(string $element, string $extraData = ""): ?string
    {
        switch ($element) {
            case self::LOGIN_FORM_EXISTS:
                return "document.querySelector('.AknLogin-form') && document.querySelector('.AknLogin-form').children.length >= 2";
            case self::BREADCRUMB_EXISTS:
                return "document.querySelector('.AknBreadcrumb') != null";
            case self::BREADCRUMB_ENDS_WITH:
                return "document.querySelector('.AknBreadcrumb').innerText.endsWith('". $extraData ."')";
            case self::LINK_TO_FOLLOW_EXISTS:
                return "[...document.querySelectorAll('a')].filter(a => a.textContent.includes('" . $extraData . "')).length > 0";
            case self::ELEMENT_WITH_ID_EXISTS:
                return "document.querySelectorAll('#" . $extraData . "').length == 1";
            case self::ATTRIBUTE_TYPE_EXISTS:
                return "[...document.querySelectorAll('body > div.modal.in > div.AknFullPage > div > div.modal-body > div > span')].filter(div => div.innerText == '" . $extraData . "').length > 0";
            case self::ATTRIBUTE_EDIT_PAGE:
                return "document.querySelectorAll('.tabsection-content').length > 0";
            case self::SAVE_BUTTON_EXISTS:
                return "document.querySelector('" . $extraData . "').innerText == 'SAVE'";
            case self::MESSAGE_EXISTS:
                return "document.querySelector('.flash-messages-holder').childNodes.length > 0";
            case self::FLASH_SUCCESS_MESSAGE_EXISTS:
                return "document.querySelector('.alert-success.AknFlash--success') != null";
            case self::FLASH_SUCCESS_MESSAGE_CONTAINS:
                return "document.querySelector('.alert-success.AknFlash--success').innerText === '" . $extraData . "'";
            case self::LOCATOR_EXISTS:
                return "document.querySelector('" . $extraData . "') != null";
        }
        return null;
    }
}

/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';
/**
 * Change family operation
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/mass-edit-form/product/operation',
    ],
    function (
        BaseOperation,
    ) {
        return BaseOperation.extend({
            /**
             * {@inheritdoc}
             */
            reset: function () {
                return false;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                return false;
            },

            /**
             * Update the form model from a dom event
             *
             * @param {event} event
             */
            updateModel: function (event) {
                return false;
            },

            /**
             * update the form model
             *
             * @param {string} family
             */
            setValue: function (family) {
                return false;
            },

            /**
             * Get the current model value
             *
             * @return {string}
             */
            getValue: function () {
                return false;
            },

            getLabel: function () {
                return false;
            },

            getTitle() {
                return false;
            },

            getIllustrationClass: function () {
                return false;
            },

            getDescription: function () {
                return false;
            },

            getCode: function () {
                return false;
            },

            getIcon: function () {
                return false;
            },

            getJobInstanceCode: function () {
                return false;
            },

            setReadOnly: function (readOnly) {
                return false;
            },

            validate: function () {
                return false;
            }
        });
    }
);

/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/product-edit-form/attributes/validation',
        'oro/mediator',
        'oro/messenger',
        'pim/field-manager',
        'pim/product-edit-form/attributes/validation-error',
        'pim/user-context'
    ],
    function ($, _, Backbone, BaseForm, mediator, messenger, FieldManager, ValidationError, UserContext) {
        return BaseForm.extend({
            /**
             * On field extension
             *
             * @param {Event} event
             */
            addFieldExtension: function (event) {
                var field = event.field;
                var valuesErrors = _.uniq(this.validationErrors.values, function (error) {
                    return JSON.stringify(error);
                });

                var errorsForAttribute = _.where(valuesErrors, {attributeCode: field.attribute.code});

                if (!_.isEmpty(errorsForAttribute)) {
                    this.addErrorsToField(field, errorsForAttribute);
                }
            }
        });
    }
);

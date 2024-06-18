/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';
/**
 * Concatenated Attribute Text field - display readOnlyValue
 */
define([
        'pim/field',
        'underscore',
        'pim/template/product/field/text'
    ], function (
    Field,
    _,
    fieldTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'change .field-input:first input[type="text"]': 'updateModel'
            },
            renderInput: function (context) {
                return this.fieldTemplate(_.extend(context, {
                    editMode: 'view'
                }));
            },
            updateModel: function () {
                var data = this.$('.field-input:first input[type="text"]').val();
                data = '' === data ? this.attribute.empty_value : data;

                this.setCurrentValue(data);
            }
        });
    }
);
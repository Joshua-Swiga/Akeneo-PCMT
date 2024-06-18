/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';
/**
 * Text field overwritten to add generate unique id button
 *
 */
define([
        'jquery',
        'pim/field',
        'underscore',
        'oro/translator',
        'pim/template/product/field/text',
        'pcmt/template/form/button/create-unique-id'
    ], function (
        $,
        Field,
        _,
        __,
        fieldTemplate,
        createButtonTemplate
    ) {
        return Field.extend({
            fieldTemplate: _.template(fieldTemplate),
            events: {
                'change .field-input:first input[type="text"]': 'updateModel',
                'click .generate-unique-id': 'generateUniqueId'
            },
            renderInput: function (context) {
                return this.fieldTemplate(context);
            },
            updateModel: function () {
                var data = this.$('.field-input:first input[type="text"]').val();
                data = '' === data ? this.attribute.empty_value : data;

                this.setCurrentValue(data);
            },
            generateUniqueId: function() {
                if (this.editable) {
                    $.ajax(
                        {
                            url: Routing.generate('pcmt_unique_id_generator'),
                            type: 'GET'
                        }
                    ).done(function(data) {
                         if (data.uniqueId) {
                             this.$('.field-input:first input[type="text"]').val(data.uniqueId);
                             this.setCurrentValue(data.uniqueId);
                         }
                    }.bind(this));
                }
            },
            renderElements: function () {
                if ('pim_catalog_identifier' === this.attribute.type && this.editable) {
                    var template = _.template(createButtonTemplate);
                    this.addElement(
                        'footer',
                        'generate-unique-id',
                        template({text: __('pcmt.form.button.create_unique_id')})
                    );
                }

                Field.prototype.renderElements.apply(this, arguments);
            },
        });
    }
);

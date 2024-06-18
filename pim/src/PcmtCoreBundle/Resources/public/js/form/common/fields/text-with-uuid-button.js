
/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define([
    'jquery',
    'underscore',
    'pim/form/common/fields/field',
    'pim/template/form/common/fields/text',
    'pcmt/template/form/common/fields/field-with-uuid-button'
],
function (
    $,
    _,
    BaseField,
    template,
    fieldTemplate
) {
    return BaseField.extend({
        containerTemplate: _.template(fieldTemplate),
        template: _.template(template),
        events: {
            'keyup input': function (event) {
                this.errors = [];
                this.updateModel(this.getFieldValue(event.target));
                // Text fields don't trigger form render because there is no case of dependency with other fields.
                // Also, the fact the form is rendered when the focus is lost causes issues with other events triggering
                // (e.g. click on another field or on a button).
            },
            'click .generate-unique-id': 'generateUniqueId'
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            return this.template(_.extend(templateContext, {
                value: this.getModelValue()
            }));
        },

        /**
         * {@inheritdoc}
         */
        getFieldValue: function (field) {
            return $(field).val();
        },

        generateUniqueId: function() {
            $.ajax(
                {
                    url: Routing.generate('pcmt_unique_id_generator'),
                    type: 'GET'
                }
            ).done(function(data) {
                if (data.uniqueId) {
                    this.$('input[type="text"]').val(data.uniqueId);
                    this.updateModel(data.uniqueId);
                }
            }.bind(this));
        },
    });
});

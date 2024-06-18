/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

define([
    'jquery',
    'underscore',
    'oro/translator',
    'pim/form',
    'pim/template/form/creation/field'
], function($, _, __, BaseForm, template) {

    return BaseForm.extend({
        template: _.template(template),
        dialog: null,
        events: {
            'change input': 'updateModel',
            'click .generate-unique-id': 'generateUniqueId'
        },

        generateUniqueId: function() {
            $.ajax(
                {
                    url: Routing.generate('pcmt_unique_id_generator'),
                    type: 'GET'
                }
            ).done(function(data) {
                if (data.uniqueId) {
                    this.$('.field-input:first input[type="text"]').val(data.uniqueId);
                    this.getFormModel().set(this.identifier, data.uniqueId);
                }
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        initialize: function(config) {
            this.config = config.config;
            console.log(config);
            this.identifier = this.config.identifier || 'code';
            this.config.create_button = this.config.create_button || false;

            BaseForm.prototype.initialize.apply(this, arguments);
        },

        /**
         * Model update callback
         */
        updateModel: function(event) {
            this.getFormModel().set(this.identifier, event.target.value || '');
        },

        /**
         * {@inheritdoc}
         */
        render: function() {
            if (!this.configured)
                this;

            this.$el.html(this.template(
                this.getRenderParams()
            ));

            this.delegateEvents();

            return this;
        },

        getRenderParams: function() {
            const errors = this.getRoot().validationErrors || [];
            return {
                identifier: this.identifier,
                label: __(this.config.label),
                requiredLabel: __('pim_common.required_label'),
                errors: errors.filter(error => {
                    const id = this.identifier;
                    const {path, attribute} = error;

                    return id === path || id === attribute;
                }),
                value: this.getFormData()[this.identifier],
                showCreateButton: this.config.create_button,
                createButtonLabel: __('pcmt.form.button.create_unique_id')
            }
        }
    });
});

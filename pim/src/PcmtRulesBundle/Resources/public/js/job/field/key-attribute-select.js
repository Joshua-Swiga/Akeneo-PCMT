/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * Attribute Select field for rules job
 */
define([
    'underscore',
    'oro/translator',
    'pcmt/rules/job/field/select',
    'pcmt/rules/template/job/field/multiple-select',
    'pim/common/property',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n'
], function (
    _,
    __,
    BaseField,
    fieldTemplate,
    propertyAccessor,
    FetcherRegistry,
    UserContext,
    i18n
) {
    return BaseField.extend({
        fieldTemplate: _.template(fieldTemplate),
        sourceFamily: '',
        destinationFamily: '',

        events: {
            "change select": "updateModelAfterChange",
        },

        sourceOptions: [],
        destinationOptions: [],
        keyAttribute: {
            sourceKeyAttribute: '',
            destinationKeyAttribute: ''
        },

        configure: function() {
            this.listenTo(this.getRoot(), this.postUpdateEventName, this.onUpdateField);
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.postFetch);

            BaseField.prototype.configure.apply(this, arguments);
        },
        postFetch: function(data) {
            this.keyAttribute =  this.getValue();
        },

        fetch: function() {
            if (this.sourceFamily && this.destinationFamily) {
                let options = {
                    sourceFamily: this.sourceFamily,
                    destinationFamily: this.destinationFamily
                };
                if (typeof this.config.types !== 'undefined') {
                    options.types = this.config.types;
                }
                if (typeof this.config.validationRule !== 'undefined') {
                    options.validationRule = this.config.validationRule;
                }
                FetcherRegistry.getFetcher(this.config.fetcher).fetchForOptions(options).then(
                    function (result) {
                        this.sourceOptions = result.sourceKeyAttributes;
                        this.destinationOptions = result.destinationKeyAttributes;
                        this.render();
                        this.updateState();
                    }.bind(this)
                );
            } else {
                this.sourceOptions = [];
                this.destinationOptions = [];
            }
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            return this.fieldTemplate(_.extend(templateContext, {
                sourceOptions: this.sourceOptions,
                sourceLabel: __('pcmt.rules.family_to_family_job.properties.key_attribute.label.source'),
                sourcePlaceholder: __('pcmt.rules.family_to_family_job.properties.key_attribute.placeholder.source'),
                sourceKeyAttribute: this.keyAttribute.sourceKeyAttribute,
                destinationOptions: this.destinationOptions,
                destinationLabel: __('pcmt.rules.family_to_family_job.properties.key_attribute.label.destination'),
                destinationPlaceholder: __('pcmt.rules.family_to_family_job.properties.key_attribute.placeholder.destination'),
                destinationKeyAttribute: this.keyAttribute.destinationKeyAttribute,
                i18n: i18n,
                locale: UserContext.get('catalogLocale')
            }));
        },

        onUpdateField: function() {
            const newSourceFamily = propertyAccessor.accessProperty(this.getFormData(), this.config.sourceFamily);
            const newDestinationFamily = propertyAccessor.accessProperty(this.getFormData(), this.config.destinationFamily);

            if (newSourceFamily !== this.sourceFamily) {
                this.sourceFamily = newSourceFamily;

                this.fetch();
            }

            if (newDestinationFamily !== this.destinationFamily) {
                this.destinationFamily = newDestinationFamily;

                this.fetch();
            }
        },

        updateModelAfterChange: function (event) {
            this.updateModelValue(event.target.name, event.target.value);
            this.render();
        },

        updateModelValue: function (type, value) {
            this.keyAttribute[type] = value;

            this.updateState();
        },

        /**
         * Update the model after dom update
         */
        updateState: function () {
            var data = propertyAccessor.updateProperty(this.getFormData(), this.getFieldCode(), this.getFieldValue());

            this.setData(data);
        },

        /**
         * {@inheritdoc}
         */
        getFieldValue: function () {
            return {
                sourceKeyAttribute: this.keyAttribute.sourceKeyAttribute,
                destinationKeyAttribute: this.keyAttribute.destinationKeyAttribute
            }
        }
    });
});

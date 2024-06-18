/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define([
    'jquery',
    'underscore',
    'pim/form',
    'pim/fetcher-registry',
    'oro/translator',
    'pim/template/form/properties/concat-attribute',
], function (
    $,
    _,
    BaseForm,
    FetcherRegistry,
    __,
    formTemplate
) {
    return BaseForm.extend({
        className: 'concatenated-attribute-container',
        events: {
            'change  .member-attribute': 'updateModel',
            'change .separator-field': 'updateModel'
        },
        template: _.template(formTemplate),
        attributes: [],
        attributeCount: 2,

        initialize: function (config) {
            this.config = config.config;
            BaseForm.prototype.initialize.apply(this, arguments);
        },

        configure: function() {
            this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.updateAttributeCount);
            this.listenTo(this.getRoot(), 'pcmt:attribute:members_changed', this.render);
            return $.when(
                this.getAttributes()
                    .then(function (attributes) {
                        this.attributes = attributes;
                    }.bind(this)),
                BaseForm.prototype.configure.apply(this, arguments)
            );

        },
        updateAttributeCount: function () {
            if (!this.checkIfLastAttributeEmpty()) {
                this.addBaseAttribute();
            } else {
                if (this.checkIfLastTwoAttributesAndSeparatorEmpty()) {
                    this.removeBaseAttribute();
                }
            }
        },
        removeBaseAttribute: function() {
            if (this.attributeCount > 2) {
                this.attributeCount--;
                this.getRoot().trigger('pcmt:attribute:members_changed');
                this.updateAttributeCount();
            }
        },
        addBaseAttribute: function() {
            this.attributeCount++;
            this.getRoot().trigger('pcmt:attribute:members_changed');
            this.updateAttributeCount();
        },
        checkIfLastAttributeEmpty: function() {
            let data = this.getFormData();
            return !data.concatenated['attribute' + this.attributeCount]
        },
        checkIfLastTwoAttributesAndSeparatorEmpty: function() {
            let data = this.getFormData();
            let lastElements = [
                'attribute' + this.attributeCount,
                'attribute' + (this.attributeCount - 1),
                'separator' + (this.attributeCount - 1)
            ];
            for (let i = 0; i < lastElements.length; i++) {
                if (data.concatenated[lastElements[i]]) {
                    return false;
                }
            }
            return true;
        },
        updateModel: function (event) {

            let data = this.getFormData();

            if(typeof data.concatenated !== 'object' || data.concatenated == null){
                data.concatenated = {}
            }

            data.concatenated[event.target.name] = this.getFieldValue(event.target);
            this.setData(data);

            this.updateAttributeCount();
        },

        render: function(templateContext) {
            this.$el.html(this.template({
                attributeCount: this.attributeCount,
                value: "",
                model: this.getFormData(),
                multiple: false,
                choices: this.formatChoices(this.attributes),
                readOnly: false,
                isReadOnly: false,
                labels: {
                    defaultChooseAttribute: __('pim_enrich.entity.attribute.property.concat_attribute_member_select.choose'),
                    defaultChooseSeparator: __('pim_enrich.entity.attribute.property.concat_attribute_separator_select.choose')
                }
            }));

            this.delegateEvents();
            this.renderExtensions();
            this.postRender();
        },

        postRender: function () {
            this.$('select.select2').select2({allowClear: true});
        },

        getAttributes: function () {
            return FetcherRegistry.getFetcher('concat-attribute').fetchAll({ options: { limit: 9999 } })
        },

        getFieldValue: function (field) {
            return $(field).val();
        },

        formatChoices: function (attributes) {
            return _.mapObject(attributes, function (attribute) {
                return attribute.code;
            })
        }
    })
});
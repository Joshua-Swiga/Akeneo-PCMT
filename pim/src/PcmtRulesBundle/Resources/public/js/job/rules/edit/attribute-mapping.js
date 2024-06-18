/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 *
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/job/common/edit/field/field',
        'pcmt/rules/template/job/rules/edit/attribute-mapping',
        'pcmt/rules/template/job/rules/edit/attribute-mapping-row',
        'pim/fetcher-registry',
        'pim/i18n',
        'pim/user-context',
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        rowTemplate,
        FetcherRegistry,
        i18n,
        UserContext
    ) {

        return BaseForm.extend({
            events: {
                "click .add-row": "addRow",
                "click .remove-row": "removeRow",
                "change select": "updateModelAfterChange",
            },
            template: _.template(template),
            rowTemplate: _.template(rowTemplate),
            sourceFamily: '',
            destinationFamily: '',
            sourceAttributeList: [],
            destinationAttributeList: [],
            mappingValues: [],
            initialize: function(config) {
                BaseForm.prototype.initialize.apply(this, arguments);
                if (undefined === this.config.fieldCode) {
                    throw new Error('This view must be configured with a field code.');
                }
            },
            configure: function() {
                BaseForm.prototype.configure.apply(this, arguments);
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_fetch', this.postFetch);
                this.listenTo(this.getRoot(), this.postUpdateEventName, this.postUpdate);
            },
            postFetch: function(data) {
                let value = this.getValue();
                if (_.isArray(value)) {
                    this.mappingValues = value;
                }
                if (_.isEmpty(this.mappingValues)) {
                    this.addRow();
                }
            },
            postUpdate: function(data) {
                var changed = false;
                if (data.configuration.sourceFamily !== this.sourceFamily) {
                    this.sourceFamily = data.configuration.sourceFamily;
                    changed = true;
                }
                if (data.configuration.destinationFamily !== this.destinationFamily) {
                    this.destinationFamily = data.configuration.destinationFamily;
                    changed = true;
                }
                if (changed) {
                    this.fetchAttributes();
                }

            },
            fetchAttributes: function() {
                let options = {
                    sourceFamily: this.sourceFamily,
                    destinationFamily: this.destinationFamily
                };
                FetcherRegistry.getFetcher('attributes-for-f2f-mapping').fetchForOptions(options).then(
                    function (result) {
                        this.sourceAttributeList = result.sourceAttributeList;
                        this.destinationAttributeList = result.destinationAttributeList;
                        this.render();
                    }.bind(this)
                );
            },
            addRow: function() {
                let index = this.getMaxIndex() + 1;
                this.mappingValues.push({
                    index: index,
                    sourceValue: '',
                    destinationValue: '',
                });
                this.updateState();
                this.render();
            },
            removeRow: function(ev) {
                var chosenIndex = $(ev.currentTarget).data('index');
                this.mappingValues = _.filter(this.mappingValues, function(element) {
                    return element.index !== chosenIndex;
                });
                this.updateState();
                this.render();
            },
            render: function () {
                let rows = [];
                _.each(this.mappingValues, function(element) {
                    rows.push(this.renderRow(element));
                }.bind(this));

                this.$el.html(this.template({
                    rows: rows,
                    error: this.getParent().getValidationErrorsForField(this.getFieldCode()),
                    sourceLabel: __('pcmt.rules.attribute_mapping.source'),
                    destinationLabel: __('pcmt.rules.attribute_mapping.destination')
                }));
                this.$('.select2').select2();
                this.delegateEvents();
                return this;
            },
            renderRow: function(element) {
                let sourceAttribute = this.getSourceAttribute(element.sourceValue);
                // show only those matching the type of source attribute
                let destinationAttributeList = sourceAttribute ?
                    _.filter(this.destinationAttributeList, function(attr) {
                        let types = [
                            'pim_catalog_text',
                            'pim_catalog_simpleselect'
                        ];
                        if (types.indexOf(sourceAttribute.type) !== -1 && types.indexOf(attr.type) !== -1) {
                            return true;
                        }
                        return attr.type === sourceAttribute.type;
                    }) :
                    [];

                return this.rowTemplate({
                    element: element,
                    sourceAttributeList: this.sourceAttributeList,
                    destinationAttributeList: destinationAttributeList,
                    i18n: i18n,
                    locale: UserContext.get('catalogLocale'),
                });
            },
            getSourceAttribute: function(code) {
                return _.find(this.sourceAttributeList, function(attr) {
                    return attr.code === code;
                });
            },
            updateModelAfterChange: function (event) {
                this.updateModelValue(parseInt(event.target.dataset.index), event.target.name, event.target.value);
                this.render();
            },
            updateModelValue: function (index, type, value) {
                let mappingValue = _.find(this.mappingValues, function(element) {
                    return element.index === index;
                });
                if (!mappingValue) {
                    return;
                }
                mappingValue[type] = value;
                this.updateState();
            },
            getMaxIndex: function() {
                if (_.isEmpty(this.mappingValues)) {
                    return 0;
                }
                let maxElement = _.max(this.mappingValues, function (element) {
                    return element.index;
                });
                return maxElement.index;

            },
            getFieldValue: function () {
                return this.mappingValues;
            },
        });
    }
);
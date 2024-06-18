/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/common/breadcrumbs',
        'pim/template/common/breadcrumbs',
        'oro/mediator',
        'pim/form-registry',
        'pim/common/property'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        mediator,
        FormRegistry,
        propertyAccessor
    ) {
        return BaseForm.extend({
            getFormData: function () {
                return this.getRoot().model.toJSON().product;
            },
            /**
             * {@inheritdoc}
             */
            render: function () {
                return $.when(
                    FormRegistry.getFormMeta(this.config.tab),
                    FormRegistry.getFormMeta(this.config.item)
                ).then(function (metaTab, metaItem) {
                    var breadcrumbTab = { code: this.config.tab, label: __(metaTab.config.title) };
                    var breadcrumbItem = null;

                    if (0 === this.getFormData().meta.id) {
                        const label = this.config.defaultLabel;
                        breadcrumbItem = { code: __(label), label: __(label), active: false };
                    } else if (undefined !== metaItem) {
                        breadcrumbItem = { code: this.config.item, label: __(metaItem.config.title), active: true };
                    } else if (undefined !== this.config.itemPath &&
                        null !== propertyAccessor.accessProperty(this.getFormData(), this.config.itemPath)
                    ) {
                        const item = propertyAccessor.accessProperty(this.getFormData(), this.config.itemPath);

                        breadcrumbItem = { code: item, label: item, active: false };
                    }

                    this.$el.empty().append(this.template({
                        breadcrumbTab: breadcrumbTab,
                        breadcrumbItem: breadcrumbItem
                    }));

                    this.delegateEvents();
                }.bind(this));

                return this;
            }
        });
    });

/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * Display navigation links in column for the tab display
 *
 * Even if this module has the same design than `navigation-block`, it does not works like it, because this module is
 * not composed of extensions, but listen to the product edit form events to register its own tabs.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'pcmt/product/template/form/column-tabs-navigation',
        'pim/router',
        'routing'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        Router,
        Routing
    ) {
        return BaseForm.extend({
            className: 'AknColumn-block',
            template: _.template(template),
            tabs: [],
            draftTabs: [],
            currentTab: null,
            events: {
                'click .column-navigation-link': 'selectTab'
            },
            currentKey: 'current_column_tab',

            /**
             * @param {string} meta.config.title Translation key of the block title
             *
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.tabs = [];
                this.draftTabs = [];

                this.currentTab = sessionStorage.getItem(this.currentKey);

                this.listenTo(this.getRoot(), 'column-tab:register', this.registerTab);
                this.listenTo(this.getRoot(), 'column-tab:select-tab', this.setCurrentTab);
                this.listenTo(this.getRoot(), this.postUpdateEventName, this.setDraftId);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            setDraftId: function () {
                this.draftId = undefined !== this.getFormData().draftId ? this.getFormData().draftId : 0;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el
                    .empty()
                    .html(this.template({
                        tabs: this.getTabs(),
                        draftTabs: this.getDraftTabs(),
                        currentTab: this.getCurrentTabOrDefault(),
                        productNavigationTitle: __(this.config.productNavigationTitle),
                        productDraftNavigationTitle: __(this.config.productDraftNavigationTitle)
                    }));
            },

            /**
             * Registers a new tab
             *
             * @param event
             */
            registerTab: function (event) {
                var tab = {
                    code: event.code,
                    isVisible: event.isVisible,
                    label: event.label,
                    route: event.code
                };

                if (event.isForDraft) {
                    this.draftTabs.push(tab);
                } else {
                    this.tabs.push(tab);
                }

                this.trigger('pim_menu:column:register_navigation_item', tab);

                this.render();
            },

            /**
             * Displays another tab
             *
             * @param event
             */
            selectTab: function (event) {
                this.getRoot().trigger('column-tab:select-tab', event);
                this.setCurrentTab(event.currentTarget.dataset.tab);
                this.render();
            },

            /**
             * Set the current tab
             *
             * @param {string} tabCode
             */
            setCurrentTab: function (tabCode) {
                if (this.config.noDraftTab == tabCode) {
                    if (this.draftId) {
                        Router.navigate('/' + Routing.generate(this.config.redirect, {id: this.draftId}), true);
                    }
                }

                this.currentTab = tabCode;
            },

            /**
             * Returns the current tab.
             * If there is no selected tab, returns the first available tab.
             */
            getCurrentTabOrDefault: function () {
                var result = _.findWhere(this.getTabs().concat(this.getDraftTabs()), {code: this.currentTab});

                return (undefined !== result) ? result.code : _.first(_.pluck(this.tabs, 'code'));
            },

            /**
             * Returns the list of visible tabs
             */
            getTabs: function () {
                return _.filter(this.tabs, function (tab) {
                    return !_.isFunction(tab.isVisible) || tab.isVisible();
                });
            },

            getDraftTabs: function () {
                return _.filter(this.draftTabs, function (tab) {
                    return !_.isFunction(tab.isVisible) || tab.isVisible();
                });
            }
        });
    }
);

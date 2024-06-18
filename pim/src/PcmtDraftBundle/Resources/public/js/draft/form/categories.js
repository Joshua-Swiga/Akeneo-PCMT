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
        'oro/translator',
        'backbone',
        'pim/product-edit-form/categories',
        'pim/template/product/tab/categories',
        'pim/template/product/tab/catalog-switcher',
        'pim/template/product/tab/jstree-locked-item',
        'pim/user-context',
        'routing',
        'pim/tree/associate',
        'oro/mediator'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        formTemplate,
        switcherTemplate,
        lockedTemplate,
        UserContext,
        Routing,
        TreeAssociate,
        mediator
    ) {
        return BaseForm.extend({
            /**
             * {@inheritdoc}
             */
            getFormData: function () {
                return this.getRoot().model.toJSON().product;
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.loadTrees().done(function (trees) {
                    this.trees = trees;

                    if (undefined === this.state.toJSON().currentTree) {
                        this.state.set('currentTree', _.first(this.trees).code);
                        this.state.set('currentTreeId', _.first(this.trees).id);
                    }

                    this.$el.html(
                        this.template({
                            product: this.getFormData(),
                            locale: UserContext.get('catalogLocale'),
                            state: this.state.toJSON(),
                            trees: this.trees
                        })
                    );

                    this.treeAssociate = new TreeAssociate('#trees', '#hidden-tree-input', {
                        list_categories: this.config.itemCategoryListRoute,
                        children:        'pim_enrich_categorytree_children'
                    });

                    this.delegateEvents();

                    this.onLoadedEvent = this.lockCategories.bind(this);
                    mediator.on('jstree:loaded', this.onLoadedEvent);

                    this.initCategoryCount();
                    this.renderCategorySwitcher();
                }.bind(this));

                return this;
            },
        });
    }
);

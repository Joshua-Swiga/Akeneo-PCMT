/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';
/**
 * Category tab extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'pim/product-edit-form/categories',
        'pim/user-context',
        'pim/tree/associate',
        'oro/mediator'
    ],
    function (
        $,
        _,
        BaseForm,
        UserContext,
        TreeAssociate,
        mediator
    ) {
        return BaseForm.extend({
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

                    this.treeAssociate.lock();

                    this.delegateEvents();

                    this.onLoadedEvent = this.lockCategories.bind(this);
                    mediator.on('jstree:loaded', this.onLoadedEvent);

                    this.initCategoryCount();
                    this.renderCategorySwitcher();
                }.bind(this));

                return this;
            }
        });
    }
);

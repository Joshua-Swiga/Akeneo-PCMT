/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * This extension will display the mass actions panel with all the actions available for checked items in a grid.
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'backbone',
        'pim/grid/mass-actions',
        'pcmt/draft/template/mass-actions'
    ],
    function (
        $,
        _,
        __,
        Backbone,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),
            className: 'AknDefault-bottomPanel AknDefault-bottomPanel--hidden AknMassActions mass-actions',
            collection: null,
            count: 0,
            events: {
                'click .select-all': 'selectAll',
                'click .select-none': 'selectNone',
                'click .select-visible': 'selectVisible',
                'click .select-button': 'toggleButton'
            },

            /**
             * {@inheritdoc}
             */
            configure() {
                const setCollection = (collection) => {
                    if (null === this.collection) {
                        this.listenTo(this.getRoot(), 'pcmt:drafts:select', this.select.bind(this));
                        this.listenTo(this.getRoot(), 'pcmt:drafts:approved', this.selectNone.bind(this));
                        this.listenTo(this.getRoot(), 'pcmt:drafts:rejected', this.selectNone.bind(this));
                    }

                    this.collection = collection;
                    this.updateView();
                };
                this.listenTo(this.getRoot(), 'pcmt:drafts:listReloaded', setCollection);

                BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * Updates the count after clicking in a single event
             *
             * @param {Object}  draftId The selected model
             * @param {boolean} checked
             */
            select(draftId, checked) {
                if (checked) {
                    this.count = Math.min(this.count + 1, this.collection.state.totalRecords);
                } else {
                    this.count = Math.max(this.count - 1, 0);
                }

                this.updateView();
            },

            /**
             * Updates the count after clicking in "Select all" button
             */
            selectAll() {
                this.count = this.collection.state.totalRecords;
                this.getRoot().trigger('pcmt:drafts:selectAll');

                this.updateView();
            },

            /**
             * Updates the count after clicking in "Select all visible" button
             */
            selectVisible() {
                this.count = 0;

                this.getRoot().trigger('pcmt:drafts:selectVisible');

                this.updateView();
            },

            /**
             * Updates the count after clicking in "Select none" button
             */
            selectNone() {
                this.count = 0;
                this.getRoot().trigger('pcmt:drafts:selectNone');

                this.updateView();
            }
        });
    }
);

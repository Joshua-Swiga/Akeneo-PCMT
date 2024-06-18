/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'pim/form',
        'backbone',
        'jquery',
        'underscore',
        'oro/translator',
        'routing',
        'pcmt/product/template/draft-pagination'
    ],
    function (BaseForm, Backbone, $, _, __, Routing, template) {
        return BaseForm.extend({
            template: _.template(template),
            collection: null,
            handles: [],
            events: {
                "click a": "onChangePage"
            },

            configure: function () {
                this.listenTo(this.getRoot(), 'pcmt:drafts:listReloaded', this.setCollection);
            },

            setCollection: function (collection) {
                this.collection = collection;

                this.updateView();
            },

            getPaginationHandles: function (state) {
                this.handles = [];

                if (!state || 0 === state.lastPage) {
                    return;
                }

                const diff = (state.currentPage === state.firstPage || state.currentPage === state.lastPage) ? 2 : 1;

                for (let page = state.firstPage; page <= state.lastPage; page++) {
                    if (page === state.firstPage || page === state.lastPage) {
                        this.addHandle(page, page, state.currentPage === page);
                    } else if (page < state.currentPage - diff) {
                        this.addHandle(null, '...');
                        page = state.currentPage - diff - 1;
                    } else if (page <= state.currentPage + diff) {
                        this.addHandle(page, page, state.currentPage === page);
                    } else if (page > state.currentPage + diff) {
                        this.addHandle(null, '...');
                        page = state.lastPage - 1;
                    }
                }
            },

            updateView: function () {
                this.$el.empty();

                if (this.collection.state.totalPages <= 1) {
                    return;
                }

                this.getPaginationHandles(this.collection.state);

                this.$el.addClass('AknGridToolbar-center');
                this.$el.append(this.template({
                    handles: this.handles,
                }));
            },

            addHandle: function (pageNo, pageText, isCurrent = false) {
                let className = null;

                if (isCurrent) {
                    className = 'active AknActionButton--highlight';
                }

                if (!pageNo) {
                    className = 'AknActionButton--unclickable';
                }

                this.handles.push({
                    pageNo: pageNo,
                    pageText: pageText,
                    className: className
                });
            },

            onChangePage: function (e) {
                const page = parseInt($(e.currentTarget).data('page'));

                if (page !== this.collection.state.currentPage) {
                    this.getRoot().trigger('pcmt:drafts:pageChanged', page);
                }
            }
        });
    }
);


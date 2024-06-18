/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
'use strict';

define(
    [
        'underscore',
        'oro/pageable-collection'
    ],
    function (
        _,
        PageableCollection
    ) {
        return PageableCollection.extend({
            /**
             * @inheritDoc
             */
            initialize: function(models, options) {
                PageableCollection.prototype.initialize.apply(this, arguments);

                _.extend(this.queryParams, {
                    currentPage: 'page',
                    pageSize:    null
                });
            },

            parse: function (resp) {
                this.state.totalRecords = 'undefined' !== typeof(resp.params.total) ? resp.params.total : resp.options.totalRecords;
                if ('undefined' !== typeof(resp.params.pageSize)) {
                    this.state.pageSize = resp.params.pageSize;
                }

                this.state = this._checkState(this.state);

                return resp.objects;
            }
        })
    }
);

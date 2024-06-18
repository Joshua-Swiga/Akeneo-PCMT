/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'jquery',
        'backbone',
        'pim/base-fetcher',
        'routing'
    ],
    function (
        $,
        Backbone,
        BaseFetcher,
        Routing
    ) {
        return BaseFetcher.extend({
            /**
             * Fetch all elements of the collection
             *
             * @return {Promise}
             */
            fetchAll: function (searchOptions) {
                if (!this.entityListPromise) {
                    if (!_.has(this.options.urls, 'list')) {
                        return $.Deferred().reject().promise();
                    }

                    this.entityListPromise = $.getJSON(
                        Routing.generate(this.options.urls.list, searchOptions),
                    ).then(_.identity).promise();
                }

                return this.entityListPromise;
            },
        });
    }
);
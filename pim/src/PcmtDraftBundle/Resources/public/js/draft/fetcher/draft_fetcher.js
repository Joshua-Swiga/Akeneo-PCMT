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
             * Fetch an element based on its identifier
             *
             * @param {int} id
             *
             * @return {Promise}
             */
            fetch: function (id) {
                return $.ajax({
                    url: Routing.generate('pcmt_core_draft_api', {id: id}),
                    type: 'GET'
                })
                    .then(function (draft) {
                        return draft;
                    })
                    .promise();
            },

            /**
             * {@inheritdoc}
             */
            getIdentifierField: function () {
                return $.Deferred().resolve('id');
            }
        });
    }
);
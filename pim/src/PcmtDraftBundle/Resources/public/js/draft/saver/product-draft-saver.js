/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define([
        'underscore',
        'pim/saver/base',
        'routing'
    ], function (
    _,
    BaseSaver,
    Routing
    ) {
        return _.extend({}, BaseSaver, {
            /**
             * {@inheritdoc}
             */
            getUrl: function (id) {
                return Routing.generate(__moduleConfig.url, {id: id});
            }
        });
    }
);
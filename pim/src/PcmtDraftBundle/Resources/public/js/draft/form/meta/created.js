/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'underscore',
        'pcmt/draft/common/created',
        'pim/template/product/meta/created'
    ],
    function (_, Created, template) {
        return Created.extend({
            className: 'AknColumn-block',

            template: _.template(template)
        });
    }
);

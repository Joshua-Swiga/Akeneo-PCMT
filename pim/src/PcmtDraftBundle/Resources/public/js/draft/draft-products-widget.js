/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/router',
        'pim/dashboard/abstract-widget',
        'pcmt/product/template/widget/draft-products-widget',
        'pcmt/product/template/widget/view-all-btn'
    ],
    function ($, _, __, router, AbstractWidget, template, viewAllBtnTemplate) {
        'use strict';

        return AbstractWidget.extend({
            id: 'draft_products_overview',
            template: _.template(template),

            _processResponse: function(data) {
                if(_.isEmpty(data)){
                    return [];
                }
                return data;
            }
        });
    }
);

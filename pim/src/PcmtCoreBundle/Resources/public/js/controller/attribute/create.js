/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

// Help while creating new Attribute - remember choice when switch tab
define([
        'pim/controller/attribute/create'
    ],
    function (BaseController) {
        return BaseController.extend({
            /**
             * {@inheritdoc}
             *
             * Override to add descriptions default value when creating an attribute
             */
            getNewAttribute: function (type) {
                var attribute = BaseController.prototype.getNewAttribute.apply(this, arguments);

                if ('pim_assets_collection' === type) {
                    attribute.reference_data_name = 'assets';
                }

                attribute.descriptions = {};

                return attribute;
            }
        });
    });


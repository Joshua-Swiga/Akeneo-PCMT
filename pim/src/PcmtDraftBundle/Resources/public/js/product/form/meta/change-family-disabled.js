/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'pim/form',
        'pcmt/product/template/meta/change-family-disabled',
    ],
    function (
        BaseForm,
        innerModalTemplate
    ) {
        return BaseForm.extend({
            className: 'AknColumn-blockDown change-family',
            innerModalTemplate: _.template(innerModalTemplate),

            render: function () {
                return false;
            }

        });
    }
);

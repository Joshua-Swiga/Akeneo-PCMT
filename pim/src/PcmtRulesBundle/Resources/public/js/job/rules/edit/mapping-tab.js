/*
 * Copyright (c) 2021, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'pim/job/common/edit/properties',
        'pcmt/rules/template/job/rules/edit/mapping-tab',
    ],
    function (
        BaseForm,
        template
    ) {

        return BaseForm.extend({
            template: _.template(template),
        });
    }
);
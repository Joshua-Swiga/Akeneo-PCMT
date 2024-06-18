/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';
define(['pim/controller/front', 'pim/form-builder'],
    function (BaseController, FormBuilder) {
        return BaseController.extend({
            renderForm: function (route) {

                return FormBuilder.build('pcmt-product-drafts-index').then((form) => {
                      form.setData({draftsData: {params: {currentPage : 1}}});
                      form.setElement(this.$el).render();
                });
            }
        });
    }
);
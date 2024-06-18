/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'pim/controller/front',
        'pim/form-builder',
        'pim/fetcher-registry'
    ],
    function (BaseController, FormBuilder, FetcherRegistry) {
        return BaseController.extend({
            renderForm: function (route) {
                return FetcherRegistry.getFetcher(this.options.config.entity).fetch(route.params.id, {cached: false})
                    .then((draft) => {
                        return FormBuilder.build(draft.product.meta.form)
                            .then((form) => {
                                form.setData(draft);
                                form.setElement(this.$el).render();

                                return form;
                            });
                    })
            },
        })
    }
);
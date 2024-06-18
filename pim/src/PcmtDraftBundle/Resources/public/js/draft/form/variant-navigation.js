/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'pim/router',
        'pim/product-edit-form/variant-navigation'
    ],
    function (
        router,
        BaseForm
    ) {
        return BaseForm.extend({
            /**
             * {@inheritdoc}
             */
            getFormData: function () {
                return this.getRoot().model.toJSON().product;
            },

            /**
             * Redirect the user to the given entity edit page
             *
             * @param {Object} entity
             */
            redirectToEntity: function (entity) {
                if (!entity) {
                    return;
                }

                let params = {};
                let route = '';

                if ('draft' === entity.model_type) {
                    route = 'pcmt_core_drafts_index';
                } else {
                    params = {id: entity.id};
                    route = ('product_model' === entity.model_type)
                        ? 'pim_enrich_product_model_edit'
                        : 'pim_enrich_product_edit'
                    ;
                }

                router.redirectToRoute(route, params);
            }
        });
    }
);

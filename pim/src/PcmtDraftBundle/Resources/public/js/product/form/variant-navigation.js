/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * Extension to display the variant navigation to browse variant product structure (parents and children)
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/router',
        'pim/product-edit-form/variant-navigation',
        'oro/messenger',
        'oro/translator'
    ],
    function (
        router,
        BaseForm,
        messenger,
        __
    ) {
        return BaseForm.extend({
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
            },

            /**
             * Action made when user submit the modal.
             *
             * @param {boolean} isVariantProduct
             * @param {Object} formModal
             */
            submitForm: function (isVariantProduct, formModal) {
                const message = isVariantProduct
                    ? __('pcmt.entity.draft.flash.create.variant_product_added')
                    : __('pcmt.entity.draft.flash.create.product_model_added');

                const route = isVariantProduct
                    ? 'pim_enrich_product_rest_create'
                    : 'pim_enrich_product_model_rest_create';

                return formModal
                    .saveProductModelChild(route)
                    .done((entity) => {
                        this.redirectToEntity(entity.meta);
                        messenger.notify('success', message);
                    });
            }
        });
    }
);

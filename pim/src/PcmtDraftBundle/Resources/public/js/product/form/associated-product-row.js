/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
define([
    'underscore',
    'jquery',
    'pim/product-edit-form/associated-product-row/original',
    'pim/router'
], function (
    _,
    $,
    BaseRow,
    Router
) {
    return BaseRow.extend({
        canRemoveAssociation: function () {
            return false;
        },

        /**
         * {@inheritdoc}
         */
        render() {
            BaseRow.prototype.render.call(this, arguments);

            const row = this.renderedRow;
            const id = this.getEntityId();

            row.on('click', () => Router.redirectToRoute(this.isProductModel() ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit', {id: id}));
        },

        getEntityId() {
            return parseInt(
                this.isProductModel()
                    ? this.model.get('id').split('-')[2]
                    : this.model.get('id').split('-')[1]
            );
        }
    });
});

/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
define([
    'underscore',
    'jquery',
    'pim/product-edit-form/associated-product-row/original',
    'oro/mediator',
    'pim/router'
], function (
    _,
    $,
    BaseRow,
    mediator,
    Router
) {
    return BaseRow.extend({

        render() {
            BaseRow.prototype.render.call(this, arguments);

            const row = this.renderedRow;
            const id = this.getEntityId();

            row.on('click', () => Router.redirectToRoute(this.isProductModel() ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit', {id: id}));

            $('.AknIconButton--remove', row).on('click', () => {
                mediator.trigger('datagrid:unselectModel:association-product-draft-grid', this.model);
                mediator.trigger('datagrid:unselectModel:association-product-model-draft-grid', this.model);
                row.remove();
            });
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

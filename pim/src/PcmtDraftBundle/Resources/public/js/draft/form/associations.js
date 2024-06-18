/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define([
    'pim/product-edit-form/associations',
    'jquery',
    'underscore',
    'oro/translator',
    'backbone',
    'pim/template/product/tab/associations',
    'pim/template/product/tab/association-panes',
    'pcmt/draft/template/product/form/modal-associations-add',
    'pim/fetcher-registry',
    'pim/attribute-manager',
    'pim/user-context',
    'routing',
    'oro/mediator',
    'oro/datagrid-builder',
    'oro/pageable-collection',
    'pim/datagrid/state',
    'require-context',
    'pim/form-builder',
    'pim/security-context'
], function(
    BaseForm,
    $,
    _,
    __,
    Backbone,
    formTemplate,
    panesTemplate,
    modalTemplate,
    FetcherRegistry,
    AttributeManager,
    UserContext,
    Routing,
    mediator,
    datagridBuilder,
    PageableCollection,
    DatagridState,
    requireContext,
    FormBuilder,
    securityContext
) {
    return BaseForm.extend({
        modalTemplate: _.template(modalTemplate),
        /**
         * {@inheritdoc}
         */
        getFormData: function () {
            return this.getRoot().model.toJSON().product;
        },

        addAssociations: function() {
            this.launchProductPicker().then(result => {

                let productAndProductModelIdentifiers = result.products;
                let isBiDirectional = result.isBiDirectional;

                let productIds = [];
                let productModelIds = [];
                productAndProductModelIdentifiers.forEach(item => {
                    const matchProductModel = item.match(/^product_model_(.*)$/);
                    if (matchProductModel) {
                        productModelIds.push(matchProductModel[1]);
                    } else {
                        const matchProduct = item.match(/^product_(.*)$/);
                        productIds.push(matchProduct[1]);
                    }
                });

                const assocType = this.getCurrentAssociationType();
                const previousProductIds = this.getFormData().associations[assocType].products;
                const previousProductModelIds = this.getFormData().associations[assocType].product_models;

                this.updateFormDataAssociations(previousProductIds.concat(productIds), assocType, 'products');
                this.updateFormDataAssociations(previousProductModelIds.concat(productModelIds), assocType, 'product_models');

                if (isBiDirectional) {
                    this.updateFormDataAssociations(productIds, assocType, 'products_bi_directional');
                    this.updateFormDataAssociations(productModelIds, assocType, 'product_models_bi_directional');
                }

                this.getRoot().trigger('pim_enrich:form:update-association');
            });
        },

        launchProductPicker: function() {
            const deferred = $.Deferred();

            FormBuilder.build('pim-associations-product-picker-form').then(form => {
                FetcherRegistry.getFetcher('association-type')
                    .fetch(this.getCurrentAssociationType())
                    .then(associationType => {

                        let modal = new Backbone.BootstrapModal({
                            modalOptions: {
                                backdrop: 'static',
                                keyboard: false,
                            },
                            okCloses: false,
                            title: __('pim_enrich.entity.product.module.associations.manage', {
                                associationType: associationType.labels[UserContext.get('catalogLocale')],
                            }),
                            innerDescription: __('pim_enrich.entity.product.module.associations.manage_description'),
                            content: '',
                            okText: __('pim_common.confirm'),
                            template: this.modalTemplate,
                            innerClassName: 'AknFullPage--full',
                        });

                        modal.open();
                        form.setElement(modal.$('.modal-body')).render();

                        modal.on('cancel', deferred.reject);
                        modal.on('ok', () => {
                            const products = form.getItems().sort((a, b) => {
                                return a.code < b.code;
                            });
                            modal.close();

                            deferred.resolve({
                                products: products,
                                isBiDirectional : modal.$el.find('#bi-directional-checkbox').is(":checked")
                            });
                        });
                    });
            });

            return deferred.promise();
        },
    });
});

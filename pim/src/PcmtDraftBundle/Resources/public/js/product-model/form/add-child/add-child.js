/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * Modal to create a product model child.
 *
 * @author    Damien Carcel <damien.carcel@akeneo.com>
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form/common/creation/modal',
        'routing',
        'pim/template/product-model-edit-form/add-child-form',
        'oro/loading-mask',
        'pim/router',
        'oro/messenger'
    ], (
        $,
        _,
        __,
        BaseForm,
        Routing,
        template,
        LoadingMask,
        router,
        messenger
    ) => {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render() {
                const illustrationClass = this.getIllustrationClass();
                this.$el.html(this.template({
                    illustrationClass,
                    okText: __('pim_common.save'),
                }));
                this.renderExtensions();
            },

            /**
             * Get the correct illustration class for products or product models
             *
             * @return {String}
             */
            getIllustrationClass() {
                const formData = this.getFormData();
                const hasFamilyVariant = formData.hasOwnProperty('family_variant');

                return hasFamilyVariant ? 'product-model' : 'products';
            },

            confirmModal(modal, deferred) {
                this.save().done(() => {
                    modal.close();
                    modal.remove();
                    deferred.resolve();

                    messenger.notify('success', __(this.config.successMessage));

                    router.redirectToRoute(
                        this.config.editRoute
                    );
                });
            },

            /**
             * Save the form content by posting it to backend
             *
             * @return {Promise}
             */
            save() {
                this.validationErrors = {};

                const loadingMask = new LoadingMask();
                this.$el.empty().append(loadingMask.render().$el.show());

                let data = $.extend(this.getFormData(),
                    this.config.defaultValues || {});

                if (this.config.excludedProperties) {
                    data = _.omit(data, this.config.excludedProperties)
                }

                return $.ajax({
                    url: Routing.generate(this.config.postUrl, {id: data.draftId}),
                    type: 'POST',
                    data: JSON.stringify(data)
                }).fail(function (response) {
                    if (response.responseJSON) {
                        this.getRoot().trigger(
                            'pim_enrich:form:entity:bad_request',
                            {'sentData': this.getFormData(), 'response': response.responseJSON.values}
                        );
                    }

                    this.validationErrors = response.responseJSON ?
                        this.normalize(response.responseJSON) : [{
                            message: __('pim_enrich.entity.fallback.generic_error')
                        }];
                    this.render();
                }.bind(this))
                    .always(() => loadingMask.remove());
            }
        });
    }
);

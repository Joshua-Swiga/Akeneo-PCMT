/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'routing',
        'pim/form/common/creation/modal',
        'oro/loading-mask',
        'pim/router',
        'oro/messenger'
    ],
    function (
        $,
        _,
        __,
        Routing,
        BaseForm,
        LoadingMask,
        router,
        messenger
    ) {
        return BaseForm.extend({
            /**
             * Confirm the modal and redirect to route after save
             * @param  {Object} modal    The backbone view for the modal
             * @param  {Promise} deferred Promise to resolve
             */
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



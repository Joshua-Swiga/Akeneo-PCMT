/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/product-edit-form/save',
        'oro/messenger',
        'pim/saver/product',
        'pim/field-manager',
        'pim/i18n',
        'pim/user-context',
        'pim/router'
    ],
    function (
        $,
        _,
        __,
        BaseSave,
        messenger,
        ProductSaver,
        FieldManager,
        i18n,
        UserContext,
        Router
    ) {
        return BaseSave.extend({
            updateSuccessMessage: __('pim_enrich.entity.product.flash.update.success'),
            updateFailureMessage: __('pim_enrich.entity.product.flash.update.fail'),
            editAsADraftLabel: __('pcmt_core.editing.button.edit_as_a_draft'),
            editExistingDraftLabel: __('pcmt_core.editing.button.edit_existing_draft'),

            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:change-family:after', this.save);
                this.listenTo(this.getRoot(), 'pim_enrich:form:update-association', this.save);

                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.changeSaveButton);
            },

            changeSaveButton: function (product) {
                const draftId = product.draftId;

                if (!product.permissionToEdit) {
                    return;
                }
                if (draftId) {
                    this.trigger('save-buttons:register-button', {
                        className: 'save',
                        priority: 200,
                        label: this.editExistingDraftLabel,
                        events: {
                            'click .save': this.navigateToDraft.bind(this)
                        }
                    });
                } else {
                    this.trigger('save-buttons:register-button', {
                        className: 'save',
                        priority: 200,
                        label: this.editAsADraftLabel,
                        events: {
                            'click .save': this.save.bind(this)
                        }
                    });
                }
            },

            navigateToDraft: function (options) {
                Router.redirectToRoute(
                    this.config.redirect,
                    {id: this.getFormData().draftId}
                );
            },

            /**
             * {@inheritdoc}
             */
            save: function (options) {
                var product = $.extend(true, {}, this.getFormData());
                var productId = product.meta.id;

                delete product.meta;
                delete product.draftId;

                var notReadyFields = FieldManager.getNotReadyFields();

                if (0 < notReadyFields.length) {
                    var fieldLabels = _.map(notReadyFields, function (field) {
                        return i18n.getLabel(
                            field.attribute.label,
                            UserContext.get('catalogLocale'),
                            field.attribute.code
                        );
                    });

                    messenger.notify(
                        'error',
                        __('pim_enrich.entity.product.flash.update.fields_not_ready', {
                            'fields': fieldLabels.join(', ')
                        })
                    );

                    return;
                }

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');

                return ProductSaver
                    .save(productId, product)
                    .then(function (data) {
                        Router.redirectToRoute(
                            this.config.redirect,
                            {id: data.id}
                        );
                    }.bind(this))
                    .fail(this.fail.bind(this))
                    .always(this.hideLoadingMask.bind(this));
            }
        });
    }
);

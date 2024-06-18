/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form/common/save',
        'oro/messenger',
        'pim/saver/product-model',
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
        ProductModelSaver,
        FieldManager,
        i18n,
        UserContext,
        Router
    ) {
        return BaseSave.extend({
            updateSuccessMessage: __('pim_enrich.entity.product_model.flash.update.success'),
            updateFailureMessage: __('pim_enrich.entity.product_model.flash.update.fail'),
            editAsADraftLabel: __('pcmt_core.editing.button.edit_as_a_draft'),
            editExistingDraftLabel: __('pcmt_core.editing.button.edit_existing_draft'),

            configure: function () {
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
                var productModel = $.extend(true, {}, this.getFormData());
                var productModelId = productModel.meta.id;

                delete productModel.meta;
                delete productModel.family;
                delete productModel.draftId;

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
                        __('pim_enrich.entity.product_model.flash.update.fields_not_ready', {
                            'fields': fieldLabels.join(', ')
                        })
                    );

                    return;
                }

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');

                return ProductModelSaver
                    .save(productModelId, productModel)
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

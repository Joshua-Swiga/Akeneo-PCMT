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
        'pim/form/common/save',
        'oro/messenger',
        'pcmt/product-draft-saver',
        'pim/field-manager',
        'pim/i18n',
        'pim/user-context'
    ],
    function (
        $,
        _,
        __,
        BaseSave,
        messenger,
        ProductDraftSaver,
        FieldManager,
        i18n,
        UserContext
    ) {
        return BaseSave.extend({
            updateSuccessMessage: __('pcmt.entity.draft.flash.update.success'),
            updateFailureMessage: __('pcmt.entity.draft.flash.update.fail'),
            label: __('pcmt_core.drafts_editing.button.save_draft'),

            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:change-family:after', this.save);
                this.listenTo(this.getRoot(), 'pim_enrich:form:update-association', this.save);

                return BaseSave.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            save: function (options) {
                var draft = $.extend(true, {}, this.getFormData());
                var draftId = draft.id;

                delete draft.product.meta;

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
                        __('pcmt.entity.draft.flash.update.fields_not_ready', {
                            'fields': fieldLabels.join(', ')
                        })
                    );

                    return;
                }

                this.showLoadingMask();
                this.getRoot().trigger('pim_enrich:form:entity:pre_save');

                return ProductDraftSaver
                    .save(draftId, draft)
                    .then(function (data) {
                        this.postSave();

                        this.setData(data, options);

                        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
                    }.bind(this))
                    .fail(this.fail.bind(this))
                    .always(this.hideLoadingMask.bind(this));
            },

            /**
             * On save fail
             *
             * @param {Object} response
             */
            fail: function (response) {
                switch (response.status) {
                    case 400:
                        this.getRoot().trigger(
                            'pim_enrich:form:entity:bad_request',
                            {'sentData': this.getFormData(), 'response': response.responseJSON}
                        );

                        if (response.responseJSON.message && response.responseJSON.message.includes('pcmt.entity.draft.error')) {
                            this.updateFailureMessage = __(response.responseJSON.message);
                        }

                        break;
                    case 500:
                        /* global console */
                        const message = response.responseJSON ? response.responseJSON : response;

                        console.error('Errors:', message);
                        this.getRoot().trigger('pim_enrich:form:entity:error:save', message);
                        break;
                    default:
                }

                messenger.notify(
                    'error',
                    this.updateFailureMessage
                );
            }
        });
    }
);
/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pcmt/product/template/form/no-draft'
    ],
    function (
        _,
        __,
        BaseForm,
        template
    ) {
        return BaseForm.extend({
            template: _.template(template),

            initialize: function (meta) {
                this.config = meta.config;

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    isVisible: false,
                    label: __(this.config.label),
                    route: this.code,
                    isForDraft: true
                });

                this.onExtensions('save-buttons:register-button', function (button) {
                    const saveButtonsExtension = this.getExtension('save-buttons');
                    if (undefined === saveButtonsExtension) {
                        throw Error('edit-form extension should declare save-buttons extension to be able to use ' +
                            'save extension');
                    }
                    saveButtonsExtension.trigger('save-buttons:add-button', button);
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            render: function () {
                this.$el.html(this.template({
                    noDraft: {
                        title: __(this.config.noDraft.title),
                        description: __(this.config.noDraft.description)
                    }
                }));

                this.renderExtensions();
            }
        });
    }
);

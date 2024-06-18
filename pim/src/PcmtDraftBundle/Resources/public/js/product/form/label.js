/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

define (
    [
        'underscore',
        'oro/translator',
        'pim/form/common/label',
        'pcmt/common/template/form/label'
    ],
    function (
        _,
        __,
        Label,
        template
    ) {
        return Label.extend({
            tagName: 'div',
            template: _.template(template),
            className: '',

            existingDraftLabel: '',

            initialize: function (meta) {
                this.config = meta.config;

                this.existingDraftLabel = __(this.config.labels.existingDraft);

                Label.prototype.initialize.apply(this, arguments);
            },

            render: function () {
                let label = '';

                if (0 !== this.getFormData().draftId) {
                    label = this.existingDraftLabel;
                }

                this.$el.empty().html(this.template({
                    label: label
                }));

                return this;
            }
        });
    }
);


/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

define (
    [
        'oro/translator',
        'pim/form/common/label',
        'pcmt/common/template/form/label',
        'pim/router'
    ],
    function (
        __,
        Label,
        template,
        Router
    ) {
        return Label.extend({
            tagName: 'div',
            template: _.template(template),
            className: '',

            createdDraftLabel: '',
            updatedDraftLabel: '',

            initialize: function (meta) {
                this.config = meta.config;

                this.createdDraftLabel = __(this.config.labels.createdDraft);
                this.updatedDraftLabel = __(this.config.labels.updatedDraft);

                Label.prototype.initialize.apply(this, arguments);
            },

            getLink() {
                let data  = this.getFormData().product.meta;

                return data ? Router.generate('pim_enrich_' + data.model_type + '_edit', { id: data.id }) : null;
            },

            getGoToBaseProductLabel() {
                var url = this.getLink();
                return url ? __('pcmt_core.drafts_editing.labels.go_to_base_product').replace('{{url}}', url) : null;
            },

            render: function () {
                let label = '';

                if ('' !== this.getFormData().updatedAt && this.getFormData().createdAt !== this.getFormData().updatedAt) {
                    const updated = new Date(this.getFormData().updatedAt);

                    label = this.updatedDraftLabel + ' ' + updated.toLocaleDateString(undefined, {year: 'numeric', month: 'long', day: 'numeric'});
                } else {
                    label = this.createdDraftLabel;
                }

                this.$el.empty().html(this.template({
                    label: label,
                    goToBaseProductLabel: this.getGoToBaseProductLabel(),
                }));

                return this;
            }
        });
    }
);

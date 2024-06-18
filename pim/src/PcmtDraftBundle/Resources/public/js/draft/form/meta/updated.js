/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'underscore',
        'pcmt/draft/common/updated',
        'pim/template/product/meta/updated'
    ],
    function (_, Updated, template) {
        return Updated.extend({
            className: 'AknColumn-block',

            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                var draft = this.getFormData();
                var html = '';

                if (draft.meta.updated) {
                    html = this.template({
                        label: this.label,
                        labelBy: this.labelBy,
                        loggedAt: _.result(draft.meta.updated, 'logged_at', null),
                        author: _.result(draft.meta.updated, 'author', null)
                    });
                }

                this.$el.html(html);

                return this;
            }
        });
    }
);

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
        'pim/user-context',
        'pim/router',
        'pim/form'
    ],
    function (
        $,
        _,
        __,
        UserContext,
        router,
        BaseForm
    ) {
        return BaseForm.extend({
            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:field:extension:add', this.addFieldExtension);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            addFieldExtension: function (event) {
                event.field.setEditable(false);

                return this;
            }
        });
    }
);

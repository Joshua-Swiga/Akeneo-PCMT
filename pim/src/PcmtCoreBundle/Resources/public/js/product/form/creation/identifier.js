/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define([
    'underscore',
    'pim/form/common/creation/field',
    'pim/user-context',
    'pim/i18n',
    'oro/translator',
    'pim/fetcher-registry',
    'pim/template/product-create-error'
], function(_, FieldForm, UserContext, i18n, __, FetcherRegistry, errorTemplate) {

    return FieldForm.extend({
        errorTemplate: _.template(errorTemplate),

        /**
         * Renders the form
         *
         * @return {Promise}
         */
        render: function() {
            return FetcherRegistry.getFetcher('attribute').getIdentifierAttribute()
                .then(function(identifier) {
                    var params = this.getRenderParams();
                    params.errors = this.getRoot().validationErrors;
                    params.label = i18n.getLabel(identifier.labels, UserContext.get('catalogLocale'), identifier.code);
                    this.$el.html(this.template(params));

                    this.delegateEvents();

                    return this;
                }.bind(this)).fail(() => {
                    this.$el.html(this.errorTemplate({message: __('pim_enrich.entity.product.flash.create.fail')}));
                });
        }
    });
});

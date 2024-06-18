/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * Destination attribute select field for rules job
 */
define([
    'underscore',
    'pcmt/rules/job/field/select',
    'pim/fetcher-registry'
], function (
    _,
    BaseField,
    FetcherRegistry
) {
    return BaseField.extend({
        fetch: function() {
            let types = [
                'pim_catalog_simpleselect',
                'pim_catalog_multiselect'
            ];
            FetcherRegistry.getFetcher(this.config.fetcher).fetchByTypes(types).then(function (options) {
                this.selectOptions = options;
                this.render();
            }.bind(this));
        },
    });
});
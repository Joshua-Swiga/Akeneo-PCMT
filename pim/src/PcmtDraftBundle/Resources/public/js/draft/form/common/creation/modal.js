/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'pim/form/common/creation/modal'
    ],
    function (
        BaseModal
    ) {
        return BaseModal.extend({
            normalize(errors) {
                const values = errors.values || [];
                return values.map(error => {
                    if (!error.path) {
                        error.path = error.attribute;
                    }
                    /**
                     * exclude family error from serialized errors bounded to modal input
                     * display it separately
                     */
                    if(error.path == 'family') {
                        error = '';
                    }
                    return error;
                })
            }
        });
    }
);
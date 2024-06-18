/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict'

define(['backbone'], function (Backbone) {
    return Backbone.View.extend({
        el: 'body',
        template: '<button>Create</button>',
        render: function () {
            this.el.html(this.template);
            return this;
        }
    })
});
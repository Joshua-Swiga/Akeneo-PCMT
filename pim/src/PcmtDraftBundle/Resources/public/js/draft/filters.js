/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

define(
    [
        'pim/form',
        'backbone',
        'jquery',
        'underscore',
        'oro/translator',
        'routing',
        'pcmt/product/template/filters'
    ],
    function (BaseForm, Backbone, $, _, __, Routing, template) {
        return BaseForm.extend({
            template: _.template(template),
            params: {},
            events: {
                "click .draft-status-choice": "statusChoiceChanged",
            },

            configure: function () {
                this.loadParams();
            },

            statusChoiceChanged: function (ev) {
                this.changeStatusChoice(ev.currentTarget.dataset.value);
            },

            changeStatusChoice: function (newChosenStatusId) {
                newChosenStatusId = parseInt(newChosenStatusId);
                const model = this.getFormData();
                if (model.chosenStatus && newChosenStatusId === model.chosenStatus.id) {
                    return;
                }
                let status = _.find(this.params.statuses, function (s) {
                    return s.id === newChosenStatusId;
                });
                if (!status) {
                    return;
                }
                model.chosenStatus = status;
                this.setData(model);
                this.getRoot().trigger('pcmt:drafts:statusChanged');
                this.render();
            },

            loadParams: function () {
                $.get(Routing.generate('pcmt_core_drafts_params'))
                    .done(_.bind(function (resp) {
                        this.params = resp;
                        this.changeStatusChoice(1);
                    }, this))
                    .fail(function () {
                        console.log('Loading params failed');
                    });
            },

            render: function () {
                const model = this.getFormData();
                if (!model.chosenStatus) {
                    return;
                }
                this.$el.html(this.template({
                    params: this.params,
                    chosenStatus: model.chosenStatus,
                    _: _,
                    __: __
                }));
            }
        });
    }
);


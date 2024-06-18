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
        'pcmt/product/template/draft-list',
        'pim/dialog',
        'pim/security-context',
        'pim/router',
        'pim/form-builder',
        'pim/form-modal',
        'pcmt/draft/collection',
        'oro/messenger'
    ],
    function (BaseForm, Backbone, $, _, __, Routing, template, Dialog, SecurityContext, Router, FormBuilder, FormModal, DraftCollection,
              messenger) {

        return BaseForm.extend({
            template: _.template(template),
            collection: null,

            chosenDrafts: {
                allSelected: false,
                selected: [],
                excluded: [],

                count: function (collection) {
                    if (this.allSelected) {
                        return collection.state.totalRecords - this.excluded.length;
                    } else {
                        return this.selected.length;
                    }
                },

                isSelected: function (draftId) {
                    return this.allSelected && !this.excluded.includes(parseInt(draftId)) || !this.allSelected && this.selected.includes(parseInt(draftId));
                },

                showCheckboxes: function () {
                    return this.allSelected || this.selected.length > 0;
                },

                selectAll: function () {
                    this.allSelected = true;
                    this.selected = [];
                    this.excluded = [];
                },

                selectNone: function () {
                    this.allSelected = false;
                    this.selected = [];
                    this.excluded = [];
                },

                select: function (draftId) {
                    if (!this.allSelected) {
                        if (!this.selected.includes(parseInt(draftId))) {
                            this.selected.push(parseInt(draftId));
                        }
                    } else {
                        if (this.excluded.includes(parseInt(draftId))) {
                            _.each(this.excluded, (draft, index) => {
                                if (draft === parseInt(draftId)) {
                                    this.excluded.splice(index, 1);
                                    return true;
                                }
                            })
                        }
                    }
                },

                unselect: function (draftId) {
                    if (!this.allSelected) {
                        if (this.selected.includes(parseInt(draftId))) {
                            _.each(this.selected, (draft, index) => {
                                if (draft === parseInt(draftId)) {
                                    this.selected.splice(index, 1);
                                    return true;
                                }
                            })
                        }
                    } else {
                        if (!this.excluded.includes(parseInt(draftId))) {
                            this.excluded.push(parseInt(draftId));
                        }
                    }
                },

                reset: function () {
                    this.allSelected = false;
                    this.selected = [];
                    this.excluded = [];
                }
            },

            events: {
                "click .draft-changes-shortcut": "changesExpand",
                "click .draft-changes-full": "changesCollapse",
                "click .draft-reject": "rejectDraftClicked",
                "click .draft-approve": "approveDraftClicked",
                "click .draft-edit": "editDraftClicked",
                "click .draft-checkbox": "checkDraft"
            },

            configure: function () {
                this.setDrafts([]);
                this.startLoading();
                this.chosenDrafts.reset();

                this.collection = new DraftCollection(null, {
                    inputName: 'draft-grid'
                });

                this.listenTo(this.getRoot(), 'pcmt:drafts:pageChanged', this.onUpdatePagination);
                this.listenTo(this.getRoot(), 'pcmt:drafts:statusChanged', this.onUpdateStatusChoice);

                this.listenTo(this.getRoot(), 'pcmt:drafts:select', this.select);
                this.listenTo(this.getRoot(), 'pcmt:drafts:selectAll', this.selectAll);
                this.listenTo(this.getRoot(), 'pcmt:drafts:selectVisible', this.selectVisible);
                this.listenTo(this.getRoot(), 'pcmt:drafts:selectNone', this.selectNone);

                this.listenTo(this.getRoot(), 'pcmt:drafts:approve', this.approveBulkDraftClicked);
                this.listenTo(this.getRoot(), 'pcmt:drafts:approved', this.loadDrafts);

                this.listenTo(this.getRoot(), 'pcmt:drafts:reject', this.rejectBulkDraftClicked);
                this.listenTo(this.getRoot(), 'pcmt:drafts:rejected', this.loadDrafts);

                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);
            },

            startLoading: function () {
                let model = this.getFormData();

                model.loading = true;

                this.setData(model);
            },

            stopLoading: function () {
                let model = this.getFormData();

                model.loading = false;

                this.setData(model);
            },

            setDrafts: function (drafts) {
                let model = this.getFormData();

                model.drafts = drafts;

                this.setData(model);
            },

            getDrafts: function () {
                return this.getFormData().drafts;
            },

            onUpdatePagination: function (page) {
                this.collection.updateState({currentPage: page});

                this.loadDrafts();
            },

            onUpdateStatusChoice: function () {
                this.collection.updateState({currentPage: this.collection.state.firstPage});

                this.loadDrafts();
            },

            changesExpand: function (ev) {
                let id = ev.currentTarget.dataset.draftId;
                let divFullId = '#draft-changes-full-' + id;
                $(divFullId).show();
                let divShortcutId = '#draft-changes-shortcut-' + id;
                $(divShortcutId).hide();
            },

            changesCollapse: function (ev) {
                let id = ev.currentTarget.dataset.draftId;
                let divFullId = '#draft-changes-full-' + id;
                $(divFullId).hide();
                let divShortcutId = '#draft-changes-shortcut-' + id;
                $(divShortcutId).show();
            },

            approveDraftClicked: function (ev) {
                let draftId = ev.currentTarget.dataset.draftId;
                Dialog.confirm(
                    'pcmt.entity.draft.confirm.approve.content',
                    'pcmt.entity.draft.confirm.approve.title',
                    function () {
                        return this.approveDraft(draftId);
                    }.bind(this),
                    '',
                    'ok',
                    'pcmt.entity.draft.confirm.approve.button_text'
                );
            },

            editDraftClicked: function (ev) {
                var draftId = parseInt(ev.currentTarget.dataset.draftId);
                var draft = _.filter(this.getDrafts(), (draft) => {
                    return draftId === draft.id;
                })[0];

                if ('New product draft' === draft.type) {
                    if (draft.values.parentId) {
                        return FormBuilder.build('pcmt-product-model-add-child-form').then(modal => {
                            modal.setData(draft.values);
                            modal.open().done(() => this.loadDrafts());
                        });
                    } else {
                        return FormBuilder.build('pcmt-product-create-modal').then(modal => {
                            modal.setData(draft.values);
                            modal.open().done(() => this.loadDrafts());
                        });
                    }
                } else if ('New product model draft' === draft.type) {
                    if (draft.values.parentId) {
                        return FormBuilder.build('pcmt-product-model-add-child-form').then(modal => {
                            modal.setData(draft.values);
                            modal.open().done(() => this.loadDrafts());
                        });
                    } else {
                        return FormBuilder.build('pcmt-product-model-create-modal').then(modal => {
                            modal.setData(draft.values);
                            modal.open().done(() => this.loadDrafts());
                        });
                    }
                } else {
                    Router.navigate('/' + Routing.generate('pcmt_core_drafts_edit', {id: draftId}), true);
                }
            },

            approveDraft: function (draftId) {
                $.ajax({
                    url: Routing.generate('pcmt_core_drafts_approve', {id: draftId}),
                    type: 'PUT'
                }).done((function () {
                    this.loadDrafts();
                    messenger.notify(
                        'success',
                        __('pcmt.entity.draft.flash.approve.success')
                    );
                }).bind(this)).fail(function (jqXHR) {
                    let messages = [];
                    if (jqXHR.responseJSON && jqXHR.responseJSON.values) {
                        messages = _.map(jqXHR.responseJSON.values, function (value) {
                            return value.attribute + ': ' + value.message;
                        });
                        messages = _.uniq(messages);
                    }
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        messages = [__(jqXHR.responseJSON.message)];
                    }
                    Dialog.alert(messages.join('<br>'), __('pcmt.entity.draft.flash.approve.fail'), '');
                });
            },

            rejectDraftClicked: function (ev) {
                let draftId = ev.currentTarget.dataset.draftId;
                Dialog.confirmDelete(
                    'pcmt.entity.draft.confirm.reject.content',
                    'pcmt.entity.draft.confirm.reject.title',
                    function () {
                        return this.rejectDraft(draftId);
                    }.bind(this),
                    'subtitle',
                    'pcmt.entity.draft.confirm.reject.button_text'
                );
            },

            rejectDraft: function (draftId) {
                $.ajax({
                    url: Routing.generate('pcmt_core_drafts_delete', {id: draftId}),
                    type: 'DELETE'
                }).done((function () {
                    this.getRoot().trigger('pcmt:drafts:rejected');
                    this.loadDrafts();
                    messenger.notify(
                        'success',
                        __('pcmt.entity.draft.flash.reject.success')
                    );
                }).bind(this)).fail(function (jqXHR) {
                    let messages = [];
                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                        messages = [__(jqXHR.responseJSON.message)];
                    }
                    Dialog.alert(messages.join('<br>'), __('pcmt.entity.draft.flash.reject.fail'), '');
                });
            },

            approveBulkDraftClicked: function (ev) {
                Dialog.confirm(
                    __('pcmt.entity.draft.confirm.bulk_approve.content', {
                        count: this.chosenDrafts.count(this.collection)
                    }),
                    __('pcmt.entity.draft.confirm.bulk_approve.title'),
                    function () {
                        this.startLoading();
                        return this.approveBulkDraft();
                    }.bind(this),
                    '',
                    'ok',
                    __('pcmt.entity.draft.confirm.bulk_approve.button_text'),
                );
            },

            rejectBulkDraftClicked: function (ev) {
                Dialog.confirm(
                    __('pcmt.entity.draft.confirm.bulk_reject.content', {
                        count: this.chosenDrafts.count(this.collection)
                    }),
                    __('pcmt.entity.draft.confirm.bulk_reject.title'),
                    function () {
                        this.startLoading();
                        return this.rejectBulkDraft();
                    }.bind(this),
                    '',
                    'AknButton--important',
                    __('pcmt.entity.draft.confirm.bulk_reject.button_text'),
                );
            },

            approveBulkDraft: function () {
                $.ajax({
                    url: Routing.generate('pcmt_core_drafts_actions_bulk'),
                    data: JSON.stringify({
                        jobInstanceCode: 'job_drafts_bulk_approve',
                        chosenDrafts: {
                            allSelected: this.chosenDrafts.allSelected,
                            selected: this.chosenDrafts.selected,
                            excluded: this.chosenDrafts.excluded,
                        }
                    }),
                    type: 'PUT'
                }).done((function () {
                    this.getRoot().trigger('pcmt:drafts:approved');
                    messenger.notify(
                        'success',
                        __('pcmt_messages.job_drafts_bulk_approve.success', {})
                    );
                }).bind(this)).fail((function (jqXHR) {
                    this.getRoot().trigger('pcmt:drafts:approved');
                    messenger.notify(
                        'error',
                        __('pcmt_messages.job_drafts_bulk_approve.fail', {})
                    );
                }).bind(this));
            },

            rejectBulkDraft: function () {
                $.ajax({
                    url: Routing.generate('pcmt_core_drafts_actions_bulk'),
                    data: JSON.stringify({
                        jobInstanceCode: 'job_drafts_bulk_reject',
                        chosenDrafts: {
                            allSelected: this.chosenDrafts.allSelected,
                            selected: this.chosenDrafts.selected,
                            excluded: this.chosenDrafts.excluded,
                        }
                    }),
                    type: 'PUT'
                }).done((function () {
                    this.getRoot().trigger('pcmt:drafts:rejected');
                    messenger.notify(
                        'success',
                        __('pcmt_messages.job_drafts_bulk_reject.success', {})
                    );
                }).bind(this)).fail((function (jqXHR) {
                    this.getRoot().trigger('pcmt:drafts:rejected');
                    messenger.notify(
                        'error',
                        __('pcmt_messages.job_drafts_bulk_reject.fail', {})
                    );
                }).bind(this));
            },

            checkDraft: function (ev) {
                this.getRoot().trigger('pcmt:drafts:select', parseInt(ev.currentTarget.dataset.draftId), ev.currentTarget.checked);
            },

            select: function (draftId, isChecked) {
                if (isChecked) {
                    this.chosenDrafts.select(draftId);
                } else {
                    this.chosenDrafts.unselect(draftId);
                }

                this.render();
            },

            selectAll: function () {
                this.chosenDrafts.selectAll();

                this.render();
            },

            selectNone: function () {
                this.chosenDrafts.selectNone();

                this.render();
            },

            selectVisible: function () {
                this.chosenDrafts.selectNone();

                this.collection.each((draft) => {
                    this.getRoot().trigger('pcmt:drafts:select', draft.id, true);
                });

                this.render();
            },

            loadDrafts: function () {
                const model = this.getFormData();

                if (!model.chosenStatus) {
                    return;
                }

                this.startLoading();

                this.collection.fetch({
                    url: Routing.generate('pcmt_core_drafts_api', {
                        status: model.chosenStatus.id
                    }),
                    success: (collection, response) => {
                        this.setDrafts(response.objects);
                        this.stopLoading();
                        this.getRoot().trigger('pcmt:drafts:listReloaded', this.collection);
                    },
                    error: () => {
                        this.setDrafts([]);
                        this.stopLoading();
                        this.getRoot().trigger('pcmt:drafts:listReloaded', this.collection);
                    }
                });
            },

            checkChosenDrafts: function () {
                _.each($('.draft-checkbox'), (checkbox) => {
                    if (this.chosenDrafts.isSelected($(checkbox).data('draft-id'))) {
                        $(checkbox).prop('checked', true);
                    }
                });

                if (this.chosenDrafts.showCheckboxes() > 0) {
                    $('.draft-checkbox-bodyCell').removeClass('AknGrid-bodyCell--actions');
                } else {
                    $('.draft-checkbox-bodyCell').addClass('AknGrid-bodyCell--actions');
                }
            },

            render: function () {
                const model = this.getFormData();
                if (!model.chosenStatus) {
                    return;
                }
                this.$el.html(this.template({
                    ...model,
                    _: _,
                    __: __,
                    rejectPermission: SecurityContext.isGranted('pcmt_permission_drafts_reject'),
                    approvePermission: SecurityContext.isGranted('pcmt_permission_drafts_approve'),
                    editPermission: SecurityContext.isGranted('pcmt_permission_drafts_edit')
                }));
                this.checkChosenDrafts();
                $('#draft_status_choice_' + model.chosenStatus.id).addClass('AknDropdown-menuLink--active active');
            }
        });
    }
);


/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * The difference to original module is to use `product.meta.id` instead of `meta.id`
 */
define(
    [
        'pim/product-edit-form/comments',
        'oro/translator',
        'oro/messenger'
    ],
    function (
        BaseForm,
        __,
        messenger
    ) {
        return BaseForm.extend({

            loadData: function () {
                return $.get(
                    Routing.generate(
                        'pim_enrich_product_comments_rest_get',
                        {
                            id: this.getFormData().product.meta.id
                        }
                    )
                );
            },

            saveComment: function () {
                $.ajax({
                    type: 'POST',
                    url: Routing.generate('pim_enrich_product_comments_rest_post', { id: this.getFormData().product.meta.id }),
                    contentType: 'application/json',
                    data: JSON.stringify({ 'body': this.$('.comment-create textarea').val() })
                }).done(function () {
                    this.render();
                    messenger.notify('success', __('flash.comment.create.success'));
                }.bind(this)).fail(function () {
                    messenger.notify('error', __('flash.comment.create.error'));
                });
            },

            saveReply: function (event) {
                var $thread = $(event.currentTarget).parents('.comment-thread');

                $.ajax({
                    type: 'POST',
                    url: Routing.generate(
                        'pim_enrich_product_comment_reply_rest_post',
                        {
                            id: this.getFormData().product.meta.id,
                            commentId: $thread.data('comment-id')
                        }
                    ),
                    contentType: 'application/json',
                    data: JSON.stringify({ 'body': $thread.find('textarea').val()})
                }).done(function () {
                    $thread.find('textarea').val('');
                    this.render();
                    messenger.notify('success', __('flash.comment.reply.success'));
                }.bind(this)).fail(function () {
                    messenger.notify('error', __('flash.comment.reply.error'));
                });
            }
        });
    }
);
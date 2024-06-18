/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

'use strict';

/**
 * Module used to display the generals properties of an entity type
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'pim/common/property',
        'pcmt/permissions/attribute_group/template',
        'jquery.select2'
    ],
    function (
        _,
        __,
        BaseForm,
        FetcherRegistry,
        propertyAccessor,
        template
    ) {
        return BaseForm.extend({
            className: 'tabsection',
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            render: function () {
                var config = this.options.config;

                this.$el.html(this.template({
                    __: __,
                    model: this.getFormData(),
                    sectionTitle: __(config.sectionTitle),
                    codeLabel: __(config.codeLabel),
                    formRequired: __(config.formRequired),
                    inputField: config.inputField,
                    hasId: propertyAccessor.accessProperty(this.getFormData(), 'meta.id') !== null
                }));

                this.$el.find('select.select2').select2({});

                this.renderExtensions();
            }
        });
    }
);

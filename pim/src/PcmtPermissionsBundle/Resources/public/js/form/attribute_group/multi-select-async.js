/*
 * Copyright (c) 2020, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */
'use strict';

define(
    [
        'jquery',
        'pim/form/common/fields/simple-select-async',
        'routing'
    ],
    function (
        $,
        SimpleSelectAsync,
        Routing
    ) {
        return SimpleSelectAsync.extend({
            /**
             * {@inheritdoc}
             */
            select2Data(term, page) {
                return {};
            },

            /**
             * {@inheritdoc}
             */
            convertBackendItem(item) {
                if (undefined !== item.name) {
                    return {
                        id: item.meta.id,
                        text: item.name
                    }
                }

                if (undefined !== item.label) {
                    return {
                        id: item.code,
                        text: item.label,
                    };
                }

                return {
                    id: item.code,
                    text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code),
                };
            },

            /**
             * {@inheritdoc}
             */
            getSelect2Options() {
                const parent = SimpleSelectAsync.prototype.getSelect2Options.apply(this, arguments);
                parent.multiple = true;

                return parent;
            },

            /**
             * {@inheritdoc}
             */
            select2InitSelection(element, callback) {
                const strValues = $(element).val();
                const values = strValues.split(',').map(item => parseInt(item));

                if (values.length > 0) {
                    $.ajax({
                        url: this.choiceUrl,
                        data: { options: { identifiers: strValues } },
                        type: this.choiceVerb
                    }).then(response => {
                        let selecteds = Object.values(response).filter((item) => {
                            return values.indexOf(item.meta.id) > -1;
                        });

                        selecteds = selecteds.map((selected) => {
                            return this.convertBackendItem(selected);
                        });

                        callback(selecteds);
                    });
                }
            }
        });
    });

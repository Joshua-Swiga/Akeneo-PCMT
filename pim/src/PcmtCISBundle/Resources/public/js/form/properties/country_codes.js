/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form/common/fields/field',
        'pim/fetcher-registry',
        'pim/user-context',
        'pim/i18n',
        'pim/template/attribute/tab/properties/group'
    ],
    function (
        $,
        _,
        __,
        BaseField,
        fetcherRegistry,
        UserContext,
        i18n,
        template
    ) {
        return BaseField.extend({
            events: {
                'change select': function (event) {
                    this.errors = [];
                    this.updateModel(this.getFieldValue(event.target));
                    this.getRoot().render();
                }
            },
            template: _.template(template),
            countries: {},

            /**
             * {@inheritdoc}
             */
            configure: function () {
                return $.when(
                    BaseField.prototype.configure.apply(this, arguments),
                    fetcherRegistry.getFetcher('country-code').fetchAll()
                        .then(function (countries) {
                            this.countries = countries;
                        }.bind(this))
                );
            },

            /**
             * {@inheritdoc}
             */
            renderInput: function (templateContext) {
                return this.template(_.extend(templateContext, {
                    value: this.getFormData()[this.fieldName],
                    groups: _.sortBy(this.countries, 'sort_order'),
                    i18n: i18n,
                    locale: UserContext.get('catalogLocale'),
                    labels: {
                        defaultLabel: __('pcmt.entity.subscription.property.target_market_country_code.choose')
                    }
                }));
            },

            /**
             * {@inheritdoc}
             */
            postRender: function () {
                this.$('select.select2').select2();
            },

            /**
             * {@inheritdoc}
             */
            getFieldValue: function (field) {
                return $(field).val();
            }
        });
    });

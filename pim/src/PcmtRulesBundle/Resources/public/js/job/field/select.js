'use strict';

/**
 * Select field extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
    'underscore',
    'pim/job/common/edit/field/field',
    'pcmt/rules/template/job/field/select',
    'jquery.select2',
    'pim/fetcher-registry',
    'pim/user-context',
    'pim/i18n'
], function (
    _,
    BaseField,
    fieldTemplate,
    select2,
    FetcherRegistry,
    UserContext,
    i18n,
) {
    return BaseField.extend({
        fieldTemplate: _.template(fieldTemplate),
        events: {
            'change select': 'updateState'
        },
        selectOptions: [],

        /**
         * {@inheritdoc}
         */
        initialize: function (config) {
            BaseField.prototype.initialize.apply(this, arguments);

            this.fetch();
        },

        fetch: function() {
            FetcherRegistry.getFetcher(this.config.fetcher).fetchAll().then(function (options) {
                this.selectOptions = options;
                this.render();
            }.bind(this));
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function (templateContext) {
            return this.fieldTemplate(_.extend(templateContext, {
                options: this.selectOptions,
                i18n: i18n,
                locale: UserContext.get('catalogLocale'),
                placeholder: 'placeholder'
            }));
        },

        /**
         * {@inheritdoc}
         */
        render: function () {
            BaseField.prototype.render.apply(this, arguments);

            this.$('.select2').select2();
        },

        /**
         * Get the field dom value
         *
         * @return {string}
         */
        getFieldValue: function () {
            return this.$('select').val();
        }
    });
});

/*
 * Copyright (c) 2019, VillageReach
 * Licensed under the Non-Profit Open Software License version 3.0.
 * SPDX-License-Identifier: NPOSL-3.0
 */

/**
 * A select2 field displaying family variants dependent on the family field in the same parent form.
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
    'pim/fetcher-registry',
    'pim/router',
    'pim/product-model/form/creation/variant',
], function(FetcherRegistry, Routing, Variant) {
    return Variant.extend({
        /**
         * {@inheritdoc}
         */
        initialize() {
            Variant.prototype.initialize.apply(this, arguments);

            this.previousFamily = null;
            this.readOnly = false;
        },

        /**
         * Updates the choice URL when the model changed
         */
        onPostUpdate() {
            if (null === this.previousFamily && undefined !== this.getFormData().family_variant) {
                this.previousFamily = this.getFormData().family;
                this.setUrlForLoadingVariants().then(() => {
                    this.render();
                });
            }

            if (this.getFormData().family !== this.previousFamily) {
                this.previousFamily = this.getFormData().family;

                if (this.getFormData().family) {
                    this.setUrlForLoadingVariants();
                }

                this.setData({[this.fieldName]: null});

                this.render();
            }
        },

        setUrlForLoadingVariants() {
            return this.getFamilyIdFromCode(this.getFormData().family).then(familyId => {
                this.setChoiceUrl(
                    Routing.generate(this.config.loadUrl, {
                        family_id: familyId,
                    })
                );
            });
        }
    });
});

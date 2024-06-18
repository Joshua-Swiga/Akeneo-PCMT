'use strict';

define(['jquery', 'underscore', 'pim/base-fetcher', 'routing'], function($, _, BaseFetcher, Routing) {
  return BaseFetcher.extend({

    /**
     * Fetch all elements of the collection
     *
     * @return {Promise}
     */
    fetchForOptions: function (options) {
      let searchOptions = {};
      let possibleOptions = ['sourceFamily', 'destinationFamily', 'types', 'validationRule'];
      _.each(possibleOptions, function(option) {
        if (options[option]) {
          searchOptions[option] = options[option];
        }
      });
      return this.search(searchOptions);
    },

  });
});

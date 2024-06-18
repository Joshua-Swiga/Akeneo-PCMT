'use strict';

define(['jquery', 'underscore', 'oro/translator', 'pim/form', 'fhir/template/attribute/tab/additional','oro/messenger'],
    function ($, _, __, BaseForm, template, messenger) {
        return BaseForm.extend({
            events: {
                "click select": "updateModelAfterChange",
            },
            template: _.template(template),
            mapping:'',
            mapping_response: '',
            configure: function () {
                this.trigger('tab:register', {
                    code: this.code,
                    label: __('pcmt_core.fhir.tab.lable')
                });
                this.listenTo(this.getRoot(), 'pcmt:fhir:attribute:form:render:before', () => {
                    this.getCurrentMapping();
                });
                return BaseForm.prototype.configure.apply(this, arguments);
            },
            render: function () {
                //get mapping
                this.getRoot().trigger('pcmt:fhir:attribute:form:render:before');
                this.$el.html(this.template({
                    label: __('pcmt_core.fhir.select.lable'),
                    options: this.getMappingOptions(),
                    selected: this.mapping,
                    title: __('pcmt_core.fhir.title.lable'),
                    placeholder: __('pcmt_core.fhir.placeholder.lable')
                }));
                this.$('.select2').select2();
                this.delegateEvents();
                return this;
            },
            updateModelAfterChange: function (event) {
                this.updateFhirMapping(this.getFormData().code,this.getFormData().type,event.target.value);
                this.mapping=event.target.value;
                return this;
            },
            updateFhirMapping: function (code,type,mapping){
                $.ajax(
                    {
                        url: Routing.generate('pim_fhir_fhir_create'),
                        type: 'POST',
                        data: JSON.stringify({
                            code: code,
                            type: type,
                            mapping: mapping
                        })
                    }
                ).done(function(resp) {
                    console.log(resp);
                    if(resp.success === true){
                        messenger.notify(
                            'success',
                            __('pcmt_core.fhir.saved.lable')
                        );
                    }else{
                        messenger.notify(
                            'error',
                            __('pcmt_core.fhir.failed.lable')
                        );
                    }
                }.bind(this));

            },
            getCurrentMapping: function (){
                $.ajax(
                    {
                        url: Routing.generate('pim_fhir_get_mapping'),
                        type: 'POST',
                        data: JSON.stringify({
                            code: this.getFormData().code,
                            type: this.getFormData().type
                        })
                    }
                ).done(function (resp){
                    this.mapping=resp.mapping;
                    this.$('#fhir_mapping').val(resp.mapping).trigger('change');
                }.bind(this));
            },
            getMappingOptions: function (){
                return [
                    {"value":"other","label":__("fhir.options.other")},
                    {"value":"description","label":__("fhir.options.description")},
                    {"value":"identifier","label":__("fhir.options.identifier")},
                    {"value":"marketingAuthorization","label":__("fhir.options.marketingAuthorization")},
                ];
            }
        });
    }
);
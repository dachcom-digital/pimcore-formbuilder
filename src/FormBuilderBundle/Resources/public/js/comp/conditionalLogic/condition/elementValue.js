pimcore.registerNS('Formbuilder.comp.conditionalLogic.condition');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.condition.elementValue');
Formbuilder.comp.conditionalLogic.condition.elementValue = Class.create(Formbuilder.comp.conditionalLogic.condition.abstract, {

    getItem: function () {
        var _ = this,
            conditionTypesStore = Ext.create('Ext.data.Store', {
                fields: ['label', 'value'],
                data: [{
                    label: t('form_builder_element_value_type_contains'),
                    value: 'contains'
                }, {
                    label: t('form_builder_element_value_type_is_checked'),
                    value: 'is_checked'
                }, {
                    label: t('form_builder_element_value_type_is_not_checked'),
                    value: 'is_not_checked'
                }, {
                    label: t('form_builder_element_value_type_is_greater'),
                    value: 'is_greater'
                }, {
                    label: t('form_builder_element_value_type_is_less'),
                    value: 'is_less'
                }, {
                    label: t('form_builder_element_value_type_is_value'),
                    value: 'is_value'
                }, {
                    label: t('form_builder_element_value_type_is_not_value'),
                    value: 'is_not_value'
                }, {
                    label: t('form_builder_element_value_type_is_empty_value'),
                    value: 'is_empty_value'
                }]
            }),
            fieldStore = Ext.create('Ext.data.Store', {
                fields: ['name', 'display_name'],
                data: this.panel.getFormFields()
            }),
            descriptionField = new Ext.form.Label({
                hidden: true,
                anchor: '100%',
                flex: 1,
                style: 'display:block; padding:3px; background:#f5f5f5; border:1px solid #eee; font-weight: 300; word-wrap:break-word;'
            }),
            allowValueFieldEmpty = this.data && (
                this.data.comparator === 'is_checked' || this.data.comparator === 'is_not_checked' || this.data.comparator === 'is_empty_value'
            ),
            valueField = new Ext.form.TextField({
                name: _.generateFieldName(this.sectionId, this.index, 'value'),
                fieldLabel: t('form_builder_element_value_value'),
                anchor: '100%',
                labelAlign: 'top',
                summaryDisplay: true,
                allowBlank: allowValueFieldEmpty,
                disabled: allowValueFieldEmpty,
                value: this.data ? this.data.value : null,
                flex: 1,
                listeners: {
                    updateIndexName: function (sectionId, index) {
                        this.name = _.generateFieldName(sectionId, index, 'value');
                    }
                }
            }),
            items = [
                {
                    xtype: 'hidden',
                    name: _.generateFieldName(this.sectionId, this.index, 'type'),
                    value: this.fieldConfiguration.identifier,
                    listeners: {
                        updateIndexName: function (sectionId, index) {
                            this.name = _.generateFieldName(sectionId, index, 'type');
                        }
                    }
                },
                {
                    xtype: 'tagfield',
                    name: _.generateFieldName(this.sectionId, this.index, 'fields'),
                    fieldLabel: t('form_builder_element_value_fields'),
                    style: 'margin: 0 5px 0 0',
                    queryDelay: 0,
                    stacked: true,
                    displayField: 'display_name',
                    valueField: 'name',
                    mode: 'local',
                    labelAlign: 'top',
                    store: fieldStore,
                    editable: false,
                    selectOnFocus: false,
                    triggerAction: 'all',
                    anchor: '100%',
                    value: this.data ? this.checkFieldAvailability(this.data.fields, fieldStore, 'name') : null,
                    allowBlank: false,
                    flex: 1,
                    listeners: {
                        updateIndexName: function (sectionId, index) {
                            this.name = _.generateFieldName(sectionId, index, 'fields');
                        }
                    }
                },
                {
                    xtype: 'combo',
                    name: _.generateFieldName(this.sectionId, this.index, 'comparator'),
                    fieldLabel: t('form_builder_element_value_type'),
                    style: 'margin: 0 5px 0 0',
                    queryDelay: 0,
                    displayField: 'label',
                    valueField: 'value',
                    mode: 'local',
                    labelAlign: 'top',
                    store: conditionTypesStore,
                    editable: false,
                    selectOnFocus: false,
                    triggerAction: 'all',
                    anchor: '100%',
                    value: this.data ? this.data.comparator : null,
                    summaryDisplay: true,
                    allowBlank: false,
                    flex: 1,
                    listeners: {
                        updateIndexName: function (sectionId, index) {
                            this.name = _.generateFieldName(sectionId, index, 'comparator');
                        },
                        change: function (combo, value) {
                            var allowValueFieldEmpty = value === 'is_checked' || value === 'is_not_checked' || value === 'is_empty_value';
                            valueField.setDisabled(allowValueFieldEmpty);
                            valueField.allowBlank = allowValueFieldEmpty;
                            if (allowValueFieldEmpty) {
                                valueField.setValue('');
                                valueField.clearInvalid();
                            } else {
                                valueField.validate();
                            }

                            if (value === 'contains') {
                                descriptionField.setHidden(false);
                                descriptionField.setHtml(t('form_builder_element_value_type_contains_description'));
                            } else {
                                descriptionField.setHidden(true);
                            }
                        }
                    }
                },
                valueField
            ],
            compositeField = new Ext.form.FieldContainer({
                layout: 'hbox',
                hideLabel: true,
                style: 'padding-bottom:5px;',
                items: items
            }),
            fieldId = Ext.id();

        return new Ext.form.FormPanel({
            id: fieldId,
            type: 'combo',
            forceLayout: true,
            style: 'margin: 10px 0 0 0',
            bodyStyle: 'padding: 10px 30px 10px 30px; min-height:30px;',
            tbar: this.getTopBar(fieldId),
            items: [compositeField, descriptionField],
            listeners: {}
        });
    }
});

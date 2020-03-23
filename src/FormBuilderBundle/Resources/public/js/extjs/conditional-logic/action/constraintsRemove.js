pimcore.registerNS('Formbuilder.extjs.conditionalLogic.action');
pimcore.registerNS('Formbuilder.extjs.conditionalLogic.action.constraintsRemove');
Formbuilder.extjs.conditionalLogic.action.constraintsRemove = Class.create(Formbuilder.extjs.conditionalLogic.action.abstract, {

    getItem: function () {
        var _ = this,
            constraints = this.panel.getFormConstraints(),
            constraintTypesStore = Ext.create('Ext.data.Store', {
                fields: ['label', 'id'],
                data: constraints
            }),
            fieldStore = Ext.create('Ext.data.Store', {
                fields: ['name', 'display_name'],
                data: this.panel.getFormFields()
            }),
            validationSelectionField = new Ext.form.field.Tag({
                xtype: 'tagfield',
                style: 'margin:0 5px 0 0',
                name: _.generateFieldName(this.sectionId, this.index, 'validation'),
                fieldLabel: t('form_builder_constraints_type'),
                queryDelay: 0,
                stacked: true,
                displayField: 'label',
                valueField: 'id',
                mode: 'local',
                labelAlign: 'top',
                store: constraintTypesStore,
                editable: false,
                selectOnFocus: false,
                filterPickList: true,
                anchor: '100%',
                value: this.data ? this.data.validation : null,
                disabled: this.data && this.data.removeAllValidations,
                allowBlank: false,
                flex: 1,
                listeners: {
                    updateIndexName: function (sectionId, index) {
                        this.name = _.generateFieldName(sectionId, index, 'validation');
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
                    style: 'margin:0 5px 0 0',
                    name: _.generateFieldName(this.sectionId, this.index, 'fields'),
                    fieldLabel: t('form_builder_constraints_fields'),
                    queryDelay: 0,
                    stacked: true,
                    displayField: 'display_name',
                    valueField: 'name',
                    mode: 'local',
                    labelAlign: 'top',
                    store: fieldStore,
                    editable: false,
                    selectOnFocus: false,
                    filterPickList: true,
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
                validationSelectionField,
                {
                    xtype: 'checkbox',
                    name: _.generateFieldName(this.sectionId, this.index, 'removeAllValidations'),
                    fieldLabel: t('form_builder_constraints_all'),
                    labelAlign: 'top',
                    editable: false,
                    anchor: '100%',
                    value: this.data ? this.data.removeAllValidations : null,
                    flex: 1,
                    listeners: {
                        updateIndexName: function (sectionId, index) {
                            this.name = _.generateFieldName(sectionId, index, 'removeAllValidations');
                        },
                        change: function (e) {
                            validationSelectionField.setDisabled(this.checked);
                            if (this.checked) {
                                validationSelectionField.clearValue();
                            }
                        }
                    }
                }
            ],
            compositeField = new Ext.form.FieldContainer({
                layout: 'hbox',
                submitValue: false,
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
            items: compositeField,
            listeners: {}
        });
    }
});

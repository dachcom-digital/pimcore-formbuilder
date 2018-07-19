pimcore.registerNS('Formbuilder.comp.conditionalLogic.action');
pimcore.registerNS('Formbuilder.comp.conditionalLogic.action.mailBehaviour');
Formbuilder.comp.conditionalLogic.action.mailBehaviour = Class.create(Formbuilder.comp.conditionalLogic.action.abstract, {

    valueField: null,

    compositeField: null,

    getItem: function () {

        var _ = this, myId = Ext.id();

        var fieldStore = Ext.create('Ext.data.Store', {
            fields: ['name', 'display_name'],
            data: this.panel.getFormFields().fields
        });

        var items = [
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
                xtype: 'combo',
                name: _.generateFieldName(this.sectionId, this.index, 'identifier'),
                fieldLabel: t('form_builder_mail_behaviour_identifier'),
                style: 'margin: 0 5px 0 0',
                queryDelay: 0,
                displayField: 'key',
                valueField: 'value',
                mode: 'local',
                labelAlign: 'top',
                store: new Ext.data.ArrayStore({
                    fields: ['value', 'key'],
                    data: [
                        ['recipient', t('form_builder_mail_behaviour_identifier_recipient')],
                        ['mailTemplate', t('form_builder_mail_behaviour_identifier_mail_template')]
                    ]
                }),
                editable: true,
                triggerAction: 'all',
                anchor: '100%',
                value: this.data ? this.data.identifier : null,
                summaryDisplay: true,
                allowBlank: false,
                flex: 1,
                listeners: {
                    updateIndexName: function (sectionId, index) {
                        this.name = _.generateFieldName(sectionId, index, 'identifier');
                    },
                    change: function (field, value) {
                        this.generateValueField(value);
                    }.bind(this)
                }
            },
            {
                xtype: 'combo',
                name: _.generateFieldName(this.sectionId, this.index, 'mailType'),
                fieldLabel: t('form_builder_mail_behaviour_mail_type'),
                style: 'margin: 0 5px 0 0',
                queryDelay: 0,
                displayField: 'key',
                valueField: 'value',
                mode: 'local',
                labelAlign: 'top',
                store: new Ext.data.ArrayStore({
                    fields: ['value', 'key'],
                    data: [
                        ['main', t('form_builder_mail_behaviour_mail_type_main')],
                        ['copy', t('form_builder_mail_behaviour_mail_type_copy')]
                    ]
                }),
                editable: true,
                triggerAction: 'all',
                anchor: '100%',
                value: this.data && this.data.mailType ? this.data.mailType : 'main',
                summaryDisplay: true,
                allowBlank: false,
                flex: 1,
                listeners: {
                    updateIndexName: function (sectionId, index) {
                        this.name = _.generateFieldName(sectionId, index, 'mailType');
                    }
                }
            }
        ];

        this.compositeField = new Ext.form.FieldContainer({
            layout: 'hbox',
            hideLabel: true,
            style: 'padding-bottom:5px;',
            items: items
        });

        // add initial value field
        if (this.data && this.data.value) {
            this.generateValueField(this.data.value);
        }

        return new Ext.form.FormPanel({
            id: myId,
            type: 'combo',
            forceLayout: true,
            style: 'margin: 10px 0 0 0',
            bodyStyle: 'padding: 10px 30px 10px 30px; min-height:30px;',
            tbar: this.getTopBar(myId),
            items: this.compositeField
        });
    },

    generateValueField: function (value) {

        var _ = this;

        if (this.valueField !== null) {
            this.compositeField.remove(this.valueField);
        }

        if (value === 'recipient') {
            this.valueField = new Ext.form.TextField({
                name: _.generateFieldName(this.sectionId, this.index, 'value'),
                cls: 'form_builder_mail_behaviour_value_field',
                fieldLabel: t('form_builder_mail_behaviour_mail_value'),
                anchor: '100%',
                labelAlign: 'top',
                summaryDisplay: true,
                allowBlank: false,
                value: this.data ? this.data.value : null,
                flex: 1,
                listeners: {
                    updateIndexName: function (sectionId, index) {
                        this.name = _.generateFieldName(sectionId, index, 'value');
                    }
                }
            });
        } else if (value === 'mailTemplate') {

            var fieldPath = new Ext.form.TextField({
                fieldLabel: t('path'),
                name: _.generateFieldName(this.sectionId, this.index, 'value'),
                cls: 'form_builder_mail_behaviour_value_field',
                fieldLabel: t('form_builder_mail_behaviour_mail_value'),
                anchor: '100%',
                labelAlign: 'top',
                fieldCls: 'pimcore_droptarget_input',
                allowBlank: false,
                value: this.data ? this.data.value : null,
                flex: 1,
                listeners: {
                    updateIndexName: function (sectionId, index) {
                        console.log('hahahaha: updateIndexName');
                        this.name = _.generateFieldName(sectionId, index, 'value');
                    }
                }
            });

            fieldPath.on('render', function (el) {
                new Ext.dd.DropZone(el.getEl(), {
                    reference: this,
                    ddGroup: "element",
                    getTargetFromEvent: function (e) {
                        return fieldPath.getEl();
                    },
                    onNodeOver: function (target, dd, e, data) {
                        return Ext.dd.DropZone.prototype.dropAllowed;
                    }.bind(this),
                    onNodeDrop: function (target, dd, e, data) {
                        var record = data.records[0];
                        if (record.data.elementType == 'document') {
                            fieldPath.setValue(record.data.path);
                            return true;
                        }
                        return false;
                    }.bind(this)
                });
            }.bind(this));

            this.valueField = fieldPath;

        }

        this.compositeField.add(this.valueField);
    }
});

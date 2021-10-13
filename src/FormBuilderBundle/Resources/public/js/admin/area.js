var FormBuilderAreaWatcher = Class.create({

    watchOutputWorkflow: function (combo, record, eOpts) {

        var formId = record.get('field1'),
            outputWorkflowFieldset = Ext.get(this.id).up('fieldset').next(),
            outputWorkflowSelector = outputWorkflowFieldset.query('.fb-output-workflow-selector > div')[0],
            outputWorkflowCombo = Ext.ComponentQuery.query('combo[id=' + outputWorkflowSelector.id + ']')[0];

        combo.setDisabled(true);
        outputWorkflowCombo.setDisabled(true);

        Ext.Ajax.request({
            url: '/admin/formbuilder/output-workflow/get-output-workflow-list/' + formId,
            success: function (response) {

                var workflows = [],
                    data = Ext.decode(response.responseText);

                Ext.Array.each(data.outputWorkflows, function (row, i) {
                    workflows.push([row.id, row.name]);
                });

                if (workflows.length === 0) {
                    workflows = [
                        ['none', t('form_builder.area.no_output_workflow')]
                    ];
                }

                outputWorkflowCombo.setValue(workflows[0][0]);
                outputWorkflowCombo.setStore(workflows);
                outputWorkflowCombo.setDisabled(false);
                combo.setDisabled(false);

            }.bind(this)
        });

    },

    watchPresets: function (combo, record, eOpts) {

        var presetName = record.get('field1'),
            presetFieldset = Ext.get(this.id).up();

        if (presetFieldset.component.query('label[cls=preview-field]').length > 0) {
            Ext.Array.each(presetFieldset.component.query('label[cls=preview-field]'), function (el) {
                el.destroy();
            });
        }

        if (presetName === 'custom') {
            return;
        }

        combo.setDisabled(true);

        Ext.Ajax.request({
            url: '/admin/formbuilder/settings/get-preset-description/' + presetName,
            success: function (response) {

                var data = Ext.decode(response.responseText);

                combo.setDisabled(false);

                presetFieldset.component.add({
                    xtype: 'label',
                    cls: 'preview-field',
                    html: data.previewData.description
                });

            }.bind(this)
        });
    }
});

document.addEventListener('DOMContentLoaded', function (ev) {
    window['formBuilderAreaWatcher'] = new FormBuilderAreaWatcher();
});

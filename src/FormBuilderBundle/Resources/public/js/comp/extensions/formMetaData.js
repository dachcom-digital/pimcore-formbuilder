pimcore.registerNS('Formbuilder.comp.extensions.formMetaData');
Formbuilder.comp.extensions.formMetaData = Class.create({

    detailWindow: null,
    data: {},

    initialize: function (data) {
        this.data = data;
        this.getInputWindow();
        this.detailWindow.show();
    },

    getInputWindow: function () {

        if (this.detailWindow !== null) {
            return this.detailWindow;
        }

        this.detailWindow = new Ext.Window({
            width: 600,
            height: 400,
            iconCls: 'pimcore_icon_info',
            layout: 'fit',
            closeAction: 'close',
            plain: true,
            autoScroll: true,
            modal: true,
            buttons: [
                {
                    text: t('close'),
                    iconCls: 'pimcore_icon_empty',
                    handler: function () {
                        this.detailWindow.hide();
                        this.detailWindow.destroy();
                    }.bind(this)
                }
            ]
        });

        this.createPanel();
    },

    createPanel: function () {

        var items = [];

        if (this.data.creation_date) {
            items.push(this.generateDateField(t('creationdate'), this.data.creation_date));
        }

        if (this.data.modification_date) {
            items.push(this.generateDateField(t('modificationdate'), this.data.modification_date));
        }

        if (this.data.created_by) {
            items.push(this.generateUserField(t('userowner'), this.data.created_by));
        }

        if (this.data.modified_by) {
            items.push(this.generateUserField(t('usermodification'), this.data.modified_by));
        }

        this.detailWindow.add(new Ext.form.FormPanel({
            border: false,
            frame: false,
            bodyStyle: 'padding:10px',
            items: items,
            defaults: {
                labelWidth: 130
            },
            collapsible: false,
            autoScroll: true
        }));
    },

    generateUserField: function (label, value) {

        var htmlValue = value,
            item, user = pimcore.globalmanager.get('user');

        if (user.admin) {
            htmlValue = value + ' ' + '<a href="#">' + t('click_to_open') + '</a>';
        }

        item = {
            xtype: 'displayfield',
            fieldLabel: label,
            readOnly: true,
            value: htmlValue,
            width: 350
        };

        if (user.admin) {
            item.listeners = {
                render: function (value, detailWindow, c) {
                    c.getEl().on('click', function () {
                        pimcore.helpers.showUser(value);
                        detailWindow.close();
                    }, c);
                }.bind(this, value, this.detailWindow)
            };
        }

        return item;
    },

    generateDateField: function (label, value) {

        if (value === '0000-00-00 00:00:00') {
            return {};
        }

        return {
            xtype: 'textfield',
            fieldLabel: label,
            readOnly: true,
            value: Ext.util.Format.date(value, 'Y-m-d H:i'),
            width: 350
        };
    },

    generateDefaultField: function (label, value) {
        return {
            xtype: 'textfield',
            fieldLabel: label,
            readOnly: true,
            value: value,
            width: 350
        };
    }
});
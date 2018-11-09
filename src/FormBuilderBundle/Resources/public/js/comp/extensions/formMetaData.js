pimcore.registerNS('Formbuilder.comp.extensions.formMetaData');
Formbuilder.comp.extensions.formMetaData = Class.create({

    formId: null,
    data: {},

    detailWindow: null,

    initialize: function (formId, data) {
        this.formId = formId;
        this.data = data;
        this.getInputWindow();
        this.detailWindow.show();
    },

    getInputWindow: function () {

        if (this.detailWindow !== null) {
            return this.detailWindow;
        }

        this.detailWindow = new Ext.Window({
            width: 800,
            height: 600,
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

        var items = [],
            itemsPerPage = pimcore.helpers.grid.getDefaultPageSize(-1),
            requiredByStore, requiredByGrid, requiredByPanel;

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

        requiredByStore = new Ext.data.Store({
            pageSize: itemsPerPage,
            proxy: {
                type: 'ajax',
                url: '/admin/formbuilder/settings/get-form-dependencies',
                reader: {
                    type: 'json',
                    rootProperty: 'documents'
                },
                extraParams: {
                    formId: this.formId
                }
            },
            autoLoad: false,
            fields: ['id', 'path', 'type', 'subtype']
        });

        requiredByGrid = new Ext.grid.GridPanel({
            store: requiredByStore,
            columns: [
                {text: 'ID', sortable: true, dataIndex: 'id', hidden: false},
                {text: t('type'), sortable: true, dataIndex: 'type', hidden: false},
                {text: t('path'), sortable: true, dataIndex: 'path', flex: 1, renderer: Ext.util.Format.htmlEncode}
            ],
            flex: 1,
            columnLines: true,
            stripeRows: true,
            bbar: pimcore.helpers.grid.buildDefaultPagingToolbar(requiredByStore, {pageSize: itemsPerPage})
        });

        requiredByGrid.on('rowdblclick', function (grid, record) {
            var d = record.data;
            if (d.type === 'object') {
                pimcore.helpers.openObject(d.id, d.subtype);
            } else if (d.type === 'asset') {
                pimcore.helpers.openAsset(d.id, d.subtype);
            } else if (d.type === 'document') {
                pimcore.helpers.openDocument(d.id, d.subtype);
            }

            this.detailWindow.hide();
            this.detailWindow.destroy();

        }.bind(this));

        requiredByStore.load();

        requiredByPanel = new Ext.Panel({
            title: t('required_by'),
            flex: 1,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            resizable: false,
            split: false,
            collapsible: false,
            items: [requiredByGrid]
        });

        items.push(requiredByPanel);

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
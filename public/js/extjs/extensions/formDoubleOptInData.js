pimcore.registerNS('Formbuilder.extjs.extensions.formDoubleOptInData');
Formbuilder.extjs.extensions.formDoubleOptInData = Class.create({

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
            sessionsStore, sessionsGrid, sessionsPanel;

        sessionsStore = new Ext.data.Store({
            pageSize: itemsPerPage,
            proxy: {
                type: 'ajax',
                url: Routing.generate('form_builder.controller.admin.get_double_opt_in_session', {formId: this.formId}),
                reader: {
                    type: 'json',
                    rootProperty: 'sessions'
                }
            },
            autoLoad: false,
            fields: ['token', 'email', 'dispatchLocation', 'applied', 'creationDate']
        });

        sessionsGrid = new Ext.grid.GridPanel({
            store: sessionsStore,
            columns: [
                {
                    text: 'Token',
                    sortable: false,
                    dataIndex: 'token',
                    flex: 1,
                    hidden: false
                },
                {
                    text: t('form_builder_form.double_opt_in.sessions.email'),
                    sortable: false,
                    dataIndex: 'email',
                    flex: 2,
                    hidden: false
                },
                {
                    text: t('form_builder_form.double_opt_in.sessions.creation_date'),
                    sortable: false,
                    dataIndex: 'creationDate',
                    flex: 1,
                    hidden: false,
                    renderer: function (v) {

                        if (!v) {
                            return '--';
                        }

                        return Ext.util.Format.date(v, 'd.m.Y H:i');
                    }
                },
                {
                    text: t('form_builder_form.double_opt_in.sessions.applied'),
                    sortable: false,
                    dataIndex: 'applied',
                    flex: 1,
                    hidden: false
                },
                {
                    text: t('form_builder_form.double_opt_in.sessions.dispatch_location'),
                    sortable: false,
                    dataIndex: 'dispatchLocation',
                    flex: 1,
                    hidden: true,
                },
                {
                    xtype: 'actioncolumn',
                    width: 30,
                    items: [{
                        tooltip: t('remove'),
                        icon: '/bundles/toolbox/images/admin/delete.svg',
                        handler: function (grid, rowIndex) {

                            var rec = grid.getStore().getAt(rowIndex);

                            Ext.Msg.confirm(t('delete'), t('form_builder_form.double_opt_in.sessions.delete_confirm'), function (btn) {

                                if (btn !== 'yes') {
                                    return;
                                }

                                Ext.Ajax.request({
                                    method: 'DELETE',
                                    url: Routing.generate('form_builder.controller.admin.delete_double_opt_in_session', {token: rec.get('token')}),
                                    success: function (response) {

                                        var data = Ext.decode(response.responseText);

                                        if (!data.success) {
                                            Ext.Msg.alert(t('error'), data.message);

                                            return;
                                        }

                                        grid.getStore().reload();
                                    },
                                    failure: function () {
                                        Ext.Msg.alert(t('error'), t('error'));
                                    }
                                });

                            }.bind(this));
                        }.bind(this)
                    }]
                }
            ],
            flex: 1,
            columnLines: true,
            stripeRows: true,
            bbar: pimcore.helpers.grid.buildDefaultPagingToolbar(sessionsStore, {pageSize: itemsPerPage})
        });

        sessionsStore.load();

        sessionsPanel = new Ext.Panel({
            title: t('form_builder_form.double_opt_in.sessions'),
            flex: 1,
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            resizable: false,
            split: false,
            collapsible: false,
            items: [sessionsGrid]
        });

        items.push(sessionsPanel);

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
    }
});
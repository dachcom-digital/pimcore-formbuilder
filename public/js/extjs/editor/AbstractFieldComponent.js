class AbstractFieldComponent {

    constructor(editor, type, widgetConfiguration, widgets) {

        this.editor = editor;
        this.type = type;
        this.widgetConfiguration = widgetConfiguration;
        this.widgets = widgets;
    }

    getWidgetComponent(type, subType) {

        let component = null;

        Ext.Array.each(this.widgets, function (widget) {
            if (widget.type === type && widget.subType === subType) {
                component = widget;
                return false;
            }
        });

        return component;
    }

    getWidgetConfig(widgetConfigIdentifier, storedData) {

        let configItems = [];
        let initialData = {};

        Ext.Object.each(this.widgetConfiguration, function (configIdentifier, config) {

            if (widgetConfigIdentifier !== configIdentifier) {
                return true;
            }

            Ext.Object.each(config, function (configName, configElement) {

                switch (configElement.type) {
                    case 'checkbox' :
                        configItems.push({
                            type: 'checkbox',
                            name: configName,
                            label: configElement.label
                        });

                        initialData[configName] = storedData[configName] ? storedData[configName] === 'true' : configElement.defaultValue === true;

                        break;
                    case 'input' :
                        configItems.push({
                            type: 'input',
                            name: configName,
                            label: configElement.label
                        });

                        initialData[configName] = storedData[configName] ? storedData[configName] : (configElement.defaultValue !== null ? configElement.defaultValue : '')

                        break;
                    case 'read-only' :
                        configItems.push({
                            type: 'input',
                            name: configName,
                            label: configElement.label
                        });

                        initialData[configName] = storedData[configName] ? storedData[configName] : (configElement.defaultValue !== null ? configElement.defaultValue : '')

                        break;
                }
            });

        });

        return {
            items: configItems,
            initialData: initialData
        };
    }
}
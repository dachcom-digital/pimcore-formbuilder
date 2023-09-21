class FieldComponent extends AbstractFieldComponent {

    constructor(editor, type, widgetConfiguration, widgets) {

        super(editor, type, widgetConfiguration, widgets);

        editor.on('init', () => {
            this.setup();
        });

        editor.on('preinit', () => {

            this.win = editor.getWin();
            this.doc = editor.getDoc();

            editor.serializer.addNodeFilter('fb-field', (nodes) => {
                nodes.forEach((node) => {
                    if (!!node.attr('contenteditable')) {
                        node.attr('contenteditable', null);
                    }
                });
            });

        });

        editor.on('drop', (evt) => {

            var element, elementData;

            if (!evt.dataTransfer) {
                return;
            }

            evt.preventDefault();

            element = evt.dataTransfer.getData('element');

            if (!element) {
                return;
            }

            elementData = JSON.parse(element);

            if (elementData.configIdentifier === 'fb_field_container_fieldset' || elementData.configIdentifier === 'fb_field_container_repeater') {
                return;
            }

            editor.insertContent(this.createFieldMarkup(elementData.type, elementData.subType));
        });
    }

    setup() {

        const _ = this,
            template = this.doc.createElement('template');

        this.editor.ui.registry.addContextToolbar('fb-field', {
            predicate: function (node) {
                return node.nodeName.toLowerCase() === 'fb-field'
            },
            items: 'fb-field-options',
            scope: 'node',
            position: 'node'
        });

        this.editor.ui.registry.addMenuButton('fb-field-options', {
            icon: 'info',
            text: 'Field Options',
            fetch: (callback) => {
                var items = [
                    {
                        type: 'togglemenuitem',
                        icon: 'format',
                        text: 'Act as Label',
                        onAction: (data, b, c) => {
                            const node = this.editor.selection.getNode();
                            node.dataset.render_type = data.isActive() ? 'V' : 'L';
                        },
                        onSetup: (api) => {

                            const node = this.editor.selection.getNode();
                            api.setActive(node.dataset.render_type === 'L');

                            return () => {};
                        }
                    }
                ];
                callback(items);
            }
        });

        template.innerHTML = `
                <style>
                    :host {
                        display: inline-block;
                        background: rgb(101 123 177 / 34%);
                        padding: 0 3px;
                        color: rgb(29 72 97);
                        border-radius: 3px;
                        font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
                        font-size: 0.9375em;
                    }

                    :host(:hover) {
                        cursor: default;
                    }
                
                    span#render-type {
                        font-weight: bold;
                        background: #a3b5e1;
                        border-radius: 4px;
                        width: 17px;
                        display: inline-block;
                        text-align: center;
                        height: 17px;
                        font-size: 13px;
                    }
                    
                    button {
                        background: transparent;
                        border: 0;
                        outline: 0;
                        -webkit-tap-highlight-color: rgba(0,0,0,0);
                        -webkit-user-select: none;
                        user-select: none;
                        font-weight: normal;
                        padding: 3px;
                        margin: 0 0 0 5px;
                        border-radius: 3px;
                        position: relative;
                        top: 2px;
                    }
        
                    button svg {
                        fill: rgb(29 72 97);
                        display: block;
                    }
        
                    button:hover {
                        background: rgba(29,72,97,0.30);
                    }

                    :host([contentEditable=false][data-mce-selected]) {
                        outline: none !important;
                        box-shadow: 0 0 0 3px #b4d7ff;
                    }
                </style>

                <span>
                    <span id="render-type">V</span>
                    <slot>fb-field-not-defined</slot>
                    <button type="button" id="btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"><path d="M0 9.502v2.5h2.5l7.373-7.374-2.5-2.5L0 9.502zm11.807-6.807c.26-.26.26-.68 0-.94l-1.56-1.56a.664.664 0 0 0-.94 0l-1.22 1.22 2.5 2.5 1.22-1.22z"/></svg>
                    </button>
                </span>
            `;

        class FbField extends this.win.HTMLElement {
            constructor() {

                super();

                const shadow = this.attachShadow({mode: 'open'});

                this.setAttribute('contenteditable', false);

                this.shadowRoot.appendChild(template.content.cloneNode(true));
            }

            connectedCallback() {

                let widgetConfig = null,
                    hasWidgetConfig = false;

                const type = this.dataset.type;
                const subType = this.dataset.sub_type !== 'null' ? this.dataset.sub_type : null;
                const widgetComponent = _.getWidgetComponent(type, subType);

                if (widgetComponent !== null) {
                    widgetConfig = _.getWidgetConfig(widgetComponent.configIdentifier, this.dataset);
                    hasWidgetConfig = widgetConfig.items.length > 0;
                }

                const editConditionalBlock = () => {
                    _.dialog(this, widgetConfig);
                    return false;
                }

                if (hasWidgetConfig === false) {
                    this.shadowRoot.getElementById('btn').style.display = 'none';

                    return;
                }

                this.shadowRoot.getElementById('btn').addEventListener('click', editConditionalBlock);
            }

            attributeChangedCallback(name, oldValue, newValue) {
                if (name === 'data-render_type') {
                    this.shadowRoot.getElementById('render-type').textContent = newValue;
                }
            }

            static get observedAttributes() {
                return ['data-render_type'];
            }
        }

        this.win.customElements.define('fb-field', FbField);
    }

    dialog(field, widgetConfig) {

        let widgetConfigBody = {
                type: 'panel',
                items: []
            },
            initialData = {};

        const type = field ? field.dataset.type : null;
        const subType = field && field.dataset.sub_type !== 'null' ? field.dataset.sub_type : null;

        if (widgetConfig !== null) {
            widgetConfigBody.items = widgetConfig.items;
            initialData = widgetConfig.initialData
        }

        this.editor.windowManager.open({
            title: 'Field Configuration',
            body: widgetConfigBody,
            initialData: initialData,
            buttons: [
                {
                    type: 'cancel',
                    name: 'closeButton',
                    text: 'Cancel'
                },
                {
                    type: 'submit',
                    name: 'submitButton',
                    text: 'Save',
                    primary: true
                }
            ],
            onSubmit: (dialog) => {

                const data = dialog.getData();

                if (!field) {
                    this.editor.insertContent(this.createFieldMarkup(type, subType, data));
                } else {
                    this.editor.undoManager.transact(() => {
                        Ext.Object.each(data, function (index, value) {
                            field.dataset[index] = value;
                        });
                    });

                    this.editor.nodeChanged();
                }

                dialog.close();
            }
        });
    }

    createFieldMarkup(type, subType, dialogData) {

        const fieldLabel = subType === null ? type : subType;

        return `<fb-field data-type="${type}" data-render_type="V" data-sub_type="${subType}">${fieldLabel}</fb-field>`;
    }

}
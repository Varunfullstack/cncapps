import React from 'react';
import {getEditorNamespace} from 'ckeditor4-integrations-common';
import PropTypes from 'prop-types';

class CNCCKEditor extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);

        this.element = null;
        this.editor = null;
        this._destroyed = false;
    }

    componentDidMount() {

        this._initEditor();
    }

    _initEditor() {
        const {readOnly, type, onBeforeLoad, style, value, disableClipboard} = this.props;
        const config = this.getCNCCKEditorConfig();
        config.readOnly = readOnly;

        getEditorNamespace(CNCCKEditor.editorUrl, null).then(CKEDITOR => {
            // (#94)
            if (this._destroyed) {
                return;
            }

            // (#94)
            if (!this.element) {
                throw new Error('Element not available for mounting CKEDITOR instance.');
            }

            const constructor = type === 'inline' ? 'inline' : 'replace';

            if (onBeforeLoad) {
                onBeforeLoad(CKEDITOR);
            }

            const editor = this.editor = CKEDITOR[constructor](this.element, config);

            this._attachEventHandlers();

            // We must force editability of the inline editor to prevent `element-conflict` error.
            // It can't be done via config due to CKEditor 4 upstream issue (#57, ckeditor/ckeditor4#3866).
            if (type === 'inline' && !readOnly) {
                editor.on('instanceReady', () => {
                    editor.setReadOnly(false);
                    editor.container.setStyles(style);
                }, null, null, -1);
            }

            if (disableClipboard) {
                editor.on('paste', (evt) => {
                    evt.stop();
                })
            }

            if (style && type !== 'inline') {
                editor.on('loaded', () => {
                    editor.container.setStyles(style);
                });
            }

            if (value) {
                editor.setData(value);
            }
        }).catch(console.error);
    }

    componentWillUnmount() {
        this._destroyEditor();
    }

    componentDidUpdate(prevProps) {
        const {props, editor} = this;

        /* istanbul ignore next */
        if (!editor) {
            return;
        }

        if (prevProps.value !== props.value && editor.getData() !== props.value) {
            editor.setData(props.value);
        }

        if (prevProps.readOnly !== props.readOnly) {
            editor.setReadOnly(props.readOnly);
        }

        if (prevProps.style !== props.style && editor.container) {
            editor.container.setStyles(props.style);
        }

        this._attachEventHandlers(prevProps);
    }

    _attachEventHandlers(prevProps = {}) {
        const props = this.props;

        Object.keys(this.props).forEach(propName => {
            if (!propName.startsWith('on') || prevProps[propName] === props[propName]) {
                return;
            }

            this._attachEventHandler(propName, prevProps[propName]);
        });
    }

    _attachEventHandler(propName, prevHandler) {
        const evtName = `${propName[2].toLowerCase()}${propName.substr(3)}`;

        if (prevHandler) {
            this.editor.removeListener(evtName, prevHandler);
        }

        this.editor.on(evtName, this.props[propName]);
    }

    _destroyEditor() {
        if (this.editor) {
            this.editor.destroy();
        }

        this.editor = null;
        this.element = null;
        this._destroyed = true;
    }

    render() {
        return <div id={this.props.name}
                    name={this.props.name}
                    style={this.props.style}
                    ref={ref => (this.element = ref)}
                    className="testing"
        />;
    }

    getCNCCKEditorConfig = () => {
        const defaultConfig = {
            contentsCss: "/screen.css",
            toolbarStartupExpanded: false,
            toolbar: "CNCToolbar",
            toolbar_CNCToolbar: [
                [
                    "Source",
                    "-",
                    "-",
                    "Bold",
                    "Italic",
                    "Underline",
                    "TextColor",
                ],
                ["NumberedList", "BulletedList"],
                ["Table"],
                ["Font", "FontSize"],
                ["Link"],
            ],
            extraPlugins: "font,wordcount",
            fontSize_sizes:
                "8/8pt;9/9pt;10/10pt;11/11pt;12/12pt;14/14pt;16/16pt;18/18pt;20/20pt;22/22pt;24/24pt;26/26pt;28/28pt;36/36pt;48/48pt;72/72pt",
            wordcount: {
                showParagraphs: false,
                showCharCount: true,
                minCharCount: !this.props.minCharCount ? -1 : this.props.minCharCount
            },
            width: this.props.width || "auto",
            height: this.props.height || 500,
            resize_minHeight: this.props.height || 500,
            removePlugins: "liststyle,tabletools,language,tableselection,scayt,wsc,magicline",
            disableNativeSpellChecker: true,
            wsc_customDictionaryIds: '100920',
            font_defaultLabel: 'Arial',
            fontSize_defaultLabel: '10pt',
        };

        if (this.props.sharedSpaces) {
            defaultConfig.extraPlugins += ",sharedspace";
            defaultConfig.removePlugins += ",floatingspace,maximize,resize,elementspath";
            defaultConfig.sharedSpaces = {
                top: this.props.top,
                bottom: this.props.bottom
            };
        }


        return defaultConfig;
    }


}

CNCCKEditor.propTypes = {
    type: PropTypes.oneOf([
        'classic',
        'inline'
    ]),
    value: PropTypes.string,
    config: PropTypes.object,
    name: PropTypes.string,
    style: PropTypes.object,
    readOnly: PropTypes.bool,
    onBeforeLoad: PropTypes.func,
    minCharCount: PropTypes.number,
};

CNCCKEditor.defaultProps = {
    type: 'classic',
    value: '',
    config: {},
    readOnly: false
};

CNCCKEditor.editorUrl = 'https://cdn.ckeditor.com/4.15.1/standard-all/ckeditor.js';
CNCCKEditor.displayName = 'CKEditor';

export default CNCCKEditor;
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
        const {readOnly, type, onBeforeLoad, style, data} = this.props;
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
                    console.log('setting stiles here');
                    editor.container.setStyles(style);
                }, null, null, -1);
            }

            if (style && type !== 'inline') {
                editor.on('loaded', () => {
                    console.log('trying to set styles here', style);
                    editor.container.setStyles(style);
                });
            }

            if (data) {
                editor.setData(data);
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

        if (prevProps.data !== props.data && editor.getData() !== props.data) {
            editor.setData(props.data);
        }

        if (prevProps.readOnly !== props.readOnly) {
            editor.setReadOnly(props.readOnly);
        }

        if (prevProps.style !== props.style && editor.container) {
            console.log('trying to set styles here, container exists', props.style);
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
                    className="CNCCKEditor black"
                    ref={ref => (this.element = ref)}
        />;
    }

    getCNCCKEditorConfig = () => {
        return {
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
            removePlugins: 'liststyle,tabletools,language,tableselection,scayt,wsc',
            disableNativeSpellChecker: true,
            wsc_customDictionaryIds: '100920',

        };
    }


}

CNCCKEditor.propTypes = {
    type: PropTypes.oneOf([
        'classic',
        'inline'
    ]),
    data: PropTypes.string,
    config: PropTypes.object,
    name: PropTypes.string,
    style: PropTypes.object,
    readOnly: PropTypes.bool,
    onBeforeLoad: PropTypes.func
};

CNCCKEditor.defaultProps = {
    type: 'classic',
    data: '',
    config: {},
    readOnly: false,
};

CNCCKEditor.editorUrl = 'https://cdn.ckeditor.com/4.15.1/standard-all/ckeditor.js';
CNCCKEditor.displayName = 'CKEditor';

export default CNCCKEditor;
import React from 'react';

/**
 * paramters
 * onChange > function
 * inline bool default =false
 */
export default class CKEditor extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.elementName = "editor_" + this.props.id;
        this.componentDidMount = this.componentDidMount.bind(this);
        this.state = {
            value: this.props.value,
            reinit: false,
            height: this.props.height,
        };
    }

    componentDidUpdate = (prevProps, prevState) => {
    };

    render() {
        if (this.state.reinit) {
            setTimeout(() => this.initEditor(), 200);
        }
        return this.el(
            "div",
            {
                style: {
                    display: "inline-table",
                    height: this.props.height,
                    width: this.props.width || "100%",
                },
            },
            this.el("textarea", {
                id: this.elementName,
                name: this.elementName,
                defaultValue: this.props.value,
            })
        );
    }

    initEditor = () => {
        if (CKEDITOR.instances[this.elementName]) {
            CKEDITOR.instances[this.elementName].destroy(true);
        }
        if (this.props.inline)
            CKEDITOR.inline(this.elementName, this.getCKEditorConfig());
        else
            CKEDITOR.replace(this.elementName, this.getCKEditorConfig());
        if (CKEDITOR.instances[this.elementName]) {
            if (this.props.disableClipboard)
                CKEDITOR.instances[this.elementName].on("paste", function (evt) {
                    evt.cancel();
                });
            CKEDITOR.instances[this.elementName].on(
                "change",
                function () {
                    let data = CKEDITOR.instances[this.elementName].getData();
                    this.props.onChange(data);
                }.bind(this)
            );
        }
    };
    getCKEditorConfig = () => {
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
                [ "Font", "FontSize"],
                ["Link"],
            ],
            extraPlugins: "font,wordcount,scayt",
            fontSize_sizes:
                "8/8pt;9/9pt;10/10pt;11/11pt;12/12pt;14/14pt;16/16pt;18/18pt;20/20pt;22/22pt;24/24pt;26/26pt;28/28pt;36/36pt;48/48pt;72/72pt",
            wordcount: {
                showParagraphs: false,
                showCharCount: true,
                minCharCount: !this.props.minCharCount ? -1 : this.props.minCharCount
            },
            width: this.props.width || "auto",
            height: this.props.height || 220,
            resize_minHeight: this.props.height || 220,
            removePlugins: 'liststyle,tabletools,language,tableselection',
            scayt_customerId: 'LHXUCjpl0y2gdBb',
            scayt_autoStartup: true,
            grayt_autoStartup: true,
            scayt_sLang: "en_GB",
            disableNativeSpellChecker: true,
        };
    }

    componentDidMount() {
        this.initEditor();
        CKEditor.getDerivedStateFromProps = CKEditor.getDerivedStateFromProps.bind(
            this
        );
    }

    static getDerivedStateFromProps(props, current_state) {
        if (current_state && current_state.value !== props.value) {
            this.initEditor();
            return {
                value: props.value,
                reinit: true,
                height: props.height,
            };
        }

        return {
            reinit: false,
        };
    }
}
import React from 'react';
import CKEditor from 'ckeditor4-react';

/**
 * paramters
 * onChange > function
 * inline bool default =false
 */
export default class CNCCKEditor extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            value: this.props.value,
            reinit: false,
            height: this.props.height,
        };
    }


    render() {

        return (
            <div style={{display: "inline-table", height: this.props.height, width: this.props.width || "100%",}}
                 className={this.props.className}
            >
                <CKEditor type={this.props.type || 'inline'}
                          config={this.getCNCCKEditorConfig()}
                          data={this.props.value}
                          onChange={evt => this.props.onChange(evt.editor.getData())}
                          readOnly={this.props.readOnly}
                >
                </CKEditor>
            </div>
        );
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
            height: this.props.height || 220,
            resize_minHeight: this.props.height || 220,
            removePlugins: 'liststyle,tabletools,language,tableselection,scayt,wsc',
            disableNativeSpellChecker: true,
            wsc_customDictionaryIds: '100920',

        };
    }


}

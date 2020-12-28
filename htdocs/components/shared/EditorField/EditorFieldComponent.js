import * as React from "react";
import CNCCKEditor from "../CNCCKEditor";

export default class EditorFieldComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
    }

    render() {
        const {name, value, onChange, minCharCount, disableClipboard} = this.props;
        return (
            <div className="inline_editor_field">
                <CNCCKEditor name={name}
                             value={value}
                             type="inline"
                             onChange={($event) => onChange($event.editor.getData())}
                             minCharCount={minCharCount}
                             disableClipboard={disableClipboard}
                />
            </div>
        )
    }
}
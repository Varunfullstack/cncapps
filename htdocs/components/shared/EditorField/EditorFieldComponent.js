import * as React from "react";
import CNCCKEditor from "../CNCCKEditor";

export default class EditorFieldComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
    }

    render() {
        const {
            name,
            value,
            onChange,
            minCharCount,
            disableClipboard,
            hasToolbar,
            autoFocus,
            style,
            excludeFromErrorCount
        } = this.props;
        return (
            <div className="inline_editor_field">
                <CNCCKEditor name={name}
                             value={value}
                             type="inline"
                             style={style}
                             key="cncEditor"
                             onChange={data => {
                                 console.log("on change runs!", data);
                                 onChange(data)
                             }}
                             minCharCount={minCharCount}
                             disableClipboard={disableClipboard}
                             sharedSpaces={hasToolbar || false}
                             autoFocus={autoFocus || false}
                             excludeFromErrorCount={excludeFromErrorCount || false}
                />
            </div>
        )
    }
}
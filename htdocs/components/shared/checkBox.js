import React from 'react';

class CheckBox extends React.Component {
    el = React.createElement;

    render() {
        const {label, name, checked, onChange, reversed} = this.props;
        const items = [
            this.el("input", {
                type: "checkbox",
                key: name,
                defaultChecked: checked,
            }),
            this.el("label", {key: name + "_label"}, label),
        ];

        return this.el("div", {className: 'check-box', onClick: onChange},
            reversed ? items.reverse() : items
        );
    }
}

export default CheckBox;

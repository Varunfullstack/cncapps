import ToolTip from "./ToolTip.js";
import React from 'react';

/**
 * props
 * title
 * name
 * size
 * onClick
 * item
 */
class Icon extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {}
    }

    render() {
        return (this.el(ToolTip, {
            title: this.props.title, content: this.el('i', {
                className: this.props.name + " icon pointer font-size-" + (this.props.size || "3"),
                onClick: this.props.onClick || null
            })
        }));
    }
}

export default Icon;
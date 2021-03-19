import React from 'react';

import './ToolTip.css';
import * as PropTypes from "prop-types";

class ToolTip extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {};
    }

    render() {
        const {title, children, width, content, style} = this.props;
        return (
            <div style={{width: width, ...style}}>
                <div className="tooltip">
                    {children}
                    {content}
                    <div className="tooltiptext tooltip-bottom">
                        {title}
                    </div>
                </div>
            </div>
        );
    }
}

export default ToolTip;

ToolTip.propTypes = {
    title: PropTypes.string,
    width: PropTypes.string,
    style: PropTypes.object
};
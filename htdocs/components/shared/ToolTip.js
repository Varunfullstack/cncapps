import React from 'react';

import './ToolTip.css';

class ToolTip extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {};
    }

    render() {
        const {title, children, width, content} = this.props;
        return (
            <div style={{width: width}}>
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

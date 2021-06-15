import React from "react";
import * as PropTypes from "prop-types";

export class TrueFalseIconComponent extends React.Component {
    render() {
        const {value} = this.props;
        let iconClass = "fa-check";
        if (!value) {
            iconClass = "fa-times";
        }
        return <i className={`fal fa-2x color-gray ${iconClass} `}/>;
    }
}

TrueFalseIconComponent.propTypes = {value: PropTypes.bool};
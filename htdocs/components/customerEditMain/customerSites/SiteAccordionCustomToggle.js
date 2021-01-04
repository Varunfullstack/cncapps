import React from "react";
import {useAccordionToggle} from "react-bootstrap";
import {connect} from "react-redux";
import {toggleEditingSite} from "../actions";

const SiteAccordionCustomToggle = ({children, eventKey, siteId, onToggleEditingSite}) => {
    const decoratedOnClick = useAccordionToggle(eventKey, () => onToggleEditingSite(siteId));
    return (
        <button
            type="button"
            onClick={decoratedOnClick}
        >
            {children}
        </button>
    );
}


const mapDispatchToProps = dispatch => {
    return {
        onToggleEditingSite: (siteNo) => {
            dispatch(toggleEditingSite(siteNo));
        },
    }
}

export default connect(
    null,
    mapDispatchToProps
)(SiteAccordionCustomToggle)
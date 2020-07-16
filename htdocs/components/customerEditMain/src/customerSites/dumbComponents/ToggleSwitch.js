import React from 'react';
import {toggleVisibility} from "../actions";
import {SHOW_ACTIVE} from "../visibilityFilterTypes";
import {connect} from "react-redux";

const ToggleSwitch = ({isChecked, handleChange}) => {
    return (
        <label className="switch">
            <input type="checkbox"
                   onChange={($event) => handleChange($event.target.checked)}
                   checked={isChecked}
            />
            <span className={`slider`}/>
        </label>
    )
}

function mapStateToProps(state) {
    const {visibilityFilter} = state;
    return {
        isChecked: visibilityFilter === SHOW_ACTIVE,
    }
}

function mapDispatchToProps(dispatch) {
    return {
        handleChange: () => {
            dispatch(toggleVisibility())
        }
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ToggleSwitch)
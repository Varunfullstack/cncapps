import React from 'react';
/*
checked, 
onChange, 
name, 
disabled
*/
class Toggle extends React.Component {
    constructor(props) {
        super(props);
        this.state = {}
    }

    render() {
        const {checked, onChange, name, disabled} = this.props;
        return (
            <label className="switch">
                <input type="checkbox"
                       checked={checked}
                       name={name}
                       onChange={onChange}
                       disabled={disabled}
                />
                <span className="slider round"/>
            </label>
        )
    }
}

export default Toggle;
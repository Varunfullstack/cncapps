import React from 'react';
/*
checked, 
onChange, 
name, 
disabled
*/
class Toggle extends React.Component  {
    constructor(props) {
        super(props);
        this.state = {checked:this.props.checked}
    }
    handleOnChange=(event)=>{
        const {onChange}=this.props;
        const {checked}=this.state;
         if(onChange)
            onChange(!checked);
        this.setState({checked:!checked});
        
    }

    render() {
        const {checked,  name, disabled} = this.props;
        return (
            <label className="switch">
                <input type="checkbox"
                       checked={checked}
                       name={name}
                       onChange={this.handleOnChange}
                       disabled={disabled}
                />
                <span className="slider round"/>
            </label>
        )
    }
}

export default Toggle;
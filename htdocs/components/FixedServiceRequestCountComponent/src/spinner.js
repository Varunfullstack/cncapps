import React from 'react'
import './spinner.css';

class Spinner extends React.Component {
    el = React.createElement;

    render() {
        const {el} = this;
        const {show} = this.props;
        if (show)
            return el('div', {className: "loader"},
                el('div', {key: 'loaderContent', className: "loader-content"}));
        else return null;
    }
}

export default Spinner;
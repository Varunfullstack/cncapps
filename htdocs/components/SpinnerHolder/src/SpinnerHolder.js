import React from 'react'
import ReactDOM from 'react-dom';
import Spinner from "../../shared/Spinner/Spinner";

class SpinnerHolder extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            show: false,
        };
        window.spinnerComponent = this;
    }

    componentDidMount() {

    }

    showSpinner() {
        this.setState({show: true});
    }

    hideSpinner() {
        this.setState({show: false});
    }

    render() {
        const {show} = this.state;

        return (
            <React.Fragment>
                <Spinner show={show}/>
            </React.Fragment>

        )
    }
}

export default SpinnerHolder;

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#reactSpinnerHolder');
    if (domContainer) {
        ReactDOM.render(React.createElement(SpinnerHolder), domContainer);
    }

})


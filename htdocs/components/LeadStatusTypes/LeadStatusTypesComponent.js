import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import '../style.css';
import './LeadStatusTypesComponent.css';

class LeadStatusTypesComponent extends MainComponent {
   
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false       
        };
    }

    componentDidMount() {       
    }

    render() {
        return <div>
            <Spinner show={this.state.showSpinner}></Spinner>
           <h2>Welcome</h2>
        </div>;
    }
}

export default LeadStatusTypesComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactLeadStatusTypesComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(LeadStatusTypesComponent), domContainer);
});
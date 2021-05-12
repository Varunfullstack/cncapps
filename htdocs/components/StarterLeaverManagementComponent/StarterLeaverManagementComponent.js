import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import '../style.css';
import './StarterLeaverManagementComponent.css';

class StarterLeaverManagementComponent extends MainComponent {
   
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

export default StarterLeaverManagementComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactStarterLeaverManagementComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(StarterLeaverManagementComponent), domContainer);
});
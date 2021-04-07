import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import '../style.css';
import './SalesRequestComponent.css';
import { CreateSalesRequestComponent } from "./subComponents/CreateSalesRequestComponent.js";

class SalesRequestComponent extends MainComponent {
    
    constructor(props) {
        super(props);
        this.state = {
             
            
        };
    }

    componentDidMount() {  
      
    }
 
    render() {        
        return <div>
            <CreateSalesRequestComponent></CreateSalesRequestComponent>
        </div>;
    }
}

export default SalesRequestComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactSalesRequestComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(SalesRequestComponent), domContainer);
});
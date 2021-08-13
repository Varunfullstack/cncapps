import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import APIStarterLeaverManagement from "./services/APIStarterLeaverManagement.js";
import QuestionDetailsComponent from "./subComponents/QuestionDetailsComponent.js";
import QuestionsComponent from "./subComponents/QuestionsComponent.js";
import CustomerSearch from "../shared/CustomerSearch.js";
import '../style.css';
import './StarterLeaverManagementComponent.css';
class StarterLeaverManagementComponent extends MainComponent {
    api=new APIStarterLeaverManagement();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false,    
            customers:[],
            showAddModal:false,             
            activCustomer:null
        };
    }

    componentDidMount() {     
    }
  
    getCustomersElement=()=>{        
        return <CustomerSearch
        onChange={this.handleOnSelect}
        placeholder="Select Customer"
        >
        </CustomerSearch>         
    }
    handleOnSelect=(customer)=>{
        //console.log(customer);       
        this.setState({activCustomer:customer});

    }
    handleCloseAddModal=()=>{
        this.setState({showAddModal:false});
    }
    handleNewQuestion=()=>{
        if(this.state.activCustomer==null)
            this.alert("Please select customer");
        else
        this.setState({ showAddModal: true });
    }
    render() {
        const {activCustomer}=this.state;
        return (
          <div>
            <Spinner show={this.state.showSpinner}></Spinner>
            {this.getAlert()}
           
            <div className="flex-row">
            {this.getCustomersElement()} 

            </div>
            <br/>
            <span >Change the order of the questions here to change the order in the portal because they are copied from the order below. </span>

            <QuestionsComponent  customer={activCustomer}></QuestionsComponent>
            <QuestionDetailsComponent
              customer={activCustomer}
              onClose={this.handleCloseAddModal}
              show={this.state.showAddModal}
            ></QuestionDetailsComponent>
          </div>
        );
    }
}

export default StarterLeaverManagementComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactStarterLeaverManagementComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(StarterLeaverManagementComponent), domContainer);
});
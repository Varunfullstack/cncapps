import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import '../style.css';
import './StarterLeaverManagementComponent.css';
import APIStarterLeaverManagement from "./services/APIStarterLeaverManagement.js";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import QuestionDetailsComponent from "./subComponents/QuestionDetailsComponent.js";
import QuestionsComponent from "./subComponents/QuestionsComponent.js";
import AutoComplete from "../shared/AutoComplete/autoComplete.js";
import CustomerSearch from "../shared/CustomerSearch.js";
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
        this.getData();  
    }
    getData(){
        this.api.getCustomersHaveQuestions().then(res=>{
            //console.log("customers",res);
            this.setState({"customers":res.data});
        },error=>{
            //console.log(error);
            this.alert("Error in loading customers");
        });
    }
    getCustomersTable(){
        const {customers}=this.state;
        const columns=[
            {
               path: "customerName",
               label: "",
               hdToolTip: "Customer",
               hdClassName: "text-center",
               icon: "fal fa-2x fa-building color-gray2 pointer",
               sortable: true,
               //className: "text-center",    
               classNameColumn:"active"
            },
            {
                path: "edit",
                label: "",
                hdToolTip: "Starter Questions",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-edit color-gray2 pointer",
                sortable: true,
                className: "text-center",         
                content:(customer)=><ToolTip title="Edit Questions">
                                        <i className="fal fa-2x fa-edit color-gray pointer" onClick={()=>this.handleEdit(customer)}></i>
                                    </ToolTip>   ,
                classNameColumn:"active"                 
             },
            // {
            //     path: "starter",
            //     label: "",
            //     hdToolTip: "Starter Questions",
            //     hdClassName: "text-center",
            //     icon: "fal fa-2x fa-hourglass-start color-gray2 pointer",
            //     sortable: true,
            //     className: "text-center",         
            //     content:(customer)=>customer.starter>0?
            //     <ToolTip title="Starter Question">
            //         <i className="fal fa-2x fa-hourglass-start color-gray pointer"></i>
            //     </ToolTip>
            //     :null      
            //  },
            //  {
            //     path: "leaver",
            //     label: "",
            //     hdToolTip: "Leaver Questions",
            //     hdClassName: "text-center",
            //     icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
            //     sortable: true,
            //     className: "text-center",             
            //     content:(customer)=>customer.leaver>0?
            //     <ToolTip title="Leaver Question">
            //     <i className="fal fa-2x fa-hourglass-end color-gray pointer"></i>
            //     </ToolTip>:null   ,
                  
            //  },
        ];
        return <Table        
        pk="customerName"
        columns={columns}
        search={true}
        data={customers}
        >

        </Table>

    }
    getCustomersElement=()=>{
        const {customers}=this.state;
        if(customers.length==0)
        return null;
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
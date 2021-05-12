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

class StarterLeaverManagementComponent extends MainComponent {
    api=new APIStarterLeaverManagement();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false,    
            customers:[],
            showAddModal:false
        };
    }

    componentDidMount() {     
        this.getData();  
    }


    getData(){
        this.api.getCustomersHaveQuestions().then(res=>{
            console.log("customers",res);
            this.setState({"customers":res.data});
        },error=>{
            console.log(error);
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
            },
            {
                path: "starter",
                label: "",
                hdToolTip: "Starter Questions",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hourglass-start color-gray2 pointer",
                sortable: true,
                className: "text-center",         
                content:(customer)=>customer.starter>0?
                <ToolTip title="Starter Question">
                    <i className="fal fa-2x fa-hourglass-start color-gray pointer"></i>
                </ToolTip>
                :null      
             },
             {
                path: "leaver",
                label: "",
                hdToolTip: "Leaver Questions",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
                sortable: true,
                className: "text-center",             
                content:(customer)=>customer.leaver>0?
                <ToolTip title="Leaver Question">
                <i className="fal fa-2x fa-hourglass-end color-gray pointer"></i>
                </ToolTip>:null   ,
                  
             },
        ];
        return <Table        
        pk="customerName"
        columns={columns}
        search={true}
        data={customers}
        >

        </Table>

    }
    handleCloseAddModal=()=>{
        this.setState({showAddModal:false});
    }
    render() {
        return <div>
            <Spinner show={this.state.showSpinner}></Spinner>            
            {this.getAlert()}
            <ToolTip width={30} title="Add New Question">
                <i className="fal fa-2x fa-plus color-gray2 pointer mb-5" onClick={()=>this.setState({showAddModal:true})}></i>
            </ToolTip>
            <div style={{width:500}}>
            {this.getCustomersTable()}
            <QuestionDetailsComponent onClose={this.handleCloseAddModal} show={this.state.showAddModal} ></QuestionDetailsComponent>
            </div>
        </div>;
    }
}

export default StarterLeaverManagementComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactStarterLeaverManagementComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(StarterLeaverManagementComponent), domContainer);
});
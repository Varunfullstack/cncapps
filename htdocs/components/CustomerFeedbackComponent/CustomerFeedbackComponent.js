"use strict";
import ReactDOM from 'react-dom';
import React from 'react';

import Spinner from "../shared/Spinner/Spinner";
import MainComponent from "../shared/MainComponent";
import moment from "moment";
import APICustomerFeedback from './services/APICustomerFeedback';
import '../style.css';
import CustomerSearch from '../shared/CustomerSearch';
import ToolTip from '../shared/ToolTip';
import APIUser from '../services/APIUser';
import Table from '../shared/table/table';
//import './CustomerFeedbackComponent.css';
class CustomerFeedbackComponent extends MainComponent {
    api = new APICustomerFeedback();
    apiUser=new APIUser();
    constructor(props) {
        super(props);
        this.state = {            
            showSpinner: false,    
            users:[],
            filter:{
                from:moment().subtract(1,'m').format('YYYY-MM-DD'),
                to:'',
                customerID:'',
                engineerID:''
            }    
        }
    }
    
    componentDidMount = async () => {
        this.getData();
        this.apiUser.getActiveUsers().then(users=>this.setState({users}));
    }
     
    getData = () => {
        const {filter}=this.state;
      this.api.getCustomerFeedback(filter.from,filter.to,filter.customerID,filter.engineerID).then(feedbacks=>{
          this.setState({feedbacks});
      })
    }
    getSearchElement=()=>{        
        const {filter,users}=this.state;
        return <table>
            <tbody>
                <tr>
                    <td>Customer</td>
                    <td>
                        <CustomerSearch onChange={(customer)=>this.setFilter('customerID',customer.id)}></CustomerSearch>
                    </td>
                </tr>
                <tr>
                    <td>Engineer</td>
                    <td>
                        <select value={filter.engineerID}
                        onChange={(event)=>this.setFilter('engineerID',event.target.value)}
                        >
                            <option></option>
                            {users.map(u=><option key={u.id} value={u.id}>{u.name}</option>)}
                        </select>                    
                    </td>
                </tr>
                <tr>
                    <td>Date From</td>
                    <td><input type="date" value={filter.from} onChange={(event)=>this.setFilter('from',event.target.value)}></input></td>
                </tr>
                <tr>
                    <td>Date To</td>
                    <td><input type="date" value={filter.to} onChange={(event)=>this.setFilter('to',event.target.value)}></input></td>
                </tr>
                <tr>
                    <td></td>
                    <td>
                        <ToolTip title="Search">
                            <i className="fal fa-search fa-2x icon pointer" onClick={()=>this.getData()}></i>
                        </ToolTip>
                    </td>
                </tr>
            </tbody>
        </table>
    }
    getSearchResultElement=()=>{
        const {feedbacks}=this.state;
        const columns=[
            {
                path: "value",
                label: "",
                hdToolTip: "Comments",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-heart color-gray2 pointer",
                sortable: true,
                content:(feed)=>{
                    switch (feed.value) {
                        case 1:
                            return <i className="fal fa-smile fa-2x"></i>
                        case 2:
                            return <i className="fal fa-meh fa-2x "></i>
                        case 3:
                            return <i className="fal fa-frown fa-2x "></i>                    
                        default:
                        return  '';
                    }
                }
                //className: "text-center",
             },
             {
                path: "problemID",
                label: "",
                hdToolTip: "Service Request",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                sortable: true,
                content:(feed)=><a href={`SRActivity.php?action=displayActivity&callActivityID=${feed.problemID}` } target="_blank">{feed.problemID}</a>,
                className: "text-center",
             },
            {
               path: "cus_name",
               label: "",
               hdToolTip: "Customer",
               hdClassName: "text-center",
               icon: "fal fa-2x fa-building color-gray2 pointer",
               sortable: true,
              // className: "text-center",               
            },
            {
                path: "engineer",
                label: "",
                hdToolTip: "Engineer",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-user-hard-hat color-gray2 pointer",
                sortable: true,
                width:120
               // className: "text-center",               
             },
             {
                path: "comments",
                label: "",
                hdToolTip: "Comments",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                sortable: true,
                //className: "text-center",
             },
             {
                path: "createdAt",
                label: "",
                hdToolTip: "Create At",
                //hdClassName: "text-center",
                icon: "fal fa-2x fa-calendar color-gray2 pointer",
                sortable: true,                
                className: "text-center",
             },
        ];
        return <Table id="myfeedback"
                data={feedbacks || []}
                columns={columns}
                pk="id"
                search={true}        
                >
                </Table>
    }
    render() {
        const {minHeight} = this.state;
        return (
            <div id="main-container"
                 style={{minHeight: minHeight, marginBottom: 50}}
            >
                <Spinner show={this.state.showSpinner}></Spinner>                        
                {this.getSearchElement()}
                {this.getSearchResultElement()}
            </div>
        );
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainCustomerFeedback");
    ReactDOM.render(React.createElement(CustomerFeedbackComponent), domContainer);
});

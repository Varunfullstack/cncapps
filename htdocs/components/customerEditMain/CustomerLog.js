"use strict";
import React from 'react';
import APIAudit from '../services/APIAudit';
import Table from '../shared/table/table';
import { params } from '../utils/utils';
import MainComponent from './../shared/MainComponent';
 

class CustomerLog extends MainComponent {
    api=new APIAudit();
    constructor(props) {
        super(props);
        this.state={
            ...this.state,
            logs:[]
        }
    }
    componentDidMount() {
        this.api.getLogs(params.get("customerID")).then(res=>{
            console.log(res);
            if(res.state)
            this.setState({logs:res.data})
        })
    }
    getLogsElement=()=>{
        const columns=[
            {
               path: "pageName",
               label: "Page",
               hdToolTip: "Page",                               
               sortable: true,               
               className:"nowrap"
            },
            {
                path: "userName",
                label: "Consultant",
                hdToolTip: "Consultant",                               
                sortable: true,    
                className:"nowrap"           
             },             
             {
                path: "createAt",
                label: "Date",
                hdToolTip: "Date",                               
                sortable: true,
                content:(log)=>this.getCorrectDate(log.createAt,true),
                className:"nowrap"
             },
             {
                path: "action",
                label: "Action",
                hdToolTip: "Action",                               
                sortable: true,    
                className:"nowrap"           
             },
             {
                path: "oldValues",
                label: "Old Values",
                hdToolTip: "Old Values",                               
                sortable: true,               
             },
             {
                path: "newValues",
                label: "New Values",
                hdToolTip: "New Values",                               
                sortable: true,               
             },
        ];
        return (
            <Table
                key="logs"
                pk="id"                
                columns={columns}
                data={this.state.logs || []}
                search={true}
            ></Table>
        );
    }
    render() {       
        return <div>
            {this.getLogsElement()}
        </div>;        
    }
}

 
export default CustomerLog

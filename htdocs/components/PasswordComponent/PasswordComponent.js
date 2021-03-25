import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom"; 
import Spinner from "../shared/Spinner/Spinner";
import '../style.css';
import './PasswordComponent.css';
import APIPassword from "./services/APIPassword.js";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js"; 
import Toggle from "../shared/Toggle.js";
import CustomerSearch from "../shared/CustomerSearch.js";
import { PasswordDetails } from "./subComponents/PasswordDetails.js";
import { params } from "../utils/utils.js";

class PasswordComponent extends MainComponent {
   api=new APIPassword();
    constructor(props) {
        super(props);
        this.state = {
            ...this.state,    
            showSpinner:false ,
            showModal:false,
            types:[]   ,
            mode:"new"   ,
            data:{ 
               ...this.getInitData()
            },
            filter:{
                customer:null,
                showArchived:false,
                showHigherLevel:false
            },
            passwords:[],
            error:null,
            disabled:false
            
        };
    }

    componentDidMount() {      
        this.checkParams();
        this.getData();
    }
    checkParams=()=>{
        const customerID=params.get("customerID");
        if(customerID&&customerID!='')
        {
            const {filter,data}=this.state;
            data.customerID=customerID;
            filter.customer={
                id:customerID,
                showArchived:false,
                showHigherLevel:false
            }
            this.setState({filter,data,disabled:true});
        }
    }
    getInitData(){
        return {
            URL: '',
            archivedAt: null,
            archivedBy: null,
            customerID: null,
            level: '',
            notes: '',
            password: '',
            passwordID: null,
            serviceID: '',
            serviceName: '',
            sortOrder: '',
            username: '',
        };
    }
    getData=()=>{
        const {filter}=this.state;
         
        if(filter.customer&&filter.customer.id)
        this.api.getAllPasswords(filter.customer.id,filter.showArchived,filter.showHigherLevel)
        .then(res=>{
            if(res.state)
                this.setState({passwords:res.data,error:null});
            else
                this.setState({error:res.error});
            console.log(res);

        });
    }
    copyToClipboard=(item,prop)=>{
        console.log(item,prop);
        const {passwords}=this.state;
        const indx=passwords.map(p=>p.passwordID).indexOf(item.passwordID);
        passwords.map(p=>p.selectedColumn=null);
        passwords[indx].selectedColumn=prop;      
        this.setState({passwords});
      
        const textArea=document.createElement('textarea');
        textArea.value =item[prop];
        textArea.style.top = "0";
        textArea.style.left = "0";
        textArea.style.position = "fixed";
        document.body.appendChild(textArea);
        textArea.focus();
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    }
    getDataTable=()=>{
        const {filter}=this.state;
        let columns=[            
            {
               path: "username",
               label: "User Name",
               hdToolTip: "User Name",
               hdClassName: "text-center",
               //icon: "fal fa-2x fa-text color-gray2 pointer",
               sortable: true,
               content:(item)=><span className={"pointer "+(item.selectedColumn=="username"?"clip-board":"")}  id={item.passwordID+'username'} onClick={()=>this.copyToClipboard(item,"username")}>{item.username}</span>,
               //className: "pointer",                
            },
            {
                path: "serviceName",
                label: "Service",
                hdToolTip: "Service",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,                
                //className: "text-center",                
             },
             {
                path: "password",
                label: "Password",
                hdToolTip: "Password",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,  
                content:(item)=><span className={"pointer "+(item.selectedColumn=="password"?"clip-board":"")}  id={item.passwordID+'username'} onClick={()=>this.copyToClipboard(item,"password")}>{item.password}</span>,
              
                //className: "text-center",                
             },
             {
                path: "notes",
                label: "Notes",
                hdToolTip: "Notes",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,                
                //className: "text-center",                
             },
             {
                path: "URL",
                label: "URL",
                hdToolTip: "URL",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,          
                content:(password)=><a href={password.URL} target="_blank">{password.URL}</a>
                //className: "text-center",                
             },
             {
                path: "level",
                label: "Level",
                hdToolTip: "Level",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-eye color-gray2 pointer",
                sortable: true,          
                
                //className: "text-center",                
             },
            
        ];
        if(filter.showArchived)
        {
            columns = [...columns,
                {
                    path: "archivedBy",
                    label: "Archived By	",
                    hdToolTip: "Archived By	",
                    hdClassName: "text-center",
                    //icon: "fal fa-2x fa-eye color-gray2 pointer",
                    sortable: true,          
                    //content:(password)=><a href={password.URL} target="_blank">{password.URL}</a>
                    //className: "text-center",                
                 },
                 {
                    path: "archivedAt",
                    label: "Archived At",
                    hdToolTip: "Archived At",
                    hdClassName: "text-center",
                    //icon: "fal fa-2x fa-eye color-gray2 pointer",
                    sortable: true,                              
                    //className: "text-center",                
                 },
            ]
        }
        if(!filter.showArchived)
        {
            columns = [...columns,               
             {
                path: "edit",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-edit color-gray2 pointer",
                sortable: false,                
                className: "text-center",   
                content:(type)=> <i className="fal fa-2x fa-edit color-gray pointer" onClick={()=>this.showEditModal(type)}></i>,
             
             },
             {
                path: "archive",
                label: "",
                hdToolTip: "Archive Password",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-archive color-gray2 pointer",
                sortable: false,                
                className: "text-center",   
                content:(type)=> <i className="fal fa-2x fa-archive color-gray pointer" onClick={()=>this.handleDelete(type)}></i>,
             
             }
            ]
        }
        return <Table           
        //style={{ marginTop:20}}
         key="passwords"
        pk="passwordID"
        columns={columns}
        data={this.state.passwords||[]}
        search={true}
        >
        </Table>
    }
    showEditModal=(data)=>{
        console.log(data);
        this.setState({showModal:true,data,mode:'edit'});
        
    }
    handleDelete=async (type)=>{
        console.log(type);
        const conf = await this.confirm("Are you sure to archive this password?");
        if (conf)
          this.api.archivePassword(type.passwordID).then((res) => {
            if (res.state) this.getData();
            else this.alert(res.error);
          });
    }
  
    handleNewType=()=>{
        const {filter}=this.state;
        const data={...this.getInitData()};
        data.customerID=filter.customer.id;
        this.setState({mode:"insert",showModal:true, data});         
    }
    handleCustomerSelect=(customer)=>{
        const {data,filter}=this.state;
        data.customerID=customer.id;
        filter.customer=customer;
        this.setState({data,filter},()=>this.getData())        
    }   
    getFilter=()=>{
        const {filter,error,disabled}=this.state;
        return <div className="flex-row align-center" >
            <CustomerSearch disabled={disabled} customerID={filter?.customer?.id} placeholder="Select Customer" onChange={(customer)=>this.handleCustomerSelect(customer)}></CustomerSearch>
            {!error&&filter.customer?<div>
            <label  className="ml-3 mr-1">Show Archived</label>
            <Toggle checked={filter.showArchived}  onChange={()=>this.setFilter("showArchived",!filter.showArchived,this.getData)}></Toggle>
            <label  className="ml-3 mr-1">Show Higher Level Passwords</label>
            <Toggle checked={filter.showHigherLevel}  onChange={()=>this.setFilter("showHigherLevel",!filter.showHigherLevel,this.getData)}></Toggle>
            </div>:null
            }
        </div>
    }
   handleModalClose=(password)=>{
       console.log("close",password);
    const {passwords}=this.state;
    if(password.passwordID)
    {
        let indx=passwords.map(p=>p.passwordID).indexOf(password.passwordID);
        passwords[indx]= {...password};     
    }
    else this.getData();
    this.setState({showModal:false,passwords,data:password});
   }
    render() {
        const {error,filter,showModal,data,mode}=this.state;
        console.log('data',data);
        return <div className="flex-1">
            <Spinner show={this.state.showSpinner}></Spinner>
            
            {this.getConfirm()}
            {this.getAlert()}           
            {this.getFilter()}
            {
            !error&&filter.customer?<ToolTip title="New Type" width={30}>
                <i className="fal fa-2x fa-plus color-gray1 pointer" onClick={this.handleNewType}></i>
            </ToolTip>:null            
            }            
           {!error&&filter.customer?this.getDataTable():null}
           {error?<h2 style={{color:"red"}}>{error}</h2>:null}
           <PasswordDetails  onClose={ this.handleModalClose}  show={showModal} data={data} filter={filter} mode={mode}></PasswordDetails>

        </div>;
    }
}

export default PasswordComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactPasswordComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(PasswordComponent), domContainer);
});
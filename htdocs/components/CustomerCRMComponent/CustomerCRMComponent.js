import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import "../style.css";
import "./CustomerCRMComponent.css";
import APILeadStatusTypes from "./../LeadStatusTypes/services/APILeadStatusTypes";
import { sort } from "../utils/utils.js";
import CustomerSearch from './../shared/CustomerSearch';
import APICustomers from "../services/APICustomers.js";
import Table from "../shared/table/table.js";
import Spinner from "../shared/Spinner/Spinner.js";

class CustomerCRMComponent extends MainComponent {
  apiLeadStatus = new APILeadStatusTypes();
  apiCustomer=new APICustomers();
  scrollTimer;
  searchTimer;
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      leadStatus: [],
      filter:{
          leadStatusId:"",
          customerID:null,
          page:1,
          q:""
      },
      customers:[],
      customersFiltered:[]
    };
  }

  componentDidMount() {
    window.addEventListener('scroll', this.handleScroll, true);

    this.apiLeadStatus.getAllTypes().then((leadStatus) => {
      console.log(sort(leadStatus, "name"));
      this.setState({ leadStatus});
    });
  }

  componentWillUnmount() {
    window.removeEventListener('scroll', this.handleScroll);
  }

  handleCustomerSelect=(customer)=>{
    //console.log(customer);
    this.setFilter("customerID",customer.id)
    window.open(`Customer.php?action=dispEdit&customerID=${customer.id}&activeTab=crm`,"_blank");
    this.getData();
  }

  handleLeadSelect=(value)=>{
      const {filter}=this.state;
      filter.page=1;
      this.setFilter("leadStatusId", value,this.getData);   
  }
  getData=()=>{
    const {filter}=this.state;
    this.setState({showSpinner:true})
    this.apiCustomer.getCustomersByLeadStatus(filter.leadStatusId,filter.customerID).then(customers=>{
      //console.log(customers);
      this.setState({customers,showSpinner:false,customersFiltered:[...customers] })
  });
  }
  getCustomerTable=()=>{
    const {customersFiltered} =this.state;
    const columns=[
        {
           path: "customerName",
           label: "",
           hdToolTip: "Customer",          
           icon: "fal fa-2x fa-building color-gray2 pointer",
           sortable: true,           
           content:(customer)=><a href={`Customer.php?action=dispEdit&customerID=${customer.customerID}&activeTab=crm`} target="_blank">{customer.customerName}</a>
        },
        {
            path: "contactPhone",
            label: "",
            hdToolTip: "Phone",          
            icon: "fal fa-2x fa-phone color-gray2 pointer",
            sortable: true,     
            content:contact=><a href={`tel:${contact.contactPhone}`}>{contact.contactPhone}</a>
      
        },
        {
            path: "contactName",
            label: "",
            hdToolTip: "Contact",          
            icon: "fal fa-2x fa-id-card-alt color-gray2 pointer",
            sortable: true,           
        },
        {
            path: "jobTitle",
            label: "",
            hdToolTip: "Job Title",          
            icon: "fal fa-2x fa-id-card color-gray2 pointer",
            sortable: true,           
        },
        {
            path: "customerReviewDate",
            label: "",
            hdToolTip: "Customer Review Date",          
            icon: "fal fa-2x fa-calendar color-gray2 pointer",
            sortable: true,           
            content:(customer)=>this.getCorrectDate(customer.customerReviewDate)
        },
        {
            path: "bluestoneLeadStatus",
            label: "",
            hdToolTip: "LeadS tatus",          
            icon: "fal fa-2x fa-thermometer-full color-gray2 pointer",
            sortable: true,           
        },
    ];
    return <Table         
        style={{width:1200,marginTop:20}}
    
        
        key="customers"
        pk="customerID"
        columns={columns}
        data={(customersFiltered||[]).slice(0,this.state.filter.page*50)}
        search={false}
        onSearch={this.handleSearch}
        >
        </Table>
  }
  handleSearch=(q)=>{
      const {filter}=this.state;
      filter.q=q;
      filter.page=1;
      this.setState({filter})
      if(this.searchTimer)  
        clearTimeout(this.searchTimer);
        else
        setTimeout(()=>{
            const smallQ=q.toLocaleLowerCase();
            this.setState({
              filter,
              customersFiltered:
              this.state.customers.filter(c=>{
                  if(
                      c.customerName.toLocaleLowerCase().indexOf(smallQ)>=0||
                      (c.contactPhone!=null && c.contactPhone.toString().indexOf(smallQ)>=0)||
                      (c.contactName!=null && c.contactName.toLocaleLowerCase().indexOf(smallQ)>=0)||
                      (c.jobTitle!=null&&c.jobTitle.toLocaleLowerCase().indexOf(smallQ)>=0)||
                      (c.customerReviewDate !=null && c.customerReviewDate.toLocaleLowerCase().indexOf(smallQ)>=0)||
                      (c.bluestoneLeadStatus !=null &&c.bluestoneLeadStatus.toLocaleLowerCase().indexOf(smallQ)>=0)
                  )
                  return true;
                  return false;
              })
            })
        },500)
     
  }
  handleScroll = (event) => {
    const {filter} = this.state;
    let scrollTop = window.scrollY;
    let docHeight = document.body.offsetHeight;
    let winHeight = window.innerHeight;
    let scrollPercent = scrollTop / (docHeight - winHeight);
    let scrollPercentRounded = Math.round(scrollPercent * 100);
    if (scrollPercentRounded > 70) {
        if (this.scrollTimer) clearTimeout(this.scrollTimer);
        this.scrollTimer = setTimeout(() => {
            filter.page++;
            this.setState({filter, reset: false});
        }, 500);
    }
}
  render() {
    const { leadStatus,filter ,showSpinner} = this.state;
     
    return (
      <div>
        <div style={{ maxWidth: 300 }}>
          <table>
            <tbody>
              <tr>
                <td>Customer</td>
                <td>                  
                  <CustomerSearch
                    onChange={this.handleCustomerSelect}
                  ></CustomerSearch>
                </td>
              </tr>
              <tr>
                <td className="nowrap">Lead Status</td>
                <td>
                  <select
                    className="form-control"
                    value={filter.leadStatusId}
                    onChange={($event) =>
                      this.handleLeadSelect($event.target.value)
                    }
                  >
                    <option value="">All</option>
                    {leadStatus.map((status) => (
                      <option key={status.id} value={status.id}>
                        {status.name}
                      </option>
                    ))}
                  </select>
                </td>
              </tr>
              <tr>
                <td>Search</td>
                <td>                  
                  <input className="form-control" value={filter.q} onChange={($event)=>this.handleSearch($event.target.value)}></input>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <Spinner show={showSpinner}></Spinner>
        {!showSpinner?this.getCustomerTable():null}
      </div>
    );
  }
}

export default CustomerCRMComponent;
document.addEventListener("DOMContentLoaded", () => {
  const domContainer = document.querySelector("#reactCustomerCRMComponent");
  if (domContainer)
    ReactDOM.render(React.createElement(CustomerCRMComponent), domContainer);
});

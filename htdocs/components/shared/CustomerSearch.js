import APICustomers from "../services/APICutsomer.js";
import MainComponent from "../shared/MainComponent.js";
import AutoComplete from "./AutoComplete/autoComplete.js";
import React from "react";
import ReactDOM from "react-dom";
class CustomerSearch extends MainComponent {
  el=React.createElement;
  apiCustomer = new APICustomers();
  constructor(props) {
    super(props);
    this.state = { customers:[] }
   }
   componentDidMount() {
       
       this.apiCustomer.getCustomers().then(customers=>this.setState({customers}))
   }
   handleOnCustomerSelect=(value)=>{
       console.log(value);
       if(this.props.onChange)
        this.props.onChange(value)
   }
   render() { 
     return this.el(AutoComplete, {
        errorMessage: "No Customer found",
        items: this.state.customers,
        displayLength: "40",
        displayColumn: "name",
        pk: "id",
        width: this.props.width||300,
        onSelect: this.handleOnCustomerSelect,
    });
   }
}
export default CustomerSearch;
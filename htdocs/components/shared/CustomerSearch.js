import APICustomers from "../services/APICustomers.js";
import MainComponent from "../shared/MainComponent.js";
import AutoComplete from "./AutoComplete/autoComplete.js";
import React from "react";

class CustomerSearch extends MainComponent {
    el = React.createElement;
    apiCustomer = new APICustomers();

    constructor(props) {
        super(props);
        this.state = {customers: []}
    }

    componentDidMount() {

        this.apiCustomer.getCustomers().then(customers => this.setState({customers}))
    }

    handleOnCustomerSelect = (value) => {
        if (this.props.onChange)
            this.props.onChange(value)
    }
    getCustomerName=()=>{
        const {customers}=this.state;
        
        let customerName=this.props.customerName;
        if(this.props.customerID)
        {
            const customer=customers.find(c=>c.id==this.props.customerID);
            if(customer)
            customerName=customer.name;
        }
        console.log(customerName);
        return customerName;
    }
    render() {        
        return this.el(AutoComplete, {
            errorMessage: "No Customer found",
            items: this.state.customers,
            displayLength: "40",
            displayColumn: "name",
            pk: "id",
            width: this.props.width || 300,
            value:this.getCustomerName(),
            onSelect: this.handleOnCustomerSelect,
        });
    }
}

export default CustomerSearch;
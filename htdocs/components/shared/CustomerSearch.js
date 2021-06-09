import APICustomers from "../services/APICustomers.js";
import MainComponent from "../shared/MainComponent.js";
import AutoComplete from "./AutoComplete/autoComplete.js";
import React from "react";
import * as PropTypes from "prop-types";
import {AdditionalChargeRateList} from "../AdditionalChargeRateWrapperComponent/AdditionalChargeRateComponent/subComponents/AdditionalChargeRateList";

/*
onChange:Callback
placeholder
*/
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
    getCustomerName = () => {
        const {customers} = this.state;

        let customerName = this.props.customerName;
        if (this.props.customerID) {
            const customer = customers.find(c => c.id == this.props.customerID);
            if (customer)
                customerName = customer.name;
        }
        return customerName;
    }

    render() {
        return <AutoComplete
            errorMessage="No Customer found"
            disabled={this.props.disabled || false}
            items={this.state.customers}
            displayLength="40"
            displayColumn="name"
            pk="id"
            width={this.props.width || 300}
            value={this.getCustomerName()}
            onSelect={this.handleOnCustomerSelect}
            placeholder={this.props.placeholder}/>

    }
}

export default CustomerSearch;

CustomerSearch.propTypes = {
    disabled: PropTypes.bool,
    width: PropTypes.number,
    placeholder: PropTypes.string,
    onChange: PropTypes.func,
    customerID: PropTypes.oneOfType([PropTypes.number, PropTypes.string]),
    customerName: PropTypes.string
};
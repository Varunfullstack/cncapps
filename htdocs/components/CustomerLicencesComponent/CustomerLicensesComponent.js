"use strict";
import APICustomerLicenses from "./subComponents/APICustomerLicenses";
import TDCustomerSearchComponent from "./subComponents/TDCustomerSearchComponent";
import TDCustomerDetailsComponent from "./subComponents/TDCustomerDetailsComponent";
import TDCustomerOrdersComponent from "./subComponents/TDCustomerOrdersComponent";
import TDOrderDetailsComponent from "./subComponents/TDOrderDetailsComponent";
import NewOrder from "./subComponents/NewOrderComponent";
import StreamOneService from "./subComponents/StreamOneService";
import Spinner from '../shared/Spinner/Spinner';
import React from 'react';
import ReactDOM from 'react-dom';

/**
 * Don't forget to change v value in js import before push to gethub to avoid cache problem
 */
class CustomerLicensesComponent extends React.Component {
    el = React.createElement;
    apiCustomerLicenses;
    apiTechData;
    streamOneService;

    /**
     * init state
     * @param {*} props
     */
    constructor(props) {
        super(props);
        this.state = {orders: null, _showSpinner: false};
        this.apiCustomerLicenses = new APICustomerLicenses();
        this.streamOneService = new StreamOneService();
        this.loadComponents();
    }

    loadComponents = async () => {
    };

    async componentDidMount() {
        // this.apiCustomerLicenses.getProductsPrices({
        //   "vendorIds" : [397],
        //   "lines":
        //     [
        //       {
        //       "sku":"SK4665",
        //       "quantity":1
        //       },
        //       {
        //       "sku":"SK4663",
        //       "quantity":1
        //       }
        //     ],
        //   "page": 1
        //   }).then(result=>{
        //   console.log(result);
        // })
        //  this.apiCustomerLicenses.getProductList(3).then(res=>{
        //    console.log(res);
        //  })
        // load all subscriptions
        // get first page to know total pages
        this.showSpinner();
        let customers = await this.apiCustomerLicenses.getStreamOneCustomersLocal();
        console.log(customers);
        customers = customers.map(c => {
            c.name = c.name.replace("  ", " ");
            c.firstName = c.name.split(' ')[0];
            c.lastName = c.name.split(' ')[1];
            return c;
        })
        this.setState({customers});
        // const orders = await this.streamOneService.fetchAllOrders();
        // console.log("all subscriptions", orders);
        // this.setState({ orders });
        this.hideSpinner();
        // console.log(
        //   "email orders",
        //   this.streamOneService.getOrdersByEmail("mark.perress@ajmhealthcare.org")
        // );
    }

    handleAddCustomer = () => {
        console.log("add customer");
        window.location = "/CustomerLicenses.php?action=addNewCustomer";
    };
    showSpinner = () => {
        this.setState({_showSpinner: true});
    };
    hideSpinner = () => {
        this.setState({_showSpinner: false});
    };

    render() {
        const {el, handleAddCustomer} = this;
        const queryParams = new URLSearchParams(window.location.search);
        const action = queryParams.get("action");
        const {customers, _showSpinner} = this.state;
        if (customers != null) {
            switch (action) {
                case "searchCustomers":
                    return el(TDCustomerSearchComponent, {
                        onAddNew: handleAddCustomer,
                        customers,
                    });
                case "searchOrders":
                    return el(TDCustomerOrdersComponent, {customers});
                case "addNewCustomer":
                    return el(TDCustomerDetailsComponent, {onAddNew: handleAddCustomer, customers});
                case "editCustomer":
                    return el(TDCustomerDetailsComponent, {
                        onAddNew: handleAddCustomer,
                        customerId: queryParams.get("endCustomerId"),
                        customers
                    });
                case "newOrder":
                    return el(NewOrder, {customers});
                case "editOrder":
                    return el(TDOrderDetailsComponent, null);
                default:
                    return el("h1", null, "TechData page");
            }
        } else
            return [
                el(Spinner, {key: "spinner", show: _showSpinner}),
                el(
                    "h1",
                    {key: "loading"},
                    "Please wait until loading data from StreamOne... "
                ),
            ];
    }
}

export default CustomerLicensesComponent;

document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.getElementById("reactMainCustomerLicenses");
    ReactDOM.render(React.createElement(CustomerLicensesComponent), domContainer);
})


import Table from "./../../shared/table/table";
import Spinner from "../../shared/Spinner/Spinner";
import APICustomers from "../../services/APICutsomer";
import React from 'react';
class CustomerSearchComponent extends React.Component {
    el = React.createElement;
    apiCutsomer = new APICustomers();
    delayTimer;

    constructor(props) {
        super(props);
        const {customer} = this.props.data;
        let value = "";
        if (customer)
            value = customer.cus_name;
        this.state = {
            _showSpinner: false,
            searchValue: value,
            customers: [],

        };
    }

    showSpinner = () => {
        this.setState({_showSpinner: true});
    }
    hideSpinner = () => {
        this.setState({_showSpinner: false});
    }
    handleCustomerSearch = (event) => {
        this.setState({searchValue: event.target.value})
        clearTimeout(this.delayTimer);
        event.persist();
        this.delayTimer = setTimeout(() => {
            this.showSpinner();
            this.apiCutsomer.searchCustomers(event.target.value)
                .then(customers => {
                    return customers.map(c => {
                        if (c.supportLevel == 'main')
                            c.color = 'red';
                        return c;
                    });
                })
                .then(result => {
                    this.setState({customers: result, _showSpinner: false});
                });
        }, 1000);
    }
    getSearchElement = () => {
        const {el, handleCustomerSearch} = this;
        const {searchValue} = this.state;
        return el("input", {
            placeholder: "Search Customers or Contacts",
            value: searchValue,
            style: {width: 300, marginBottom: 10},
            onChange: handleCustomerSearch
        });
    };
    handleCustomerSelect = (customer) => {
        //
        //const projects= await ;
        if (this.props.updateSRData)
            this.props.updateSRData({customer, customerID: customer.cus_custno, nextStep: 2});
        this.apiCutsomer.getCustomerProjects(customer.cus_custno).then(projects => {
            if (this.props.updateSRData)
                this.props.updateSRData({projects});
        })

    }
    getCustomersElement = () => {
        const {el, handleCustomerSelect} = this;
        const {customers} = this.state;
        let columns = [
            {
                hide: false,
                order: 1,
                path: null,
                label: "",
                sortable: false,
                textColorColumn: "color",
                content: (customer) =>
                    customer.specialAttentionContact == '1' || customer.specialAttentionCustomer == 'Y' ?
                        el(
                            "i",
                            {
                                className: "fal fa-2x fa-star color-gray2",
                            }
                        ) : null,
            },
            {
                hide: false,
                order: 1.1,
                path: "cus_name",
                label: "",
                hdToolTip: "Customer",
                icon: "fal fa-2x fa-building color-gray2 ",
                sortable: false,
                width: "220",
                hdClassName: "text-center",
                textColorColumn: "color",
                content: (customer) =>
                    el(
                        "label",
                        {
                            className: "pointer",
                            onClick: () => handleCustomerSelect(customer)
                        },
                        customer.cus_name
                    ),
            },
            {
                hide: false,
                order: 2,
                path: "site_name",
                label: "",
                hdToolTip: "Customer Site",
                icon: "fal fa-2x  fa-location color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                textColorColumn: "color",
                content: (customer) =>
                    el(
                        "label",
                        {
                            className: "pointer",
                            onClick: () => handleCustomerSelect(customer)
                        },
                        customer.site_name
                    ),
            },
            {
                hide: false,
                order: 4,
                path: "contact_name",
                label: "",
                hdToolTip: "Contact",
                icon: "fal fa-2x  fa-user color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                textColorColumn: "color",
                content: (customer) =>
                    el(
                        "label",
                        {
                            className: "pointer",
                            onClick: () => handleCustomerSelect(customer)
                        },
                        customer.contact_name
                    ),
            },
            {
                hide: false,
                order: 5,
                path: "con_position",
                label: "",
                hdToolTip: "Position",
                icon: "fal fa-2x  fa-id-card-alt color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                textColorColumn: "color",
                content: (customer) =>
                    el(
                        "label",
                        {
                            className: "pointer",
                            onClick: () => handleCustomerSelect(customer)
                        },
                        customer.con_position
                    ),
            },
            {
                hide: false,
                order: 6,
                path: "supportLevel",
                label: "",
                hdToolTip: "Support Level",
                icon: "fal fa-2x fa-layer-group color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                textColorColumn: "color",
                content: (customer) =>
                    el(
                        "label",
                        {
                            className: "pointer",
                            onClick: () => handleCustomerSelect(customer)
                        },
                        customer.supportLevel
                    ),
            },
            {
                hide: false,
                order: 7,
                path: "con_phone",
                label: "",
                hdToolTip: "Contact Phone",
                icon: "fal fa-2x fa-phone color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                textColorColumn: "color",
                content: (customer) =>
                    el(
                        "label",
                        {
                            className: "pointer",
                            onClick: () => handleCustomerSelect(customer)
                        },
                        customer.con_phone
                    ),
            },
            {
                hide: false,
                order: 9,
                path: "con_notes",
                label: "",
                hdToolTip: "Notes",
                icon: "fal fa-2x fa-file-alt color-gray2 ",
                sortable: false,
                hdClassName: "text-center",
                textColorColumn: "color",
                content: (customer) =>
                    el(
                        "label",
                        {
                            className: "pointer",
                            onClick: () => handleCustomerSelect(customer)
                        },
                        customer.con_notes
                    ),
            },
        ];
        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));

        return el(Table, {
            id: "customers",
            data: customers || [],
            columns: columns,
            pk: "con_contno",
            search: false,
        });
    };

    render() {
        const {el, getSearchElement, getCustomersElement} = this;
        const {_showSpinner} = this.state;
        return el("div", null,
            el(Spinner, {show: _showSpinner}),
            getSearchElement(),
            getCustomersElement());
    }
}

export default CustomerSearchComponent;
"use strict";
import Table from '../../shared/table/table';
import Spinner from '../../shared/Spinner/Spinner';
import ToolTip from "../../shared/ToolTip";
import APICustomerLicenses from "./APICustomerLicenses.js";
import React from 'react';

/**import
 * searching in TechData customers and link them with CNC customers
 */
class TDCustomerSearchComponent extends React.Component {
    el = React.createElement;
    apiCustomerLicenses;
    apiTechData;

    /**
     * init state
     * @param {*} props
     */
    constructor(props) {
        super(props);
        this.state = {
            customers: this.props.customers,
            filteredResult: this.props.customers,
            _showSpinner: false,
        };
        this.apiCustomerLicenses = new APICustomerLicenses();
    }

    showSpinner = () => {
        this.setState({_showSpinner: true});
    };
    hideSpinner = () => {
        this.setState({_showSpinner: false});
    };

    handleChange = ({currentTarget: input}) => {
        const {customers} = this.state;
        if (customers.length > 0 && input.value.length > 0) {
            const filteredResult = customers.filter((c) => {
                return (
                    (c.name)
                        .toLowerCase()
                        .indexOf(input.value.toLowerCase()) >= 0 ||
                    c.companyName.toLowerCase().indexOf(input.value.toLowerCase()) >= 0 ||
                    c.email.toLowerCase().indexOf(input.value.toLowerCase()) >= 0 ||
                    (c.cncCustName != null &&
                        c.cncCustName
                            .toLowerCase()
                            .indexOf(input.value.toLowerCase()) >= 0)
                );
            });
            this.setState({filteredResult});
        } else this.setState({filteredResult: [...customers]});
    };

    getSearchElement(label, name) {
        const {el, handleChange} = this;
        return el(React.Fragment, {key: "frag" + name}, [
            el("td", {key: "td" + name, className: "text-right nowrap"}, label),
            el(
                "td",
                {key: `td${name}Input`},
                el("input", {
                    key: name,
                    name: name,
                    type: "search",
                    className: "form-control",
                    onChange: handleChange,
                })
            ),
        ]);
    }

    getSearchElements() {
        const {el, handleAddNew} = this;
        return el(
            "table",
            {key: "table", style: {maxWidth: 1000}},
            el(
                "tbody",
                null,
                el("tr", null, [
                    this.getSearchElement("Search", "name"),
                    el(
                        "td",
                        {key: "tdNew", className: "col"},
                        el(ToolTip, {
                            title: "Add New Customer",
                            content: el("i", {
                                key: "NewCustomer",
                                onClick: handleAddNew,
                                className: "fal fa-plus fa-2x pointer",
                            }),
                        })
                    ),
                ])
            )
        );
    }

    handleAddNew = () => {
        if (this.props.onAddNew) this.props.onAddNew();
    };
    handleEdit = (customer) => {

        window.location =
            "/CustomerLicenses.php?action=editCustomer&endCustomerId=" +
            customer.endCustomerId;
    };
    handleSaas = (customer) => {
        window.location = `/CustomerLicenses.php?action=searchOrders&email=${customer.email}&tap=saas`;
    };
    getSearchResult = () => {
        const {filteredResult} = this.state;
        const {el, handleEdit, handleSaas} = this;

        const columns = [
            {path: "companyName", label: "StreamOne Company Name", sortable: true},

            {
                path: "cncCustName", label: "CNC Customer", sortable: true, sortFn: (direction) => (a, b) => {
                    if (!a.cncCustName) {
                        return 1;
                    }
                    if (!b.cncCustName) {
                        return -1;
                    }
                    if (direction == 'asc') {
                        return a.cncCustName.localeCompare(b.cncCustName);
                    }
                    return b.cncCustName.localeCompare(a.cncCustName);
                }
            },
            {path: "name", label: "Contact Name", sortable: true,},
            {path: "email", label: "Email", sortable: true},
            {
                path: null,
                label: "Edit Company",
                sortable: false,
                content: (c) => c.endCustomerId != null ?
                    el(ToolTip, {
                        title: "Edit customer details",
                        content: el("i", {onClick: () => handleEdit(c), className: 'pointer fal fa-edit'})
                    })
                    : null
            },
            {
                path: null,
                label: "Edit Licenses",
                sortable: false,
                content: (c) =>
                    el(ToolTip, {
                        title: "Edit customer licences", content:
                            el("i", {onClick: () => handleSaas(c), className: 'pointer fal fa-edit'})
                    })
                ,
            },
        ];
        {
            return this.el(Table, {
                id: "reaulttable",
                data: filteredResult || [],
                columns: columns,
                defaultSortPath: "cncCustName",
                defaultSortOrder: "asc",
                key: 'result-table',
                pk: "email",
            });
        }
    };

    render() {
        const {el} = this;
        const {_showSpinner} = this.state;

        return el("div", null, [
            el(Spinner, {key: "spinner", show: _showSpinner}),
            this.getSearchElements(),
            this.getSearchResult(),
        ]);
    }
}

export default TDCustomerSearchComponent;

"use strict";
import {Cities} from '../../utils/ukCities';
import Spinner from '../../shared/Spinner/Spinner';
import ToolTip from "../../shared/ToolTip";
import APICustomerLicenses from "./APICustomerLicenses";
import AutoComplete from "../../shared/AutoComplete/autoComplete";
import React from 'react';
import CustomerSearch from "../../shared/CustomerSearch";

/**
 *  Edit TechData customers and link them with CNC customers
 */
class TDCustomerDetailsComponent extends React.Component {
    el = React.createElement;
    apiCustomerLicenses;

    /**
     * init state
     * @param {*} props
     */
    constructor(props) {
        super(props);
        this.state = {
            _showSpinner: false,
            mode: "insert",
            cncCustomers: [],
            data: {
                companyName: "",
                firstName: "",
                lastName: "",
                title: "",
                email: "",
                phone1: "",
                phone2: "",
                addressLine1: "",
                addressLine2: "",
                city: "",
                country: "GB",
                state: "",
                postalCode: "",
                cncCustName: "",
                cncCustomerId: "",
            },
            errors: {},
        };
        this.apiCustomerLicenses = new APICustomerLicenses();
    }

    componentDidMount() {
        const {customers} = this.props;
        const queryParams = new URLSearchParams(window.location.search);
        const endCustomerId = queryParams.get("endCustomerId");
        const result = customers.filter(c => c.endCustomerId == endCustomerId)
        if (result.length > 0) {
            let data = result[0];
            data.firstName = data.name.split(' ')[0];
            data.lastName = data.name.split(' ')[1];
            data.newCustomerId = null;
            this.setState({data: result[0], mode: "edit"});

        }
        this.apiCustomerLicenses
            .getCustomers()
            .then((cncCustomers) => this.setState({cncCustomers}));

    }

    showSpinner = () => {
        this.setState({_showSpinner: true});
    };
    hideSpinner = () => {
        this.setState({_showSpinner: false});
    };
    handleChange = ({currentTarget: input}) => {
        const data = {...this.state.data};
        data[input.name] = input.value;
        this.setState({data});
    };

    getCustomerElement(
        label,
        name,
        content = null,
        value = "",
        required = true,
        errorMessage = "",
        disabled = false
    ) {
        const {el, handleChange} = this;
        return el("tr", {key: "tr" + name}, [
            el("td", {key: "td" + name, className: "text-right nowrap", style: {width: 100}}, label),
            el(
                "td",
                {key: `td${name}Input`},
                content
                    ? content
                    : el("input", {
                        key: name,
                        name: name,
                        type: "text",
                        className: "form-control " + (required ? "required" : ""),
                        onChange: handleChange,
                        value,
                        disabled: disabled ? 'disabled' : ''
                    })
            ),
            el(
                "td",
                {key: "tdError" + name},
                el(
                    "span",
                    {key: "error" + name, className: "error-message"},
                    errorMessage
                )
            ),
        ]);
    }

    getCustomerElements() {
        const {
            el,
            handleCncCustomerOnSelect,
            handleCityOnSelect,
            handleOnSave,
            handleOnCancel
        } = this;
        const {cncCustomers, data, errors, mode} = this.state;
        let errorMessage = "";
        if (typeof errors == "string") errorMessage = errors;
        return el(
            "table",
            {key: "table", style: {width: 500}},
            el("tbody", null, [
                this.getCustomerElement(
                    "Company Name",
                    "companyName",
                    null,
                    data.companyName,
                    true,
                    errors["companyName"]
                ),
                this.getCustomerElement(
                    "First Name",
                    "firstName",
                    null,
                    data.firstName,
                    true,
                    errors["firstName"]
                ),
                this.getCustomerElement(
                    "Last Name",
                    "lastName",
                    null,
                    data.lastName,
                    true,
                    errors["lastName"]
                ),
                this.getCustomerElement(
                    "Title",
                    "title",
                    null,
                    data.title || "",
                    false,
                    errors["title"]
                ),
                this.getCustomerElement(
                    "Email",
                    "email",
                    null,
                    data.email,
                    true,
                    errors["email"],
                    mode == "edit"
                ),
                this.getCustomerElement(
                    "Phone 1",
                    "phone1",
                    null,
                    data.phone1 || "",
                    true,
                    errors["phone1"]
                ),
                this.getCustomerElement(
                    "Phone 2",
                    "phone2",
                    null,
                    data.phone2 || "",
                    false,
                    errors["phone2"]
                ),
                this.getCustomerElement(
                    "Address Line 1",
                    "addressLine1",
                    null,
                    data.addressLine1 || "",
                    true,
                    errors["addressLine1"]
                ),
                this.getCustomerElement(
                    "Address Line 2",
                    "addressLine2",
                    null,
                    data.addressLine2 || "",
                    false,
                    errors["addressLine2"]
                ),
                this.getCustomerElement(
                    "City",
                    "city",
                    el(AutoComplete, {
                        key: "cityAuto",
                        errorMessage: "No City Found",
                        items: Cities,
                        displayLength: "40",
                        displayColumn: "name",
                        pk: "id",
                        value: data.city || "",
                        onSelect: handleCityOnSelect,
                    }),
                    null,
                    true,
                    errors["city"]
                ),
                this.getCustomerElement(
                    "State",
                    "state",
                    null,
                    data.state || "",
                    false,
                    errors["state"]
                ),
                this.getCustomerElement(
                    "Country",
                    "country",
                    null,
                    data.country || "GB",
                    true,
                    errors["country"]
                ),
                this.getCustomerElement(
                    "Postal Code",
                    "postalCode",
                    null,
                    data.postalCode || "",
                    true,
                    errors["postalCode"]
                ),
                this.getCustomerElement(
                    "CNC Customer",
                    "cncCustomerId",
                    <CustomerSearch
                        customerID={data.cncCustomerId}
                        customerName={data.cncCustName}
                        onChange={handleCncCustomerOnSelect}
                    />
                ),
                el(
                    "tr",
                    {key: "trError"},
                    el(
                        "td",
                        {key: "tdError", colSpan: 3},
                        el(
                            "span",
                            {key: "spanerror", className: "error-message"},
                            errorMessage
                        )
                    )
                ),
                el(
                    "tr",
                    {key: "trSave"},
                    el(
                        "td",
                        {key: "tdSave", colSpan: 2},
                        el('div', {style: {display: "flex", flexDirection: "row", width: 70}},
                            el(ToolTip, {
                                title: "Save",
                                content: el("i", {
                                    key: "btnSave",
                                    onClick: handleOnSave,
                                    className: "fal fa-save fa-2x m-5 icon pointer"
                                })
                            }),
                            el(ToolTip, {
                                title: "Cancel",
                                content: el("i", {
                                    key: "btnCancel",
                                    onClick: handleOnCancel,
                                    className: "fal fa-window-close fa-2x m-5 icon pointer"
                                })
                            })
                        )
                    )
                ),
            ])
        );
    }

    handleCncCustomerOnSelect = (event) => {
        const data = {...this.state.data};
        if (event != null) {
            data.newCustomerId = event.id;
            data.cncCustName = event.name;

        } else {
            data.newCustomerId = null;
            data.cncCustName = "";
        }
        this.setState({data});
    };
    handleCityOnSelect = (event) => {
        const data = {...this.state.data};

        if (event != null) {
            data.city = event.name;

        } else {
            data.city = null;
        }
        this.setState({data});
    };
    handleOnSave = () => {

        const {data, mode} = this.state;

        this.showSpinner();

        if (mode == "insert") {
            this.apiCustomerLicenses
                .addTechDataCustomer(data)
                .then((result) => {

                    this.hideSpinner();
                    if (result.Result == "Failed") {
                        const errors = result.ErrorMessage;
                        this.setState({errors});
                    }
                });
        } else if (mode == "edit") {
            this.apiCustomerLicenses
                .updateTechDataCustomer(data.endCustomerId, data)
                .then((result) => {
                    this.hideSpinner();

                    if (result.Result == "Failed") {
                        const errors = result.ErrorMessage;
                        this.setState({errors});
                    } else if (result.Result == "Success") {
                        this.handleOnCancel();
                    }
                });
        }
    };

    getCustomerDetails = (endCustomerId) => {
        this.apiCustomerLicenses.getCustomerDetails(endCustomerId).then((res) => {

            if (res.Result == "Success") {
                const data = {...res.BodyText.endCustomerDetails};
                this.setState({data, mode: "edit"});
            }
        });
    };
    handleOnCancel = () => {
        window.location = '/CustomerLicenses.php?action=searchCustomers'

    }

    render() {
        const {el} = this;
        const {_showSpinner} = this.state;
        return [el(Spinner, {
            key: "spinner",
            show: _showSpinner
        }), el("div", {key: "divContent"}, [this.getCustomerElements()])];
    }
}

export default TDCustomerDetailsComponent;

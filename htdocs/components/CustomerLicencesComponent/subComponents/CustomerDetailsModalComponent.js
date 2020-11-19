import Modal from "./../../shared/Modal/modal";
import {Cities} from "../../utils/ukCities";
import AutoComplete from "../../shared/AutoComplete/autoComplete";
import React from 'react';

class CustomerDetailsModalComponent extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {
            customer: props.customer,
            errorMessage: null,
            errors: [],
        };
    }

    componentDidMount() {

        // let {  customer } = this.props;
        // this.setState({customer});
    }

    getEmptyCustomer = () => {
        return {
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
        };
    };
    handleChange = ({currentTarget: input}) => {
        const customer = {...this.state.customer};
        customer[input.name] = input.value;
        this.setState({customer});
    };
    correctObject = (customer) => {
        if (!customer.phone1) customer.phone1 = "";

        if (!customer.title) customer.title = "";

        if (!customer.phone2) customer.phone2 = "";

        if (!customer.addressLine1) customer.addressLine1 = "";

        if (!customer.addressLine2) customer.addressLine2 = "";

        if (!customer.city) customer.city = "";

        if (!customer.state) customer.state = "";

        if (!customer.country) customer.country = "GB";

        if (!customer.postalCode) customer.postalCode = "";
        return customer;
    };
    valid = (customer) => {
        return !(!customer.companyName ||
            !customer.firstName ||
            !customer.lastName ||
            !customer.email ||
            !customer.phone1 ||
            !customer.addressLine1 ||
            !customer.country ||
            !customer.postalCode ||
            !customer.city);
    };

    getCustomerElement(
        label,
        name,
        content = null,
        value = "",
        required = true,
        errorMessage = ""
    ) {
        const {el, handleChange} = this;
        return el("tr", {key: "tr" + name}, [
            el(
                "td",
                {
                    key: "td" + name,
                    className: "text-right nowrap",
                    style: {width: 100},
                },
                label
            ),
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

    handleOnSave = () => {
        const customer = {...this.props.customer, ...this.state.customer};

        if (customer.country == null)
            customer.country = "GB";
        if (this.valid(customer)) {
            this.props.onSumbit(customer);
        } else {
            this.setState({errors: "Please enter required inputs"})
        }

    };
    handleOnClose = () => {
        this.props.onClose();
    };
    getCustomerElements = () => {
        const {el, handleCityOnSelect, handleOnSave, handleOnCancel} = this;
        let {customer: customerSt, errors} = this.state;
        let customerProps = this.props.customer;

        const customer = this.correctObject({...customerProps, ...customerSt});
        // this.setState({customer});
        let errorMessage = "";
        if (typeof errors === "string") errorMessage = errors;


        return el(
            "table",
            {key: "table", style: {width: 500}},
            el("tbody", null, [
                this.getCustomerElement(
                    "Company Name",
                    "companyName",
                    null,
                    customer?.companyName,
                    true,
                    errors["companyName"]
                ),
                this.getCustomerElement(
                    "First Name",
                    "firstName",
                    null,
                    customer?.firstName,
                    true,
                    errors["firstName"]
                ),
                this.getCustomerElement(
                    "Last Name",
                    "lastName",
                    null,
                    customer?.lastName,
                    true,
                    errors["lastName"]
                ),
                this.getCustomerElement(
                    "Title",
                    "title",
                    null,
                    customer?.title,
                    true,
                    errors["title"]
                ),
                this.getCustomerElement(
                    "Email",
                    "email",
                    null,
                    customer?.email,
                    true,
                    errors["email"]
                ),
                this.getCustomerElement(
                    "Phone 1",
                    "phone1",
                    null,
                    customer?.phone1,
                    true,
                    errors["phone1"]
                ),
                this.getCustomerElement(
                    "Phone 2",
                    "phone2",
                    null,
                    customer?.phone2,
                    false,
                    errors["phone2"]
                ),
                this.getCustomerElement(
                    "Address Line 1",
                    "addressLine1",
                    null,
                    customer?.addressLine1,
                    true,
                    errors["addressLine1"]
                ),
                this.getCustomerElement(
                    "Address Line 2",
                    "addressLine2",
                    null,
                    customer?.addressLine2,
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
                        value: customer?.city,
                        required: true,
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
                    customer?.state,
                    false,
                    errors["state"]
                ),
                this.getCustomerElement(
                    "Country",
                    "country",
                    null,
                    customer?.country,
                    true,
                    errors["country"]
                ),
                this.getCustomerElement(
                    "Postal Code",
                    "postalCode",
                    null,
                    customer?.postalCode,
                    true,
                    errors["postalCode"]
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
            ])
        );
    };
    handleCityOnSelect = (event) => {
        if (event != null) {
            const customer = {...this.state.customer};
            customer.city = event.name;
            this.setState({customer});

        }
    };

    getFooter = () => {
        const {el, handleOnClose, handleOkButton, handleOnSave} = this;
        return el(React.Fragment, {key: "footer"}, [
            el("button", {key: "btnCancel", onClick: handleOnClose}, "Cancel"),
            el("button", {key: "btnSubmit", onClick: handleOnSave}, "Ok"),
        ]);
    };

    render() {
        const {show} = this.props;
        const {el, handleOnClose, getFooter, getCustomerElements} = this;
        return el(Modal, {
            key: "Modal",
            show: show,
            width: "600px",
            title: `Customer Details`,
            onClose: handleOnClose,
            content: getCustomerElements(),
            footer: getFooter(),
        });
    }
}

export default CustomerDetailsModalComponent;

"use strict";
import AutoComplete from "../../shared/AutoComplete/autoComplete";
import APICustomerLicenses from './APICustomerLicenses';
import React from 'react';

/**
 *  Edit TechData customers and link them with CNC customers
 */
class TDOrderDetailsComponent extends React.Component {
    el = React.createElement;
    apiCustomerLicenses;

    /**
     * init state
     * @param {*} props
     */
    constructor(props) {
        super(props);
        this.state = {
            mode: "insert",
            cncCustomers: [],
            data: {vendor: null, product: null},
            vendors: [],
            products: [],
            errors: {},
        };
        this.apiCustomerLicenses = new APICustomerLicenses();
    }

    componentDidMount() {
        const queryParams = new URLSearchParams(window.location.search);
        const orderId = queryParams.get("orderId");
        if (orderId && orderId !== "") {
            this.apiCustomerLicenses.getOrderDetials(orderId).then(res => {

            })
        }
    }

    fetchAllVendors = (page) => {


        this.apiCustomerLicenses.getVendors(page).then(response => {
            if (response.Result === 'Success') {
                let vendors = [...this.state.vendors];
                vendors = vendors.concat(response.BodyText.vendors);
                this.setState({vendors});
                console.log(page, response.BodyText.totalPages)
                if (page < response.BodyText.totalPages)
                    this.fetchAllVendors(page + 1)
            }
        })
    }
    handleChange = ({currentTarget: input}) => {
        const data = {...this.state.data};
        data[input.name] = input.value;
        this.setState({data});
    };

    getOrderElement(
        label,
        name,
        content = null,
        value = "",
        required = true,
        errorMessage = ""
    ) {
        const {el, handleChange} = this;
        return el("tr", {key: "tr" + name}, [
            el("td", {key: "td" + name, className: "text-right nowrap"}, label),
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

    getOrderElements() {
        const {
            el,
            handleProductSelect,
            handleVendorSelect,
            handleSkuSelect,
            handleOnSave,
            handleOnCancel
        } = this;
        const {vendors, errors, products, data} = this.state;
        let errorMessage = "";
        if (typeof errors === "string")
        return el(
            "table",
            {key: "table", style: {maxWidth: 1000}},
            el("tbody", null, [
                this.getOrderElement(
                    "Vendor",
                    "vendor",
                    el(AutoComplete, {
                        key: "vendorAuto",
                        errorMessage: "No Vendor Found",
                        items: vendors,
                        displayLength: "40",
                        displayColumn: "name",
                        pk: "id",
                        onSelect: handleVendorSelect,
                    }),
                    null,
                    true
                ),
                this.getOrderElement(
                    "Procuct",
                    "product",
                    el(AutoComplete, {
                        key: "productAut",
                        errorMessage: "No Product Found",
                        items: products,
                        displayLength: "40",
                        displayColumn: "listingName",
                        pk: "listingName",
                        onSelect: handleProductSelect,
                    }),
                    null,
                    true
                ),
                this.getOrderElement(
                    "Skus",
                    "skus",
                    el(AutoComplete, {
                        key: "skusAuto",
                        errorMessage: "No Skus Found",
                        items: data?.product?.skus || [],
                        displayLength: "40",
                        displayColumn: "skuName",
                        pk: "sku",
                        onSelect: handleSkuSelect,
                    }),
                    null,
                    true
                ),
                this.getOrderElement(
                    "AddOns",
                    "addOns",
                    el(AutoComplete, {
                        key: "skusAuto",
                        errorMessage: "No Skus Found",
                        items: data?.sku?.addOns || [],
                        displayLength: "40",
                        displayColumn: "skuName",
                        pk: "sku",
                    }),
                    null,
                    true
                ),
                el(
                    "tr",
                    {key: "trSave"},
                    el(
                        "td",
                        {key: "tdSave", colSpan: 2},
                        [el("button", {key: "btnSave", onClick: handleOnSave}, "Save"),
                            el("button", {key: "btnCancel", onClick: handleOnCancel}, "Cancel")]
                    )
                ),
            ])
        );
    }

    handleSkuSelect = (sku) => {

        const data = {...this.state.data};
        data.sku = sku;
        this.setState({data});
    }
    handleProductSelect = (product) => {
        console.log(product)
        const data = {...this.state.data};
        data.product = product;
        this.setState({data});
    }
    handleVendorSelect = (vendor) => {
        console.log(vendor)
        const data = {...this.state.data};
        data.vendor = vendor;
        this.apiCustomerLicenses.getProductsByVendor(vendor.id, 10).then(res => {
            if (res.Result === 'Success') {

                this.setState({products: res.BodyText.products.vendors[0].listings});
            }
            console.log('vendor products', res.Result)
        })
        this.setState({data});
    }
    handleOnSave = () => {

        const {data, mode} = this.state;
        if (mode === "insert") {
            this.apiCustomerLicenses
                .addTechDataCustomer(this.state.data)
                .then((result) => {

                    if (result.Result === "Failed") {
                        const errors = result.ErrorMessage;
                        this.setState({errors});
                    }
                });
        } else if (mode === "edit") {
            console.log('edit customer')
            this.apiCustomerLicenses
                .updateTechDataCustomer(this.state.data.id, this.state.data)
                .then((result) => {

                    if (result.Result === "Failed") {
                        const errors = result.ErrorMessage;
                        this.setState({errors});
                    } else if (result.Result === "Success") {
                        this.handleOnCancel();
                    }
                });
        }
    };

    getCustomerDetails = (endCustomerId) => {
        this.apiCustomerLicenses.getCustomerDetails(endCustomerId).then((res) => {

            if (res.Result === "Success") {
                const data = {...res.BodyText.endCustomerDetails};

                //this.setState({ data, mode: "edit" });
            }
        });
    };
    handleOnCancel = () => {
        window.location = '/CustomerLicenses.php?action=searchOrders'

    }

    render() {
        const {el} = this;
        return el("div", null, [this.getOrderElements()]);
    }
}

export default TDOrderDetailsComponent;

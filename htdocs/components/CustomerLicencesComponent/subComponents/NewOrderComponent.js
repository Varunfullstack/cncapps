import Spinner from "./../../shared/Spinner/Spinner";
import APICustomerLicenses from "./APICustomerLicenses.js";
import Table from "./../../shared/table/table";
import CustomerDetailsModal from './CustomerDetailsModalComponent.js';

import React from 'react';

//AddOns and billing sku(s) (SK22141) cannot be purchased using place order API
class NewOrderComponent extends React.Component {
    el = React.createElement;
    apiCustomerLicenses;

    constructor(props) {
        super(props);
        this.apiCustomerLicenses = new APICustomerLicenses();
        this.state = {
            endCustomer: null,
            _showSpinner: false,
            _showAddOnsModal: false,
            productList: [],
            filteredProductList: [],
            selectedProductLine: null,
            errorMessage: null,
            _showCustomerModal: false,
            selectedDomain: ""
        };
    }

    async componentDidMount() {
        // get techdata customer details
        const queryParams = new URLSearchParams(window.location.search);
        const endCustomerEmail = queryParams.get("email");
        let state = {};
        this.showSpinner();
        const customerSerach = this.props.customers.filter(c => c.email == endCustomerEmail);
        if (customerSerach.length > 0) {
            state.endCustomer = customerSerach[0];
            if (state.endCustomer.MsDomain && state.endCustomer.MsDomain.length > 0) {
                state.selectedDomain = state.endCustomer.MsDomain[0].domain;
            } else
                state.selectedDomain = '';
        }

        //
        const currentUser = await this.apiCustomerLicenses.getCurrentUser();
        let productList = await this.apiCustomerLicenses.getLocalProducts();

        productList = productList.map(p => {
            p.quantity = 0;
            return p;
        });
        let pages = productList.length / 20;


        for (let i = 0; i < productList.length; i += 20) {
            const chunck = productList.slice(i, i + 20);

            let streamOneProducts = await this.apiCustomerLicenses.getProductBySKU({
                skus: chunck.map((p) => p.sku),
            });

            productList = productList.map((p) => {
                if(streamOneProducts.Result=='Success')
                {
                    const streamProduct = streamOneProducts.BodyText.productDetails.filter(
                        (s) => s.sku == p.sku
                    );
                    if (streamProduct.length > 0) {
                        p = {...p, ...streamProduct[0]};
                    }
                }
                p.quantity = 0;
                return p;
            });
        }


        if (state.endCustomer.email)
            state = {
                ...state,
                ...{
                    currentUser,
                    productList,
                    filteredProductList: productList
                },
            };
        else this.setState({error: "Please select customer"});

        this.setState({...state});
        this.hideSpinner();
    }

    showSpinner = () => {
        this.setState({_showSpinner: true});
    };
    hideSpinner = () => {
        this.setState({_showSpinner: false});
    };
    // handleProductListChange = (event) => {
    //   //
    //   const { productList } = this.state;
    //   const selectedCategory = productList.filter(
    //     (p) => p.listingName == event.target.value
    //   )[0];
    //   //
    //   this.setState({
    //     selectedCategoryName: event.target.value,
    //     selectedCategory,
    //   });
    // };

    handleDomainChange = (event) => {
        if (event?.target?.value)
            this.setState({selectedDomain: event.target.value});
        else
            this.setState({selectedDomain: value || null});
    };

    getEndcustomerDomainElement() {
        const {endCustomer, selectedDomain} = this.state;
        const {el, handleDomainChange} = this;
        if (endCustomer?.MsDomain)
            return el(
                "select",
                {
                    key: "customerDomains",
                    onChange: handleDomainChange,
                    value: selectedDomain,
                    style: {width: 155}
                },
                endCustomer?.MsDomain ? endCustomer.MsDomain.map((d, indx) =>
                        el("option", {key: "option" + indx}, d.domain)
                    )
                    : null
            );
        else return el('input', {key: 'domain', value: selectedDomain, onChange: handleDomainChange});
    }

    handleProductQuantity = (event, product) => {
        //
        const {filteredProductList} = this.state;
        let index = filteredProductList.map((s) => s.sku).indexOf(product.sku);
        filteredProductList[index].quantity = event.target.value;
        this.setState({filteredProductList});
    };


    getLinesElement() {
        const {filteredProductList} = this.state;
        const {el, handleProductQuantity} = this;
        const columns = [
            {
                path: "sku",
                label: "SKU",
                sortable: true,
            },
            {path: "skuName", label: "Product Name", sortable: true},
            {path: "cost", label: "Unit Price", sortable: true, content: (p) => (<label>&pound;{p.cost}</label>)},
            {path: "skuType", label: 'Product Type', sortable: true},
            {path: "listingName", label: 'Listing Name', sortable: true},
            {
                path: "quantity",
                label: "Qty",
                sortable: true,

                content: (p) =>
                    el("input", {
                        value: p.quantity,
                        min: 0,
                        type: "number",
                        disabled: p.skuType == "Add On Subscription",
                        style: {maxWidth: 40},
                        onChange: (event) => handleProductQuantity(event, p),
                    }),
            },


        ];

        return this.el('div', {key: "tableContainer", style: {maxWidth: 1200, overflowY: 'auto', maxHeight: 600}},
            this.el(Table, {
                id: "lines",
                data: filteredProductList || [],
                columns: columns,
                defaultSortPath: "skuType",
                defaultSortOrder: "desc",
                pk: "sku",
                search: false,
                searchLabelStyle: {marginRight: 30, marginLeft: 5}
            }));
    }

    handleDeleteCartItem = (item) => {
        const {filteredProductList} = this.state;
        let _pIndex = -1;
        filteredProductList.forEach((element, pIndex) => {
            if (element.sku == item.sku) _pIndex = pIndex;
        });
        if (_pIndex >= 0)
            filteredProductList[_pIndex].quantity = 0;
        this.setState({filteredProductList});
        //check if it is a product
    };
    getFinalOrderItems = () => {
        const {filteredProductList} = this.state;
        let items = [];
        filteredProductList.forEach((product) => {
            if (product.quantity > 0)
                items.push(product);
        });
        return items;
    };
    getOrderFinalItemsElement = () => {
        let items = this.getFinalOrderItems();

        const columns = [
            {
                path: "sku",
                label: "SKU",
                sortable: true,
            },
            {path: "skuName", label: "Product Name", sortable: true},
            {path: "quantity", label: "Qty", sortable: true},
            {path: "cost", label: "Unit Price", sortable: true, content: (p) => this.el('label', null, "₤" + p.cost)},
            {
                path: null,
                label: "Delete",
                sortable: true,
                content: (item) =>
                    this.el(
                        "button",
                        {onClick: () => this.handleDeleteCartItem(item)},
                        "Delete"
                    ),
            },
        ];
        if (items) {
            let total = 0;
            if (items.length > 0)
                total = items
                    .map((item) => item.cost * item.quantity)
                    .reduce((c, p) => c + p)
                    .toFixed(2);

            return this.el("div", {key: "cartContainer", style: {width: 1200}}, [
                this.el(Table, {
                    id: "cartItems",
                    data: items || [],
                    columns: columns,
                    defaultSortPath: "sku",
                    defaultSortOrder: "asc",
                    pk: "sku",
                }),
                items.length > 0
                    ? this.el(
                    "dt",
                    {key: "total", style: {textAlign: "right"}},
                    "Total " + '₤' + total
                    )
                    : null,
            ]);
        } else return null;
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
    handleSubmit = () => {
        let items = this.getFinalOrderItems();
        const {endCustomer, selectedDomain, currentUser} = this.state;

        if (items.length == 0) {
            alert("Your cart is empty Please set product quantities");
            return;
        }
        if (!this.valid(endCustomer)) {
            this.setState({errorMessage: null, _showCustomerModal: true});

        }
        // place order
        else {
            this.showSpinner();

            const lines = items.map((item) => {
                if (selectedDomain)
                    return {
                        sku: item.sku,
                        quantity: item.quantity,
                        additionalData: {domain: selectedDomain},
                    };
                else return {sku: item.sku, quantity: item.quantity};
            });
            const order = {
                placeOrders: [
                    {
                        poNumber: "CNC",
                        lines,
                        paymentMethod: {
                            type: "Terms",
                        },
                        metaData: {
                            firstName: currentUser.firstName,
                            lastName: currentUser.lastName,
                            isEndCustomer: false,
                        },
                        agreementDetails: {
                            firstName: endCustomer.firstName,
                            lastName: endCustomer.lastName,
                            email: endCustomer.email,
                            acceptanceDate: moment().format("MM/DD/YYYY"),
                            phoneNumber: endCustomer.phone1,
                        },
                        endCustomer: {
                            id: endCustomer.endCustomerId,
                        },
                    },
                ],
            };
            if (endCustomer.endCustomerId == null) {
                order.placeOrders[0].endCustomer = {...endCustomer}
            }
            //
            // this.hideSpinner();
            // return;
            this.apiCustomerLicenses.addOrder(order).then((res) => {

                if (
                    res.Result == "Success" &&
                    res.BodyText.placeOrdersDetails[0].result == "success"
                )
                    window.location = `/CustomerLicenses.php?action=searchOrders&email=${endCustomer.email}&tap=saas`;
                else this.setState({errorMessage: res.ErrorMessage});
                this.hideSpinner();
            });
        }
    };
    handleCustomerOnSumbit = (customer) => {

        this.setState({_showCustomerModal: false, endCustomer: customer});
        setTimeout(() => this.handleSubmit(), 100)

    }
    handleOnClose = () => {
        this.setState({_showCustomerModal: false});
    }
    handleSearch = (event) => {

        const value = event.target.value;
        const {productList} = this.state;
        const filteredProductList = productList.filter(p =>
            p.sku.toLowerCase().indexOf(value) >= 0 ||
            p.skuName.toLowerCase().indexOf(value) >= 0 ||
            p.cost.toLowerCase().indexOf(value) >= 0 ||
            p.skuType.toLowerCase().indexOf(value) >= 0 ||
            p.listingName.toLowerCase().indexOf(value) >= 0 ||
            p.quantity.toString().toLowerCase().indexOf(value) >= 0);

        this.setState({filteredProductList});
    }

    render() {
        const {el, handleSubmit, handleCustomerOnSumbit, handleOnClose, handleSearch} = this;
        const {_showSpinner, errorMessage, endCustomer, _showCustomerModal} = this.state;
        return this.el("div", null, [
            el(Spinner, {key: "spinner", show: _showSpinner}),
            el(CustomerDetailsModal, {
                key: "customerDetails",
                customer: endCustomer,
                show: _showCustomerModal,
                onSumbit: handleCustomerOnSumbit,
                onClose: handleOnClose
            }),
            //this.getAddonModalElement(),
            el("table", {key: "tableContent"}, [
                el(
                    "tbody",
                    {key: "tbody1"},
                    endCustomer != null ? el("tr", {key: "trProductList"}, [
                        el("td", {key: "td1"}, "StreamOne "),
                        el("td", {
                            key: "td2",
                            colSpan: 3
                        }, " Place New Order for" + endCustomer ? endCustomer?.firstName + ' ' + endCustomer?.lastName : ''),
                    ]) : null,
                    el("tr", {key: "tr2"}, [
                        el("td", {key: "td3"}, "Domain"),
                        el("td", {key: "td4"}, this.getEndcustomerDomainElement()),
                        el("td", {key: "td5"}, "Search"),
                        el("td", {key: "td6"}, el('input', {onChange: handleSearch})),
                    ])
                ),
            ]),
            this.getLinesElement(),
            el("h3", {key: "cartTitle"}, "Order final items"),
            this.getOrderFinalItemsElement(),
            el(
                "span",
                {
                    key: "errorMessage",
                    className: "error-message",
                    style: {display: "block", margin: 10},
                },
                errorMessage
            ),
            el("i", {
                key: "submit",
                onClick: handleSubmit,
                className: 'fal fa-shopping-cart fa-2x pointer',
                title: "Place Order"
            },),
        ]);
    }
}

export default NewOrderComponent;

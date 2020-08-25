"use strict";
import Table from './../utils/table/table.js?v=1';
import APICustomerLicenses from './APICustomerLicenses.js?v=1';

/**
 *  Edit TechData customers and link them with CNC customers
 */
class CMPTDProductAddOns extends React.Component {
    el = React.createElement;
    apiCustomerLicenses;
    addonsLoad = false;

    /**
     * init state
     * @param {*} props
     */
    constructor(props) {
        super(props);
        this.state = {
            productDetails: [],
            addOns: [],
            errors: {},
        };
        this.apiCustomerLicenses = new APICustomerLicenses();
    }

    componentDidUpdate() {

    }

    componentDidMount() {
        this.getAddons();
    }

    getAddonsTable(addOns) {
        const {el} = this;

        const columns = [
            {path: "sku", label: "TD#", sortable: true},
            {path: "skuName", label: "Product Name", sortable: true,},
        ];
        if (addOns) {
            return this.el(Table, {
                key: 'skuName',
                data: addOns || [],
                columns: columns,
                defaultSortPath: 'skuName',
                defaultSortOrder: 'asc',
                pk: 'sku'
            })
        }
    }

    getAddons() {

        const {skus} = this.props;
        console.log(skus);
        if (skus && skus.length > 0)
            this.apiCustomerLicenses.getProductBySKU({
                "skus": skus
            }).then(result => {
                console.log("getProductBySKU", result);
                if (result.Result == 'Success') {
                    this.setState({productDetails: result.BodyText.productDetails});
                    this.addonsLoad = true;

                }
            });

    }

    render() {
        const {productDetails} = this.state;
        const {el} = this;
        const addOns = productDetails.map(p => p.addOns);
        const allAddOns = [].concat.apply([], addOns);
        return el("div", null,
            this.getAddonsTable(allAddOns));
    }
}

export default CMPTDProductAddOns;

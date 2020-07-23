"use strict";
import APICustomerLicenses from './APICustomerLicenses.js?v=7';
import CMPTDCustomerSearch from './CMPTDCustomerSearch.js?v=7';
import CMPTDCustomerDetails from './CMPTDCustomerDetails.js?v=7';
import CMPTDCustomerOrders from './CMPTDCustomerOrders.js?v=7';
import CMPTDOrderDetails from './CMPTDOrderDetails.js?v=7'
import NewOrder from './CMPNewOrder.js?v=7';
/**
 * Don't forget to change v value in js import before push to gethub to avoid cache problem
 */
class CMPCustomerLicenses extends React.Component {
  el = React.createElement;
  apiCustomerLicenses;
  apiTechData;

  /**
   * init state
   * @param {*} props
   */
  constructor(props) {
    super(props);
    this.state = {};
    this.apiCustomerLicenses = new APICustomerLicenses();
    this.loadComponents();
  }
  loadComponents = async () => {};
  componentDidMount() {
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
  }
  handleAddCustomer = () => {
    console.log("add customer");
    window.location = "/CustomerLicenses.php?action=addNewCustomer";
  };
  render() {
    const { el, handleAddCustomer } = this;
    const queryParams = new URLSearchParams(window.location.search);
    const action = queryParams.get("action");
    switch (action) {
      case "searchCustomers":
        return el(CMPTDCustomerSearch, { onAddNew: handleAddCustomer });
      case "searchOrders":
        return el(CMPTDCustomerOrders, null);
      case "addNewCustomer":
        return el(CMPTDCustomerDetails, { onAddNew: handleAddCustomer });
      case "editCustomer":
        return el(CMPTDCustomerDetails, {
          onAddNew: handleAddCustomer,
          customerId: queryParams.get("endCustomerId"),
        });
      case "newOrder":
        return el(NewOrder, null);
      case "editOrder":
        return el(CMPTDOrderDetails, null);
      default:
        return el("h1", null, "TechData page");
    }
  }
}
export default CMPCustomerLicenses;

const domContainer = document.querySelector("#reactMainCustomerLicenses");
ReactDOM.render(React.createElement(CMPCustomerLicenses), domContainer);

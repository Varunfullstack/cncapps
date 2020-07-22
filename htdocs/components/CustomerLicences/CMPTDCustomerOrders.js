"use strict";
import AutoComplete from "./../utils/autoComplete.js?v=1";
import Table from './../utils/table/table.js?v=1';
import APICustomerLicenses from './APICustomerLicenses.js?v=1';
import Modal from './../utils/modal.js?v=1';
import RadioButtons from './../utils/radioButtons.js?v=1';
import Spinner from './../utils/spinner.js?v=1';
import CMPOrderHistoryModal from './CMPOrderHistoryModal.js?v=1';
/**
 * searching in TechData customers and link them with CNC customers
 */
class CMPTDCustomerOrders extends React.Component {
  el = React.createElement;
  apiCustomerLicenses;
  addonsRef;
  /**
   * init state
   * @param {*} props
   */
  constructor(props) {
    super(props);
    this.state = {
      results: [],
      endCustomer: null,
      selectedOrderLine: null,
      showModal: false,
      modalDefaultAction: 1,
      currentUser: null,
      _showSpinner: false,
      orderUpdateError: null,
      modalElement: null,
      selectedAddon: null,
      orderHistory: null,
      showOrderHistory: false,
      showAddonHistory: false,
    };
    this.apiCustomerLicenses = new APICustomerLicenses();
    this.addonsRef = React.createRef();
  }
  getInitResults() {
    return {
      page: 0,
      recordsPerPage: 0,
      totalPages: 0,
      totalRecords: 0,
      subscriptions: [],
    };
  }
  componentDidMount() {
    // get techdata customer details
    const queryParams = new URLSearchParams(window.location.search);
    const endCustomerId = queryParams.get("endCustomerId");
    this.showSpinner();
    this.apiCustomerLicenses
      .getCustomerDetails(endCustomerId)
      .then((result) => {
        if (result.Result == "Success") {
          //////console.log(result.BodyText.endCustomerDetails);
          this.setState({ endCustomer: result.BodyText.endCustomerDetails });
          setTimeout(() => this.getCustomerOrders(), 100);
        } else this.setState({ error: "Please select customer" });
        this.hideSpinner();
      });
    //get current user
    this.apiCustomerLicenses.getCurrentUser().then((res) => {
      //////console.log('current user',res);
      this.setState({ currentUser: res });
    });
  }
  showSpinner = () => {
    this.setState({ _showSpinner: true });
  };
  hideSpinner = () => {
    this.setState({ _showSpinner: false });
  };
  handleNewOrder = () => {
    if (this.state.search.techDataCustomerId == null) {
      const error = "Please Select a valid customer whose had TechData account";
      this.setState({ error });
    } else {
      window.location =
        "/CustomerLicenses.php?action=newOrder&endCustomerId=" +
        this.state.search.techDataCustomerId +
        "&customerName=" +
        this.state.search.customerName;
    }
  };

  getCustomerOrders = () => {
    const { endCustomer } = this.state;
    ////console.log(this.state);
    if (endCustomer != null);
    {
      this.apiCustomerLicenses
        .getSubscriptionsByEndCustomerId(endCustomer.id)
        .then((response) => {
          ////console.log(response);
          if (response.Result == "Success") {
            let allSubscriptions = [];
            response.BodyText.subscriptions.map((sub) => {
              Object.keys(sub).map((key, index) => {
                sub[key].unitPrice =
                  sub[key].currencySymbol + sub[key].unitPrice;
                allSubscriptions.push(sub[key]);
              });
            });

            this.setState({ results: allSubscriptions });
          } else this.setState({ results: this.getInitResults() });
        });
    }
    // ////console.log("Search", this.state.search);
  };

  getSearchResult = () => {
    let { results, selectedOrderLine } = this.state;
    const { el, handleManageTenant, handleAddOns, handelOrderHistory } = this;
    const columns = [
      {
        path: "createdDate",
        label: "Created Date",
        sortable: true,
        content: (sub) =>
          el("label", null, moment(sub.createdDate).format("DD/MM/YYYY HH:MM")),
      },
      { path: "orderNumber", label: "Order", sortable: true },
      { path: "sku", label: "SKU", sortable: true },
      {
        path: "additionalData.domain",
        label: "Inital Office 365 Domain",
        sortable: true,
      },
      { path: "name", label: "Product Info", sortable: true },
      { path: "quantity", label: "Units", sortable: true },
      { path: "unitPrice", label: "Unit Price", sortable: true },
      { path: "status", label: "Status", sortable: true },
      {
        path: "lineStatus",
        label: "Line Status",
        sortable: true,
        content: (o) =>
          o.lineStatus === "processing"|| o.lineStatus === "in_process"
            ? el("div", {
                key: "divSpin" + o.orderNumber,
                className: "loader-content-sm",
              })
            : el("div", { key: "divSpin" + o.orderNumber }, o.lineStatus),
      },

      {
        path: null,
        label: "AddOns",
        sortable: true,
        content: (c) =>
          el(
            "button",
            { key: "btnManageTenant", onClick: () => handleAddOns(c) },
            "AddOns"
          ),
      },
      {
        path: null,
        label: "Manage Licenses",
        sortable: true,
        content: (c) =>
          el(
            "button",
            { key: "btnManageTenant", onClick: () => handleManageTenant(c) },
            "Edit"
          ),
      },
      {
        path: null,
        label: "History",
        sortable: true,
        content: (c) =>
          el(
            "button",
            { key: "btnHistory", onClick: () => handelOrderHistory(c) },
            "Show"
          ),
      },
    ];
    if (results) {
      //////console.log('selectedOrderLine',selectedOrderLine)
      return this.el(Table, {
        key: "subscriptions",
        data: results || [],
        columns: columns,
        defaultSortPath: "createdDate",
        defaultSortOrder: "desc",
        pk: "orderNumber",
        selected: selectedOrderLine,
        selectedKey: "sku",
      });
    }
  };
  handelOrderHistory = (order) => {
    console.log(order);
    const { selectedOrderLine } = this.state;
    //getOrderHistory
    if (order.subscriptionHistory.length > 0) {
      if (selectedOrderLine?.sku !== order.sku)
        this.setState({
          orderHistory: order.subscriptionHistory,
          showOrderHistory: true,
          selectedOrderLine: order,
        });
      else
        this.setState({
          orderHistory: order.subscriptionHistory,
          showOrderHistory: true,
        });
    } else {
      if (selectedOrderLine?.sku !== order.sku)
        this.setState({
          orderHistory: [],
          showOrderHistory: true,
          selectedOrderLine: order,
        });
      else this.setState({ orderHistory: [], showOrderHistory: true });
    }
  };
  getHeader = () => {
    const { el } = this;
    const { endCustomer } = this.state;
    if (endCustomer != null)
      return el(
        "h3",
        { key: "h2Customer", className: "text-center" },
        `StreamOne Orders For ${endCustomer.firstName} @ ${endCustomer.companyName}`
      );
    else
      el(
        "span",
        { key: "spanCustomer", className: "text-center" },
        "Loading informations ..."
      );
  };

  getModalOrderElement = () => {
    const {
      showModal,
      selectedOrderLine,
      modalDefaultAction,
      orderUpdateError,
    } = this.state;
    const {
      el,
      handleOnClose,
      handleModalAction,
      handleChange,
      handleUpdateOrder,
      handleSetOrderStatus,
    } = this;
    const inactive =
      selectedOrderLine?.lineStatus === "inactive" ? true : false;
    //prepare body
    const body = el("div", { key: "body" }, [
      el(
        "span",
        { key: "spanTitle" },
        "Select an option below to modify your current subscription and press Submit when done or Cancel to return to manage Tenant page"
      ),
      el("div", { key: "divStatus" }, [
        el("span", { key: "spanStatusText" }, "Current status : "),
        el(
          "span",
          { key: "spanStatusCompleted", className: "green-text" },
          selectedOrderLine?.lineStatus == "active" ? "Active" : ""
        ),
        el(
          "span",
          { key: "spanStatusNotCompleted", className: "red-text" },
          selectedOrderLine?.lineStatus == "inactive" ? "Inactive" : ""
        ),
      ]),
      el("hr", { key: "hr1" }),
      el("h4", { key: "q1" }, "What do you want to do?"),
      el(RadioButtons, {
        key: "actions",
        disabled: inactive,
        value: 1,
        onChange: handleModalAction,
        items: [
          { id: 1, name: "Change the number of seats for this subscriptions" },
          { id: 2, name: "Suspend this Subscription" },
        ],
      }),
      el("label", { key: "l2" }, "Number of Seats : "),
      el("input", {
        key: "i2",
        type: "number",
        name: "quantity",
        min: 0,
        disabled: modalDefaultAction !== 1 || inactive,
        value: selectedOrderLine?.quantity,
        onChange: handleChange,
      }),
      el("br", { key: "br1" }),
      el("label", { key: "l3" }, "Only applicable when changing seat number "),
      el("span", { key: "s3", className: "error-message" }, orderUpdateError),
    ]);
    const footer = el(React.Fragment, { key: "footer" }, [
      el("button", { key: "btnCancel", onClick: handleOnClose }, "Cancel"),
      !inactive
        ? el(
            "button",
            { key: "btnSubmit", onClick: handleUpdateOrder },
            "Submit"
          )
        : null,
      inactive
        ? el(
            "button",
            {
              key: "btnActivate",
              onClick: () => handleSetOrderStatus("activate"),
            },
            "Activate"
          )
        : null,
    ]);
    this.setState({
      modalElement: el(Modal, {
        key: "Modal",
        show: showModal,
        width: "600px",
        title: `Modify your ${selectedOrderLine?.name}`,
        onClose: handleOnClose,
        content: body,
        footer,
      }),
    });
  };
  handleManageTenant = (order) => {
    const { selectedOrderLine } = this.state;
    if (selectedOrderLine?.sku != order.sku) {
      this.setState({
        showModal: true,
        orderUpdateError: null,
        selectedOrderLine: order,
        modalDefaultAction: 1,
      });
    } else
      this.setState({
        showModal: true,
        orderUpdateError: null,
        modalDefaultAction: 1,
      });
    setTimeout(() => {
      this.getModalOrderElement();
    }, 100);
  };

  handleOnClose = () => {
    this.setState({ showModal: false, modalElement: null });
  };
  handleModalAction = (actionValue) => {
    ////console.log(actionValue);
    this.setState({ modalDefaultAction: actionValue });
    setTimeout(() => this.getModalOrderElement(), 50);
  };
  handleChange = ({ currentTarget: input }) => {
    const selectedOrderLine = { ...this.state.selectedOrderLine };
    selectedOrderLine[input.name] = input.value;
    this.setState({ selectedOrderLine });
    setTimeout(() => this.getModalOrderElement(), 50);
  };

  handleUpdateOrder = () => {
    const {
      modalDefaultAction,
      selectedOrderLine,
      currentUser,
      endCustomer,
    } = this.state;
    ////console.log(selectedOrderLine);
    let body = {
      action: "units",
      orderNumber: selectedOrderLine.orderNumber,
      sku: selectedOrderLine.sku,
      metaData: {
        firstName: currentUser.firstName,
        lastName: currentUser.lastName,
        isEndCustomer: false,
      },
    };
    if (modalDefaultAction === 1) {
      body.newQuantity = selectedOrderLine.quantity;
      body.agreementDetails = {
        firstName: endCustomer.firstName,
        lastName: endCustomer.lastName,
        email: endCustomer.email,
        acceptanceDate: moment().format("MM/DD/YYYY"),
        phoneNumber: endCustomer.phone1,
      };
    }
    if (modalDefaultAction === 2) body.action = "suspend";
    this.showSpinner();
    this.apiCustomerLicenses
      .updateOrder({ modifyOrders: [body] })
      .then((res) => {
        ////console.log(res);
        if (res.Result == "Success") {
          if (res.BodyText.modifyOrdersDetails[0].status == "success") {
            this.setState({
              showModal: false,
              orderUpdateError: null,
              modalDefaultAction: 1,
              modalElement: null,
            });
            setTimeout(() => this.getCustomerOrders(), 2000);
            setTimeout(() => this.getCustomerOrders(), 10000);
          } else if (res.BodyText.modifyOrdersDetails[0].status === "failed")
            this.setState({
              orderUpdateError: res.BodyText.modifyOrdersDetails[0].message,
            });
        }
        this.hideSpinner();
      });
  };
  handleSetOrderStatus = (status) => {
    const {
      modalDefaultAction,
      selectedOrderLine,
      currentUser,
      endCustomer,
    } = this.state;
    let body = {
      action: status,
      orderNumber: selectedOrderLine.orderNumber,
      sku: selectedOrderLine.sku,
      metaData: {
        firstName: currentUser.firstName,
        lastName: currentUser.lastName,
        isEndCustomer: false,
      },
    };
    this.showSpinner();
    this.apiCustomerLicenses
      .updateOrder({ modifyOrders: [body] })
      .then((res) => {
        ////console.log(res);
        if (res.Result == "Success") {
          if (res.BodyText.modifyOrdersDetails[0].status == "success") {
            this.setState({
              showModal: false,
              orderUpdateError: null,
              modalDefaultAction: 1,
              modalElement: null,
            });
            setTimeout(() => this.getCustomerOrders(), 2000);
            setTimeout(() => this.getCustomerOrders(), 10000);
            this.hideSpinner();
          }
        }
      });
  };

  handleAddOns = (order) => {
    ////console.log("order", order);
    this.setState({
      selectedOrderLine: order,
    });
    setTimeout(() => this.getAddons(order), 50);
  };
  getAddons = async (order) => {
    if (order != null) {
      this.showSpinner();

      //1- load order detials to get it's addons
      let orderAddons = await this.apiCustomerLicenses.getOrderDetials(
        order.orderNumber
      );

      //2- update product quantity and price
      const selectedOrderLine = { ...this.state.selectedOrderLine };
      const line = orderAddons.BodyText.orderInfo.lines.filter(
        (l) => l.sku === selectedOrderLine.sku
      );
      selectedOrderLine.addOns = line && line.length > 0 && line[0].addOns;
      //until now we have current order addons with there qunantity

      //3- get current product to get all avialabel addons
      let product = await this.apiCustomerLicenses.getProductBySKU({
        skus: [order.sku],
      });

      ////console.log("getProductBySKU", product?.BodyText?.productDetails[0].addOns);
      if (product.Result == "Success") {
        //get price list for all addons
        let productAddOns = product?.BodyText?.productDetails[0]?.addOns;
        const addOnsProductList = productAddOns?.map((a) => {
          return { sku: a.sku, quantity: 1 };
        });
        ////console.log('products',products);
        //4- get currend product addons prices
        const pages = addOnsProductList?.length / 10;
        for (let i = 0; i < pages; i++) {
          const obj = {
            vendorIds: [397],
            lines: addOnsProductList,
            page: i + 1,
          };
          let prices = await this.apiCustomerLicenses.getProductsPrices(obj);
          ////console.log("prices", prices.BodyText.pricingDetails);
          if (prices.Result == "Success") {
            prices.BodyText.pricingDetails.map((adn) => {
              for (let j = 0; j < productAddOns.length; j++) {
                if (productAddOns[j].sku === adn.sku) {
                  productAddOns[j] = { ...productAddOns[j], ...adn };
                }
              }
            });
          }
        }
        // now we have order addons and product addons and need to update order addons price
        // update selectedOrderLine addons prices
        for (let k = 0; k < selectedOrderLine?.addOns?.length; k++) {
          for (let l = 0; l < productAddOns.length; l++) {
            if (selectedOrderLine.addOns[k].sku === productAddOns[l].sku) {
              selectedOrderLine.addOns[k] = {
                ...selectedOrderLine.addOns[k],
                ...productAddOns[l],
              };
            }
          }
        }
        //console.log(selectedOrderLine,productAddOns);
        // finally update state
        this.setState({
          selectedOrderLine,
          productDetails: product.BodyText.productDetails[0],
        });
      } else {
        this.setState({ productDetails: null });
      }
      this.hideSpinner();
      this.scrollToAddons();
    }
  };
  handleAddonEdit = (addon) => {
    console.log("addon", addon);
    this.setState({
      showModal: true,
      orderUpdateError: null,
      selectedAddon: addon,
    });
    setTimeout(() => {
      this.getModalAddonsElement(addon);
    }, 100);
  };
  handleAddonHistory = (addon) => {
    this.setState({
      selectedAddon: addon,
      orderHistory: addon.additionalData?.subscriptionHistory,
      showAddonHistory: true,
    });
  };
  getAddonsElement = () => {
    const { productDetails, selectedOrderLine } = this.state;
    const { handleAddonEdit, handleAddonHistory, el } = this;
    //////console.log('addons',productDetails?.addOns,selectedOrderLine?.addOns);
    const allAddOns = productDetails?.addOns?.map((a) => {
      if (selectedOrderLine?.addOns) {
        const addonTemp = selectedOrderLine.addOns.filter(
          (a2) => a2.sku === a.sku
        );
        const newAddon =
          addonTemp.length > 0 ? { ...a, ...addonTemp[0] } : { ...a };
        //////console.log(newAddon);
        if (!newAddon.quantity) newAddon.quantity = 0;
        return newAddon;
      } else return a;
    });
    ////console.log('final addons',allAddOns);

    if (productDetails != null) {
      //      ////console.log(productDetails);
      const columns = [
        { path: "skuName", label: "Product Name", sortable: true },
        { path: "sku", label: "TD#", sortable: true },
        {
          path: "formattedResellerCost",
          label: "Price",
          sortable: true,
          content: (a) =>
            el(
              React.Fragment,
              null,
              a.formattedResellerCost || "Not Authorized"
            ),
        },
        {
          path: "quantity",
          label: "Quantity",
          sortable: true,
          content: (a) =>
            a.addOnStatus === "processing"||a.addOnStatus === "in_process"
              ? el("div", {
                  key: "divSpin" + a.sku,
                  className: "loader-content-sm",
                })
              : el(
                  "div",
                  {
                    key: "divSpin" + a.sku,
                  },
                  a.quantity || 0
                ), //(a.quantity || 0)
        },
        {
          path: null,
          label: "Edit",
          sortable: false,
          content: (addon) =>
            el("button", { onClick: () => handleAddonEdit(addon) }, "Edit"),
        },
        {
          path: null,
          label: "History",
          sortable: false,
          content: (addon) =>
            el("button", { onClick: () => handleAddonHistory(addon) }, "Show"),
        },
      ];
      return el(Table, {
        key: "addOns",
        data: allAddOns || [],
        columns: columns,
        defaultSortPath: "quantity",
        defaultSortOrder: "desc",
        pk: "sku",
      });
    } else return null;
  };

  handleUpdateOrderAddOn = (addon) => {
    const {
      selectedOrderLine,
      currentUser,
      endCustomer,
      productDetails,
    } = this.state;

    const inOrderList = selectedOrderLine.addOns.filter(
      (a) => a.sku === addon.sku
    );
    //console.log('addon',inOrderList,addon,selectedOrderLine,productDetails);
    let body = {
      orderNumber: selectedOrderLine.orderNumber,
      baseSubscription: selectedOrderLine.sku,
      addOns: [
        {
          action: "units",
          addOnSku: addon.sku,
          newQuantity: addon.quantity,
          quantity: addon.quantity,
        },
      ],
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
    };
    //console.log('update addon',body);
    if (addon.quantity == 0) {
      body.addOns[0].action = "suspend";
      delete body.addOns[0].newQuantity;
      delete body.addOns[0].quantity;
    }
    this.showSpinner();
    if (inOrderList.length > 0) {
      //console.log("old addon")
      this.apiCustomerLicenses
        .updateSubscriptionAddOns({ modifyAddons: body })
        .then((res) => {
          //console.log(res);
          if (res.Result == "Success") {
            this.setState({
              showModal: false,
              orderUpdateError: null,
              modalDefaultAction: 1,
            });
            setTimeout(() => this.getAddons(selectedOrderLine), 3000);
            setTimeout(() => this.getAddons(selectedOrderLine), 15000);
          } else if (res.Result === "Failed")
            this.setState({
              orderUpdateError: res.ErrorMessage,
            });
          setTimeout(() => {
            this.getModalAddonsElement(addon);
          }, 100);
          this.hideSpinner();
        });
    } else if (addon.quantity > 0) {
      //new addon

      this.apiCustomerLicenses
        .purchaseSubscriptionAddOns({ orderAddons: body })
        .then((res) => {
          //console.log(res);
          if (res.Result == "Success") {
            this.setState({
              showModal: false,
              orderUpdateError: null,
              modalDefaultAction: 1,
            });
            setTimeout(() => this.getAddons(selectedOrderLine), 3000);
            setTimeout(() => this.getAddons(selectedOrderLine), 15000);
          } else if (res.Result === "Failed")
            this.setState({
              orderUpdateError: res.ErrorMessage,
            });

          this.hideSpinner();
        });
    } else {
      this.setState({
        orderUpdateError: "Please enter a valid quantity",
        _showSpinner: false,
      });

      setTimeout(() => {
        this.getModalAddonsElement(addon);
      }, 100);
    }
  };
  handleAddonChange = ({ currentTarget: input }) => {
    const selectedAddon = { ...this.state.selectedAddon };
    selectedAddon[input.name] = input.value;
    this.setState({ selectedAddon });
    setTimeout(() => this.getModalAddonsElement(), 50);
  };
  getModalAddonsElement = () => {
    const {
      showModal,
      selectedOrderLine,
      orderUpdateError,
      selectedAddon,
    } = this.state;
    const {
      el,
      handleOnClose,
      handleAddonChange,
      handleUpdateOrderAddOn,
    } = this;
    const inactive =
      selectedOrderLine?.lineStatus === "inactive" ? true : false;
    //prepare body
    const body = el("div", { key: "body" }, [
      el(
        "span",
        { key: "spanTitle", style: { display: "block" } },
        "Specify  the number of seats required."
      ),
      el("strong", { key: "s1" }, "Add-On: "),
      el("span", { key: "span1" }, selectedAddon?.skuName),
      el("div", { key: "divStatus" }, [
        el("span", { key: "spanStatusText" }, "Base Subscription status : "),
        el(
          "span",
          { key: "spanStatusCompleted", className: "green-text" },
          selectedOrderLine?.lineStatus == "active" ? "Active" : ""
        ),
        el(
          "span",
          { key: "spanStatusNotCompleted", className: "red-text" },
          selectedOrderLine?.lineStatus == "inactive" ? "Inactive" : ""
        ),
      ]),
      el("hr", { key: "hr1" }),
      el("label", { key: "l2" }, "Number of Seats : "),
      el("input", {
        key: "i2",
        type: "number",
        name: "quantity",
        min: 0,
        disabled: inactive,
        value: selectedAddon?.quantity,
        onChange: handleAddonChange,
      }),
      el("br", { key: "br1" }),
      el("span", { key: "s3" }, "0 Quantity will suspend the addon"),
      el("span", { key: "s4", className: "error-message" }, orderUpdateError),
    ]);
    const footer = el(React.Fragment, { key: "footer" }, [
      el("button", { key: "btnCancel", onClick: handleOnClose }, "Cancel"),
      el(
        "button",
        {
          key: "btnSubmit",
          onClick: () => handleUpdateOrderAddOn(selectedAddon),
          disabled: inactive,
        },
        "Submit"
      ),
    ]);
    this.setState({
      modalElement: el(Modal, {
        key: "Modal",
        show: showModal,
        width: "600px",
        title: `Office 365 Add-On`,
        onClose: handleOnClose,
        content: body,
        footer,
      }),
    });
  };

  scrollToAddons = () => window.scrollTo(0, this.addonsRef.current.offsetTop);
  handleOrderHistoryHide = () => {
    this.setState({ showOrderHistory: false });
  };
  handleAddonHistoryClose = () => {
    this.setState({ showAddonHistory: false });
  };
  handleNewOrder=()=>{
    const {endCustomer}=this.state;    
    window.location =
    "/CustomerLicenses.php?action=newOrder&endCustomerId=" + endCustomer.id;
  }
  render() {
    const { el, handleOrderHistoryHide, handleAddonHistoryClose,handleNewOrder } = this;
    const {
      _showSpinner,
      modalElement,
      orderHistory,
      showOrderHistory,
      selectedOrderLine,
      showAddonHistory,
      selectedAddon,
      
    } = this.state;
    console.log(selectedAddon);
 
    return el("div", null, [
      el(Spinner, { key: "spinner", show: _showSpinner }),
      el(CMPOrderHistoryModal, {
        show: true,
        key: "orderHistor",
        items: orderHistory,
        show: showOrderHistory,
        title: selectedOrderLine && "History of " + selectedOrderLine?.name,
        onHide: handleOrderHistoryHide,
      }),
      el(CMPOrderHistoryModal, {
        show: true,
        key: "addonHistor",
        items: orderHistory,
        show: showAddonHistory,
        title:
          selectedAddon && "History of " + selectedAddon?.skuName + " addon",
        onHide: handleAddonHistoryClose,
      }),
      modalElement,
      this.getHeader(),
      el('button',{key:'btnNewOrder',onClick:handleNewOrder},"Place New Order",),
      this.getSearchResult(),
      el("h2", { key: "h2Addons", ref: this.addonsRef }, "AddOns"),
      this.getAddonsElement(),
    ]);
  }
}

export default CMPTDCustomerOrders;

import Spinner from "./../utils/spinner.js?v=1";
import APICustomerLicenses from "./APICustomerLicenses.js?v=1";
import Table from "./../utils/table/table.js?v=1";
import Modal from "./../utils/modal.js?v=1";
//AddOns and billing sku(s) (SK22141) cannot be purchased using place order API
class NewOrder extends React.Component {
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
      selectedProductLine: null,
      errorMessage: null,
    };
  }
  async componentDidMount() {
    // get techdata customer details
    const queryParams = new URLSearchParams(window.location.search);
    const endCustomerId = queryParams.get("endCustomerId");
    let state = {};
    this.showSpinner();
    const endCustomer = await this.apiCustomerLicenses.getCustomerDetails(
      endCustomerId
    );
    //console.log(endCustomer);

    if (endCustomer.BodyText.endCustomerDetails.additionalData) {
      console.log(
        endCustomer.BodyText.endCustomerDetails
      );
      state = {
        selectedDomain:
          endCustomer.BodyText.endCustomerDetails.additionalData.MsDomain[0],
      };
    }
    //console.log(endCustomer.BodyText.endCustomerDetails);
    const currentUser = await this.apiCustomerLicenses.getCurrentUser();
    let productList = await this.apiCustomerLicenses.getLocalProducts();
   
    productList=productList.map(p=>{
      p.quantity=0;
      return p;
    });
    let streamOneProducts = await this.apiCustomerLicenses.getProductBySKU({
      skus: productList.map(p=>p.sku),
    });
    productList=productList.map(p=>{
      const streamProduct=streamOneProducts.BodyText.productDetails.filter(s=>s.sku===p.sku);
      if(streamProduct.length>0)
      {
        p={...p,...streamProduct[0]};
      }
      p.quantity=0;
      return p;
    });
    console.log('streamOneProduct',streamOneProducts)
    console.log(productList);
    if (endCustomer.Result == "Success")
      state = {
        ...state,
        ...{
          endCustomer: endCustomer.BodyText.endCustomerDetails,
          currentUser,
          productList,
        },
      };
    else this.setState({ error: "Please select customer" });
    this.setState({ ...state });
    this.hideSpinner();
  }

  showSpinner = () => {
    this.setState({ _showSpinner: true });
  };
  hideSpinner = () => {
    this.setState({ _showSpinner: false });
  };
  handleProductListChange = (event) => {
    //console.log(event.target.value);
    const { productList } = this.state;
    const selectedCategory = productList.filter(
      (p) => p.listingName == event.target.value
    )[0];
    //console.log(selectedCategory);
    this.setState({
      selectedCategoryName: event.target.value,
      selectedCategory,
    });
  };
  
  handleDomainChange = (event) => {
    this.setState({ selectedDomain: event.target.value });
  };
  getEndcustomerDomainElement() {
    const { endCustomer, selectedDomain } = this.state;
    const { el, handleDomainChange } = this;
    if (endCustomer?.additionalData)
      return el(
        "select",
        {
          key: "customerDomains",
          onChange: handleDomainChange,
          value: selectedDomain,
          style:{width: 155}
        },
        endCustomer.additionalData
          ? endCustomer.additionalData.MsDomain.map((c, indx) =>
              el("option", { key: "option" + indx }, c)
            )
          : null
      );
    else return null;
  }
  handleProductQuantity = (event, product) => {
    //console.log(event.target.value,product);
    const { productList } = this.state;
    let index = productList.map((s) => s.sku).indexOf(product.sku);
    productList[index].quantity = event.target.value;
    this.setState({ productList });
  };

  handleOnClose = () => {
    this.setState({ _showAddOnsModal: false });
  };

  
  getLinesElement() {
    const { productList } = this.state;
    const { el, handleProductQuantity } = this;
    const columns = [
      {
        path: "sku",
        label: "SKU",
        sortable: true,
      },
      { path: "skuName", label: "Product Name", sortable: true },
      { path: "cost", label: "Unit Price", sortable: true,content:(p)=>el("label",null,"₤"+p.cost) },
      {path:"skuType",label:'Product Type',sortable:true},
      {path:"listingName",label:'Listing Name',sortable:true},
      {
        path: "quantity",
        label: "Qty",
        sortable: true,
        
        content: (p) =>
          el("input", {
            value: p.quantity,
            min: 0,
            type: "number",
            disabled:p.skuType==="Add On Subscription",
            style: { maxWidth: 40 },
            onChange: (event) => handleProductQuantity(event, p),
          }),
      },
      
      
    ];

    return this.el('div',{key:"tableContainer",style:{maxWidth:1200,overflowY:'auto',maxHeight:600}},
    this.el(Table, {
      key: "lines",
      data: productList || [],
      columns: columns,
      defaultSortPath: "skuType",
      defaultSortOrder: "desc",
      pk: "sku",
      search:true,
      searchLabelStyle:{marginRight: 30,marginLeft: 5}
    }));
  }
  handleDeleteCartItem = (item) => {
    const { productList } = this.state;
    let  _pIndex = -1;      
    productList.forEach((element, pIndex) => {
      if (element.sku === item.sku) _pIndex = pIndex;
    });
    if(_pIndex>=0)
    productList[_pIndex].quantity=0;
    this.setState({ productList });
    //check if it is a product
  };
  getFinalOrderItems = () => {
    const { productList } = this.state;
    let items = [];
    productList.forEach((product) => {   
        if (product.quantity > 0) 
          items.push(product);       
    });
    return items;
  };
  getOrderFinalItemsElement = () => {
    let items = this.getFinalOrderItems();
    console.log(items);
    const columns = [
      {
        path: "sku",
        label: "SKU",
        sortable: true,
      },
      { path: "skuName", label: "Product Name", sortable: true },
      { path: "quantity", label: "Qty", sortable: true },
      { path: "cost", label: "Unit Price", sortable: true,content:(p)=>this.el('label',null,"₤"+p.cost) },
      {
        path: null,
        label: "Delete",
        sortable: true,
        content: (item) =>
          this.el(
            "button",
            { onClick: () => this.handleDeleteCartItem(item) },
            "Delete"
          ),
      },
    ];
    if (items) {
      //////console.log('selectedOrderLine',selectedOrderLine)
      let total = 0;
      if (items.length > 0)
        total = items
          .map((item) => item.cost * item.quantity)
          .reduce((c, p) => c + p)
          .toFixed(2);

      return this.el("div", { key: "cartContainer", style: { width: 1200 } }, [
        this.el(Table, {
          key: "cartItems",
          data: items || [],
          columns: columns,
          defaultSortPath: "sku",
          defaultSortOrder: "asc",
          pk: "sku",
        }),
        items.length > 0
          ? this.el(
              "dt",
              { key: "total", style: { textAlign: "right" } },
              "Total " + '₤' + total
            )
          : null,
      ]);
    } else return null;
  };
  handleSubmit = () => {
    this.showSpinner();
    this.setState({ errorMessage: null });
    let items = this.getFinalOrderItems();
    const { endCustomer, selectedDomain, currentUser } = this.state;
    if (items.length == 0)
      alert("Your cart is empty Please set product quantities");
    // place order
    else {
      console.log(endCustomer, selectedDomain);
      const lines = items.map((item) => {
        if (selectedDomain)
          return {
            sku: item.sku,
            quantity: item.quantity,
            additionalData: { domain: selectedDomain },
          };
        else return { sku: item.sku, quantity: item.quantity };
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
              id: endCustomer.id,
            },
          },
        ],
      };
      console.log(order);
      this.apiCustomerLicenses.addOrder(order).then((res) => {
        console.log(res);
        if (
          res.Result === "Success" &&
          res.BodyText.placeOrdersDetails[0].result === "success"
        )        
        window.location = `/CustomerLicenses.php?action=searchOrders&endCustomerId=${endCustomer.id}&tap=saas`;
        else this.setState({ errorMessage: res.ErrorMessage });
        this.hideSpinner();
      });
    }
  };
  render() {
    const { el, handleSubmit } = this;
    const { _showSpinner, errorMessage,endCustomer  } = this.state;
    return this.el("div", null, [
      el(Spinner, { key: "spinner", show: _showSpinner }),
      //this.getAddonModalElement(),
      el("table", { key: "tableContent" }, [
        el(
          "tbody",
          { key: "tbody1" },
          endCustomer!=null?el("tr", { key: "trProductList" }, [
             el("td", { key: "td1" }, "StreamOne "),
             el("td", { key: "td2" }," Place New Order for"+endCustomer?endCustomer?.firstName+' '+endCustomer?.lastName:''),
           ]):null,
          el("tr", { key: "tr2" }, [
            el("td", { key: "td3" }, "Domain"),
            el("td", { key: "td4" }, this.getEndcustomerDomainElement()),
          ])
        ),
      ]),
      this.getLinesElement(),
      el("h3", { key: "cartTitle" }, "Order final items"),
      this.getOrderFinalItemsElement(),
      el(
        "span",
        {
          key: "errorMessage",
          className: "error-message",
          style: { display: "block", margin: 10 },
        },
        errorMessage
      ),
      el("i", { key: "submit", onClick: handleSubmit,className:'fa fa-shopping-cart fa-2x pointer' , title:"Place Order"}, ),
    ]);
  }
}
export default NewOrder;

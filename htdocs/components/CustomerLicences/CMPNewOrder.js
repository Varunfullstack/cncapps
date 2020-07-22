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
      errorMessage:null
    };
  }
  async componentDidMount() {
    // get techdata customer details
    const queryParams = new URLSearchParams(window.location.search);
    const endCustomerId = queryParams.get("endCustomerId");
    let state={};
    this.showSpinner();
    const endCustomer = await this.apiCustomerLicenses.getCustomerDetails(
      endCustomerId
    );
    //console.log(endCustomer);

    if(endCustomer.BodyText.endCustomerDetails.additionalData)
    {
      console.log(endCustomer.BodyText.endCustomerDetails.additionalData.MsDomain[0]);
      state={selectedDomain:endCustomer.BodyText.endCustomerDetails.additionalData.MsDomain[0]};
    }
    //console.log(endCustomer.BodyText.endCustomerDetails);
    const currentUser = await this.apiCustomerLicenses.getCurrentUser();

    //get all products
    let productList = await this.apiCustomerLicenses.getProductsByVendor(
      397,
      1
    );
    if (productList.Result === "Success") {
      productList = productList.BodyText.products.vendors[0]["listings"];
      let allProductLines=[];
      productList = productList.map((p) => {
        p.skus = p.skus.map((a) => {
          allProductLines.push({skue:a.sku,quantity:1});
          a.quantity = 0;
          if (a.addOns)
            a.addOns = a.addOns.map((addon) => {
              addon.quantity = 0;
              return addon;
            });
          return a;
        });
        return p;
      });
      console.log(allProductLines);
      const pages=allProductLines.length/10;
      //get all prices
      for(let i=1;i<=pages;i++)
      {
        const prices=await this.apiCustomerLicenses.getProductsPrices(allProductLines);
        if(prices.Result=== "Success")
        {
          productList=  productList.map((p) => {
            p.skus = p.skus.map((a) => {
             let price= prices.BodyText.pricingDetails.filter(p=>p.sku===a.sku);
              a.quantity = 0;
              if(price.length>0)
                a={...a,...price[0]};
              return a;
            });
            return p;
          });
        }
      }
      console.log(productList);
      state={...state,...{
        selectedCategoryName: productList[4]["listingName"],
        selectedCategory: productList[4],
      }};
      
    }
    //console.log('productList',productList);
    if (endCustomer.Result == "Success")
    state={...state,...{
      endCustomer: endCustomer.BodyText.endCustomerDetails,
      currentUser,
      productList,
    }};      
    else this.setState({ error: "Please select customer" });
    this.setState({...state});
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
  getProductListElement() {
    const { productList, selectedCategoryName } = this.state;
    const { el, handleProductListChange } = this;
    if (productList)
      return el(
        "select",
        {
          key: "productListSelect",
          onChange: handleProductListChange,
          value: selectedCategoryName,
        },
        productList.map((c, indx) =>
          el("option", { key: "option" + indx }, c.listingName)
        )
      );
    else return null;
  }
  handleDomainChange=(event)=>{
    this.setState({selectedDomain:event.target.value});

  }
  getEndcustomerDomainElement() {
    const { endCustomer,selectedDomain } = this.state;
    const { el, handleDomainChange } = this;
    if (endCustomer?.additionalData)
      return el(
        "select",
        {
          key: "customerDomains",
          onChange: handleDomainChange,
          value: selectedDomain,
        },
        endCustomer.additionalData? endCustomer.additionalData.MsDomain.map((c, indx) =>
          el("option", { key: "option" + indx }, c)
        ):null
      );
    else return null;
  }
  handleProductQuantity = (event, product) => {
    //console.log(event.target.value,product);
    const { selectedCategory } = this.state;
    let index = selectedCategory.skus.map((s) => s.sku).indexOf(product.sku);
    selectedCategory.skus[index].quantity = event.target.value;
    this.setState({ selectedCategory });
  };
  handleAddonsClick = (product) => {
    console.log(product);
    this.setState({ _showAddOnsModal: true, selectedProductLine: product });
  };
  handleOnClose = () => {
    this.setState({ _showAddOnsModal: false });
  };
  handleProductAddOnsQuantity = (event, addOn) => {
    const { selectedCategory, selectedProductLine } = this.state;
    const aIndx = selectedProductLine.addOns
      .map((a) => a.sku)
      .indexOf(addOn.sku);
    let pIndx = selectedCategory.skus
      .map((s) => s.sku)
      .indexOf(selectedProductLine.sku);
    selectedCategory.skus[pIndx].addOns[aIndx].quantity = event.target.value;
    this.setState({ selectedCategory });
  };
  getAddonModalElement = () => {
    const { selectedProductLine, _showAddOnsModal } = this.state;
    const { el, handleOnClose, handleProductAddOnsQuantity } = this;
    //prepare body
    let body = null;

    const columns = [
      {
        path: "sku",
        label: "SKU",
        sortable: true,
      },
      { path: "skuName", label: "Addon Name", sortable: true },
      { path: "description", label: "description", sortable: true },
      {
        path: "quantity",
        label: "Qty",
        sortable: true,
        content: (p) =>
          el("input", {
            value: p.quantity,
            min: 0,
            type: "number",
            style: { maxWidth: 40 },
            onChange: (event) => handleProductAddOnsQuantity(event, p),
          }),
      },
    ];
    if (selectedProductLine?.addOns) {
      //////console.log('selectedOrderLine',selectedOrderLine)
      body = this.el(Table, {
        key: "lines",
        data: selectedProductLine?.addOns || [],
        columns: columns,
        defaultSortPath: "skuName",
        defaultSortOrder: "asc",
        pk: "sku",
      });
    }
    const footer = el(
      React.Fragment,
      { key: "footer" },
      el("button", { key: "btnCancel", onClick: handleOnClose }, "Close")
    );
    return el(Modal, {
      key: "Modal",
      show: _showAddOnsModal,
      maxWidth: "900px",
      title: `Set AddOns Quantity to auto add it to final cart`,
      onClose: handleOnClose,
      content: body,
      footer,
    });
  };
  getLinesElement() {
    const { selectedCategory } = this.state;
    const { el, handleProductQuantity, handleAddonsClick } = this;
    const columns = [
      {
        path: "sku",
        label: "SKU",
        sortable: true,
      },
      { path: "skuName", label: "Product Name", sortable: true },
      { path: "description", label: "description", sortable: true },
      { path:"formattedResellerCost",label:"Unit Price", sortable: true },
      {
        path: "quantity",
        label: "Qty",
        sortable: true,
        content: (p) =>
          el("input", {
            value: p.quantity,
            min: 0,
            type: "number",
            style: { maxWidth: 40 },
            onChange: (event) => handleProductQuantity(event, p),
          }),
      },
      // {
      //   path: null,
      //   label: "AddOns",
      //   sortable: true,
      //   content: (p) =>
      //     el("button", { onClick: () => handleAddonsClick(p) }, "AddOns"),
      // },
    ];
    if (selectedCategory) {
      //////console.log('selectedOrderLine',selectedOrderLine)
      return this.el(Table, {
        key: "lines",
        data: selectedCategory?.skus || [],
        columns: columns,
        defaultSortPath: "skuName",
        defaultSortOrder: "asc",
        pk: "sku",
      });
    } else return null;
  }
  handleDeleteCartItem = (item) => {
    const { productList } = this.state;
    let _cIndex = -1,
      _pIndex = -1,
      _aIndex = -1;
    productList.forEach((element, cIndex) => {
      element.skus.forEach((product, pIndex) => {
        if (product.sku == item.sku) {
          productList.quantity = 0;
          _cIndex = cIndex;
          _pIndex = pIndex;
        } else if (product.addOns) {
          product.addOns.forEach((addon, aIndex) => {
            if (addon.sku == item.sku) {
              addon.quantity = 0;
              _cIndex = cIndex;
              _pIndex = pIndex;
              _aIndex = aIndex;
            }
          });
        }
      });
    });
    console.log(productList, _cIndex, _pIndex);
    if (_cIndex >= 0 && _pIndex >= 0 && _aIndex == -1) {
      // it's a product item
      productList[_cIndex].skus[_pIndex].quantity = 0;
    }
    if (_cIndex >= 0 && _pIndex >= 0 && _aIndex >= 0) {
      // it's a addon item
      productList[_cIndex].skus[_pIndex].addOns[_aIndex].quantity = 0;
    }
    this.setState({ productList });

    //check if it is a product
  };
  getFinalOrderItems = () => {
    const { productList } = this.state;
    let items = [];
    productList.forEach((element) => {
      element.skus.forEach((product) => {
        if (product.quantity > 0) items.push(product);
        if (product.addOns) {
          product.addOns.forEach((addOn) => {
            if (addOn.quantity > 0) items.push(addOn);
          });
        }
      });
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
      { path:"formattedResellerCost",label:"Unit Price", sortable: true },
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
      let total=0;
      if(items.length>0)      
         total=items.map(item=>item.unitResellerCost*item.quantity).reduce((c,p)=>c+p).toFixed(2);
      
      return this.el(
        "div",
        { key: "cartContainer", style: { width: 600 } },
        [
        this.el(Table, {
          key: "cartItems",
          data: items || [],
          columns: columns,
          defaultSortPath: "skuName",
          defaultSortOrder: "asc",
          pk: "sku",
        })
        ,items.length>0?this.el('dt',{key:'total',style:{textAlign: "right"}},"Total "+items[0].currencySymbol+total):null
      ]

      );
    } else return null;
  };
  handleSubmit = () => {
    this.showSpinner();
    this.setState({errorMessage:null});

    let items = this.getFinalOrderItems();
    const {endCustomer,selectedDomain,currentUser}=this.state;
    if (items.length == 0)
      alert("Your cart is empty Please set product quantities");
    // place order
    else {
      console.log(endCustomer,selectedDomain);
      const lines=items.map(item=>{
        if(selectedDomain)
          return {sku:item.sku,quantity:item.quantity,additionalData:{domain:selectedDomain}};
        else 
          return {sku:item.sku,quantity:item.quantity};
      })
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
            endCustomer:{
              id:endCustomer.id,

            }
          },
        ],
      };
      console.log(order);
      this.apiCustomerLicenses.addOrder(order).then(res=>{
        console.log(res);
        if(res.Result==='Success'&&res.BodyText.placeOrdersDetails[0].result=== "success")
          window.location =`/CustomerLicenses.php?aaction=searchOrders&endCustomerId=${endCustomer.id}&tap=saas` ;
        else 
          this.setState({errorMessage:res.ErrorMessage});
        this.hideSpinner();
      });
    }
  };
  render() {
    const { el, handleSubmit } = this;
    const { _showSpinner,errorMessage } = this.state;
    return this.el("div", null, [
      el(Spinner, { key: "spinner", show: _showSpinner }),
      this.getAddonModalElement(),
      el("table", { key: "tableContent" }, [
        el(
          "tbody",
          { key: "tbody1" },
          el("tr", { key: "trProductList" }, [
            el("td", { key: "td1" }, "Product List"),
            el("td", { key: "td2" }, this.getProductListElement()),
          ]),
          el("tr", { key: "tr2" }, [
            el("td", { key: "td3" }, "Domain"),
            el("td", { key: "td4" }, this.getEndcustomerDomainElement()),
          ])
        ),
      ]),
      this.getLinesElement(),
      el("h3", { key: "cartTitle" }, "Order final items"),
      this.getOrderFinalItemsElement(),
      el('span',{key:"errorMessage",className:"error-message",style:{display:"block",margin:10}},errorMessage),
      el("button", { key: "submit", onClick: handleSubmit }, "Place Order"),
    ]);
  }
}
export default NewOrder;

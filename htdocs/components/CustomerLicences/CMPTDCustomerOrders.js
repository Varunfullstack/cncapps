"use strict";
import AutoComplete from "./../utils/autoComplete.js?v=1";
import Table from './../utils/table/table.js?v=1';
import APICustomerLicenses from './APICustomerLicenses.js?v=1';
import Modal from './../utils/modal.js?v=1';
import RadioButtons from './../utils/radioButtons.js?v=1';
import Spinner from './../utils/spinner.js?v=1';
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
      selectedOrder: null,
      showModal:false,
      modalDefaultAction:1,
      currentUser:null,
      _showSpinner:false,
      orderUpdateError:null
    };
    this.apiCustomerLicenses = new APICustomerLicenses();
    this.addonsRef=React.createRef();
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
          console.log(result.BodyText.endCustomerDetails);
          this.setState({ endCustomer: result.BodyText.endCustomerDetails });
          setTimeout(() => this.getCustomerOrders(), 100);
        } else this.setState({ error: "Please select customer" });
        this.hideSpinner();
      });
    //get current user
    this.apiCustomerLicenses.getCurrentUser().then(res=>{
      console.log('current user',res);
      this.setState({currentUser:res});
    })
  }
  showSpinner=()=>{
    this.setState({_showSpinner:true});
  }
  hideSpinner=()=>{
    this.setState({_showSpinner:false});
  }
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
    console.log(this.state);
    if (endCustomer != null);
    {
      this.apiCustomerLicenses
        .getSubscriptionsByEndCustomerId(endCustomer.id)
        .then((response) => {
          console.log(response);
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
    // console.log("Search", this.state.search);
  };

  getSearchResult = () => {
    let { results } = this.state;
    const { el, handleManageTenant, handleAddOns } = this;

    const columns = [
      {
        path: "createdDate",
        label: "Created Date",
        sortable: true,
        content: (sub) =>
          el("label", null, moment(sub.createdDate).format("DD/MM/YYYY HH:MM")),
      },
      { path: "orderNumber", label: "Order", sortable: true },
      { path: "orderSource", label: "Order Source", sortable: true },
      { path: "vendorName", label: "Vendor", sortable: true },
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
      { path: "lineStatus", label: "Line Status", sortable: true },

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
        label: "Manage",
        sortable: true,
        content: (c) =>
          el(
            "button",
            { key: "btnManageTenant", onClick: () => handleManageTenant(c) },
            "Manage Tenant"
          ),
      },
    ];
    if (results) {
      return this.el(Table, {
        key: "subscriptions",
        data: results || [],
        columns: columns,
        defaultSortPath: "createdDate",
        defaultSortOrder: "desc",
        pk: "orderNumber",
      });
    }
  };

  getHeader = () => {
    const { el } = this;
    const { endCustomer } = this.state;
    if (endCustomer != null)
      return el(
        "h3",
        { key: "h2Customer", className: "text-center" },
        `Customer ${endCustomer.firstName} for ${endCustomer.companyName}`
      );
    else
      el(
        "span",
        { key: "spanCustomer", className: "text-center" },
        "Loading informations ..."
      );
  };

  handleAddOns = (order) => {
    this.setState({ selectedOrder: order });
    console.log("order", order);
    this.getAddons(order.sku);
  };

  handleManageTenant = (order) => {
    console.log("order", order);
    // window.location =    
    //   "/CustomerLicenses.php?action=editOrder&orderId=" + order.orderNumber;
    this.setState({showModal:true,productDetails:order,orderUpdateError:null});
  };

  getAddons=(sku)=> {
     if (sku!=null) {
       this.showSpinner();
      this.apiCustomerLicenses
        .getProductBySKU({
          skus: [sku],
        })
        .then((result) => {
          this.hideSpinner();
          console.log("getProductBySKU", result.BodyText.productDetails );
          if (result.Result == "Success") {
            this.setState({ productDetails: result.BodyText.productDetails[0] });
         
          }
          else 
          {
            this.setState({ productDetails: null });

          }
          this.scrollToAddons();
        });
    }
  }
  getAddonsElementd=()=> {
    const {productDetails}=this.state;
    
    if (productDetails!=null) {
//      console.log(productDetails);
      const columns = [
        { path: "sku",label: "TD#",sortable: true,},
        { path: "skuName", label: "Product Name", sortable: true },        
      ];   
        return this.el(Table, {
          key: "addOns",
          data: productDetails?.addOns || [],
          columns: columns,
          defaultSortPath: "skuName",
          defaultSortOrder: "asec",
          pk: "sku",
        });
      
   }
   else return null;
 }
 handleOnClose=()=>{
   this.setState({showModal:false});
 }
 handleModalAction=(actionValue)=>{
  console.log(actionValue);
  this.setState({modalDefaultAction:actionValue})
 }
 handleChange = ({ currentTarget: input }) => {
  const productDetails = { ...this.state.productDetails };
  productDetails[input.name] = input.value;
  this.setState({ productDetails });
};

handleUpdateOrder=()=>{
 const {modalDefaultAction,productDetails,currentUser,endCustomer}= this.state;
 let body={   
  action: "units",
  orderNumber: productDetails.orderNumber,
  sku: productDetails.sku,
  metaData: 
		{
		firstName : currentUser.firstName,
		lastName : currentUser.lastName,
		isEndCustomer: false
		}
 }
 if(modalDefaultAction===1)
 {
  body.newQuantity=productDetails.quantity;
  body.agreementDetails= 
		{
		firstName: endCustomer.firstName,
		lastName: endCustomer.lastName,
		email: endCustomer.email,
		acceptanceDate:moment().format("MM/DD/YYYY"),
		phoneNumber: endCustomer.phone1
    }    
 }
 if(modalDefaultAction===2)
  body.action='suspend';
  this.showSpinner();
 this.apiCustomerLicenses.updateOrder({modifyOrders:[body]}).then(res=>{
   console.log(res);
   if(res.Result=='Success')
   {
     if(res.BodyText.modifyOrdersDetails[0].status=='success')
     {
        this.setState({showModal:false,orderUpdateError:null});
        setTimeout(()=> this.getCustomerOrders(),3000);
        setTimeout(()=> this.getCustomerOrders(),10000);
     }
     else if(res.BodyText.modifyOrdersDetails[0].status==="failed")
     this.setState({orderUpdateError:res.BodyText.modifyOrdersDetails[0].message})
   }
   this.hideSpinner();
   
 })
}
handleSetOrderStatus=(status)=>{
  
  const {modalDefaultAction,productDetails,currentUser,endCustomer}= this.state;
  let body={   
   action: status,
   orderNumber: productDetails.orderNumber,
   sku: productDetails.sku,
   metaData: 
     {
     firstName : currentUser.firstName,
     lastName : currentUser.lastName,
     isEndCustomer: false
     }
  }  
  this.showSpinner();
  this.apiCustomerLicenses.updateOrder({modifyOrders:[body]}).then(res=>{
    console.log(res);
    if(res.Result=='Success')
    {
      if(res.BodyText.modifyOrdersDetails[0].status=='success')
      {
         this.setState({showModal:false});
         setTimeout(()=> this.getCustomerOrders(),3000);
         setTimeout(()=> this.getCustomerOrders(),10000);
         this.hideSpinner();

      }
    }
    
  })
}
 getModalContent=()=>
 {
  const {showModal,productDetails,modalDefaultAction,orderUpdateError}=this.state;
  const { el ,handleOnClose,handleModalAction,handleChange,handleUpdateOrder ,handleSetOrderStatus} = this;  
  const inactive=productDetails?.lineStatus==='inactive'?true:false;
  //prepare body
  const body=el('div',{key:'body'},[
    el('span',{key:'spanTitle'},'Select an option below to modify your current subscription and press Submit when done or Cancel to return to manage Tenant page'),
    el('div',{key:'divStatus'},[ 
      el('span',{key:"spanStatusText"},'Current status : '),
      el('span',{key:"spanStatusCompleted",className:'green-text'},productDetails?.lineStatus=="active"?'Acitve':''),
      el('span',{key:"spanStatusNotCompleted", className:'red-text'},productDetails?.lineStatus=='inactive'?'In Acitve':''),
      ]),
    el('hr',{key:'hr1'}),
    el('h4',{key:'q1'},'What do you want to do?'),
    el(RadioButtons,{key:'actions',disabled:inactive,value:1,onChange:handleModalAction,items:[{id:1,name:'Change the number of seats for this subscriptions'},
    {id:2,name:'Suspend this Subscription'}
  ]}),
    el('label',{key:'l2'},'Number of Seats : '),
    el('input',{key:'i2',type:'number' ,name:'quantity', min:0,disabled:modalDefaultAction!==1||inactive,value:productDetails?.quantity,onChange:handleChange}),
    el('br',{key:"br1"}),
    el('label',{key:'l3'},'Only applicable when changing seat number '),
    el('span',{key:'s3',className:'error-message'},orderUpdateError),
  ]);
  const footer=el(React.Fragment,{key:'footer'},[
    el('button',{key:"btnCancel",onClick:handleOnClose},"Cancel"),
    !inactive?el('button',{key:"btnSubmit",onClick:handleUpdateOrder},"Submit"):null,   
    inactive?el('button',{key:"btnActivate",onClick:()=>handleSetOrderStatus('activate')},"Activate"):null,   
  ])
  return  el(Modal,{key:'Modal',show:showModal,width:'600px',title:`Modify your ${productDetails?.name}`,onClose:handleOnClose,content:body,footer});
  
 }
 scrollToAddons = () => window.scrollTo(0, this.addonsRef.current.offsetTop)   

  render() {
    const { el } = this;
    const {_showSpinner}=this.state;
    return el("div", null, [
      el(Spinner,{key:'spinner',show:_showSpinner}),
      this.getModalContent(),
      this.getHeader(),
      el("h2", { key: "h2installedSaas" }, "SaaS Applications"),
      this.getSearchResult(),
      el('h2',{key:'h2Addons',ref:this.addonsRef},'AddOns'),
      this.getAddonsElementd(),
     
    ]);
  }
}

export default CMPTDCustomerOrders;

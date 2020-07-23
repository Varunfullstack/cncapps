"use strict";
import AutoComplete from "./../utils/autoComplete.js?v=1";
import Table from './../utils/table/table.js?v=1';
import APICustomerLicenses from './APICustomerLicenses.js?v=1';
import Spinner from './../utils/spinner.js?v=1';

/**
 * searching in TechData customers and link them with CNC customers
 */
class CMPTDCustomerSearch extends React.Component {
  el = React.createElement;
  apiCustomerLicenses;
  apiTechData;
  /**
   * init state
   * @param {*} props
   */
  constructor(props) {
    super(props);
    this.state = {
      search: {
        noOfRecords: 500,
      },
      cncCustomers: [],
      result: [],
      filteredResult: [],
      _showSpinner: false,
    };
    this.apiCustomerLicenses = new APICustomerLicenses();
  }
  showSpinner = () => {
    this.setState({ _showSpinner: true });
  };
  hideSpinner = () => {
    this.setState({ _showSpinner: false });
  };
  componentDidMount() {
    this.showSpinner();
    this.apiCustomerLicenses.getCustomers().then((cncCustomers) => {
      this.setState({ cncCustomers });
      setTimeout(() => this.handleSearch(), 100);
      this.hideSpinner();
    });
  }
  handleChange = ({ currentTarget: input }) => {
    const { result } = this.state;
    if (result.length > 0 && input.value.length > 0) {
      const filteredResult = result.filter((c) => {
        return (
          (c.firstName + " " + c.lastName)
            .toLowerCase()
            .indexOf(input.value.toLowerCase()) >= 0 ||
          c.companyName.toLowerCase().indexOf(input.value.toLowerCase()) >= 0 ||
          c.email.toLowerCase().indexOf(input.value.toLowerCase()) >= 0 ||
          (c.cncCustomerName != null &&
            c.cncCustomerName
              .toLowerCase()
              .indexOf(input.value.toLowerCase()) >= 0)
        );
      });
      this.setState({ filteredResult });
    } else this.setState({ filteredResult: [...result] });
    // const search = { ...this.state.search };
    // search[input.name] = input.value;
    // this.setState({ search });
  };
  getSearchElement(label, name) {
    const { el, handleChange } = this;
    return el(React.Fragment, { key: "frag" + name }, [
      el("td", { key: "td" + name, className: "text-right nowrap" }, label),
      el(
        "td",
        { key: `td${name}Input` },
        el("input", {
          key: name,
          name: name,
          type: "text",
          className: "form-control",
          onChange: handleChange,
        })
      ),
    ]);
  }
  getSearchElements() {
    const { el, handleSearch, handleAddNew } = this;
    return el(
      "table",
      { key: "table", style: { maxWidth: 1000 } },
      el(
        "tbody",
        null,
        el("tr", null, [
          this.getSearchElement("Search", "name"),
          el(
            "td",
            { key: "tdNew", className: "col" },
            el(
              "button",
              {
                key: "NewCustomer",                
                onClick: handleAddNew,
              },
              "Add new"
            )
          ),
        ])
      )
    );
  }
 
  handleSearch = () => {
    console.log("Search", this.state.search);
    this.apiCustomerLicenses
      .searchTechDataCustomers(this.state.search)
      .then((res) => {
        //set cnc customers
        if (res.Result === "Success") {
          // set result
          res.BodyText.endCustomersDetails.map((endCust) => {
            const cncCustomers = this.state.cncCustomers.filter(
              (cnc) => cnc.techDataCustomerId == endCust.endCustomerId
            );
            //console.log(endCust.endCustomerId);
            if (cncCustomers.length > 0) {
              endCust.cncCustomerName = cncCustomers[0].name;
            }
            return endCust;
          });
        }
        return res;
      })
      .then((res) => {
        console.log("result", res);
        if (res.Result === "Success") {
          // set result
          this.setState({
            result: res.BodyText.endCustomersDetails,
            filteredResult: res.BodyText.endCustomersDetails,
          });
        } else {
          this.setState({ result: [], filteredResult: [] });
        }
      });
  };

  handleAddNew = () => {
    if (this.props.onAddNew) this.props.onAddNew();
  };
  handleEdit = (customer) => {
    console.log(customer);
    window.location =
      "/CustomerLicenses.php?action=editCustomer&endCustomerId=" +
      customer.endCustomerId;
  };
  handleSaas = (customer) => {
    console.log(customer);
    // get customer orders;
    window.location = `/CustomerLicenses.php?action=searchOrders&endCustomerId=${customer.endCustomerId}&tap=saas`;
    //    this.apiCustomerLicenses.getSubscriptionsByEndCustomerId(customner.endCustomerId).then(result=>console.log(result))
    //this.apiCustomerLicenses.getSubscriptionsByEmail('mark.perres@ajmhealthcare.ord').then(result=>console.log(result))
  };
  getSearchResult = () => {
    const { search, filteredResult } = this.state;
    const { el, handleEdit, handleSaas } = this;
    const columns = [
      { path: "companyName", label: "StreamOne Company Name", sortable: true },
      
      { path: "cncCustomerName", label: "CNC Customer", sortable: true },
      {
        path: "firstName",
        label: "Contact Name",
        sortable: true,
        content: (c) =>
          el(
            "label",
            { ket: c.endCustomerId + "name" },
            c.firstName + " " + c.lastName
          ),
      },
      { path: "email", label: "Email", sortable: true },
     
      // { path: "createdOn", label: "Created On", sortable: true },
      {
        path: null,
        label: "Edit Company",
        sortable: false,
        content: (c) => el("i", { onClick: () => handleEdit(c),className:'pointer fa fa-pencil',title:"Edit customer details" })
      },
      {
        path: null,
        label: "Edit Licenses",
        sortable: false,
        content: (c) => el("i", { onClick: () => handleSaas(c),className:'pointer fa fa-pencil',title:"Edit customer licences" }),
      },
    ];
    //if(search&& search.result&&search.result.endCustomersDetails!=null)
    {
      return this.el(Table, {
        key: "reaulttable",
        data: filteredResult || [],
        columns: columns,
        defaultSortPath: "companyName",
        defaultSortOrder: "desc",
        pk: "endCustomerId",
      });
    }
  };
  render() {
    const { el } = this;
    const { _showSpinner } = this.state;
    return el("div", null, [
      el(Spinner, { key: "spinner", show: _showSpinner }),
      this.getSearchElements(),
      this.getSearchResult(),
    ]);
  }
}

export default CMPTDCustomerSearch;

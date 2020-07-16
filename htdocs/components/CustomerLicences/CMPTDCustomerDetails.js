"use strict";
import AutoComplete from "./../utils/autoComplete.js?v=1";
import Table from './../utils/table/table.js?v=1';
import APICustomerLicenses from './APICustomerLicenses.js?v=1';
import {Cities} from './../utils/ukCities.js';
/**
 *  Edit TechData customers and link them with CNC customers
 */
class CMPTDCustomerDetails extends React.Component {
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
      data: {
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
        cncCustomerName: "",
        cncCustomerId: "",
      },
      errors: {},
    };
    this.apiCustomerLicenses = new APICustomerLicenses();
  }

  componentDidMount() {
    this.apiCustomerLicenses
      .getCustomers()
      .then((cncCustomers) => this.setState({ cncCustomers }));

    const queryParams = new URLSearchParams(window.location.search);
    const endCustomerId = queryParams.get("endCustomerId");
    if (endCustomerId && endCustomerId != "")
      this.getCustomerDetails(endCustomerId);
  }
  handleChange = ({ currentTarget: input }) => {
    const data = { ...this.state.data };
    data[input.name] = input.value;
    this.setState({ data });
  };
  getCustomerElement(
    label,
    name,
    content = null,
    value = "",
    required = true,
    errorMessage = ""
  ) {
    const { el, handleChange } = this;
    return el("tr", { key: "tr" + name }, [
      el("td", { key: "td" + name, className: "text-right nowrap" }, label),
      el(
        "td",
        { key: `td${name}Input` },
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
        { key: "tdError" + name },
        el(
          "span",
          { key: "error" + name, className: "error-message" },
          errorMessage
        )
      ),
    ]);
  }
  getCustomerElements() {
    const {
      el,
      handleCncCustomerOnSelect,
      handleCityOnSelect,
      handleOnSave,
      handleOnCancel
    } = this;
    const { cncCustomers, data, errors } = this.state;
    let errorMessage = "";
    if (typeof errors === "string") errorMessage = errors;
    return el(
      "table",
      { key: "table", style: { maxWidth: 1000 } },
      el("tbody", null, [
        this.getCustomerElement(
          "Company Name",
          "companyName",
          null,
          data.companyName,
          true,
          errors["companyName"]
        ),
        this.getCustomerElement(
          "First Name",
          "firstName",
          null,
          data.firstName,
          true,
          errors["firstName"]
        ),
        this.getCustomerElement(
          "Last Name",
          "lastName",
          null,
          data.lastName,
          true,
          errors["lastName"]
        ),
        this.getCustomerElement(
          "Title",
          "title",
          null,
          data.title,
          false,
          errors["title"]
        ),
        this.getCustomerElement(
          "Email",
          "email",
          null,
          data.email,
          true,
          errors["email"]
        ),
        this.getCustomerElement(
          "Phone 1",
          "phone1",
          null,
          data.phone1,
          true,
          errors["phone1"]
        ),
        this.getCustomerElement(
          "Phone 2",
          "phone2",
          null,
          data.phone2,
          false,
          errors["phone2"]
        ),
        this.getCustomerElement(
          "Address Line 1",
          "addressLine1",
          null,
          data.addressLine1,
          true,
          errors["addressLine1"]
        ),
        this.getCustomerElement(
          "Address Line 2",
          "addressLine2",
          null,
          data.addressLine2,
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
            value:data.city,
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
          data.state,
          false,
          errors["state"]
        ),
        this.getCustomerElement(
          "Country",
          "country",
          null,
          data.country,
          true,
          errors["country"]
        ),
        this.getCustomerElement(
          "Postal Code",
          "postalCode",
          null,
          data.postalCode,
          true,
          errors["postalCode"]
        ),
        this.getCustomerElement(
          "CNC Customer",
          "cncCustomerId",
          el(AutoComplete, {
            key: "customersAuto",
            errorMessage: "No Customer found",
            items: cncCustomers,
            displayLength: "40",
            displayColumn: "name",
            pk: "id",
            value:data.cncCustomerName,
            onSelect: handleCncCustomerOnSelect,
          })
        ),
        el(
          "tr",
          { key: "trError" },
          el(
            "td",
            { key: "tdError", colSpan: 3 },
            el(
              "span",
              { key: "spanerror", className: "error-message" },
              errorMessage
            )
          )
        ),
        el(
          "tr",
          { key: "trSave" },
          el(
            "td",
            { key: "tdSave", colSpan: 2 },
            [el("button", { key: "btnSave", onClick: handleOnSave }, "Save"),
            el("button", { key: "btnCancel", onClick: handleOnCancel }, "Cancel")]
          )
        ),
      ])
    );
  }
  handleCncCustomerOnSelect = (event) => {
    if (event != null) {
      const data = { ...this.state.data };
      data.cncCustomerId = event.id;
      data.cncCustomerName = event.name;
      this.setState({ data });
      console.log(event);
    }
  };
  handleCityOnSelect = (event) => {
    if (event != null) {
      const data = { ...this.state.data };
      data.city = event.name;
      this.setState({ data });
      console.log(event);
    }
  };
  handleOnSave = () => {
    console.log(this.state.data);
    const { data, mode } = this.state;
    if (mode == "insert") {
      this.apiCustomerLicenses
        .addTechDataCustomer(this.state.data)
        .then((result) => {
          console.log("add customer result", result);
          if (result.Result == "Failed") {
            const errors = result.ErrorMessage;
            this.setState({ errors });
          }
        });
    } else if (mode == "edit") {
      console.log('edit customer')
      this.apiCustomerLicenses
      .updateTechDataCustomer(this.state.data.id,this.state.data)
      .then((result) => {
        console.log("update customer result", result);
        if (result.Result == "Failed") {
          const errors = result.ErrorMessage;
          this.setState({ errors });
        }
        else   if (result.Result == "Success") 
        {
          this.handleOnCancel();
        }
      });
    }
  };

  getCustomerDetails = (endCustomerId) => {
    this.apiCustomerLicenses.getCustomerDetails(endCustomerId).then((res) => {
      console.log("Customer", res);
      if (res.Result == "Success") {
        const data = { ...res.BodyText.endCustomerDetails };
        this.setState({ data, mode: "edit" });
      }
    });
  };
  handleOnCancel=()=>{
    window.location='/CustomerLicenses.php?action=searchCustomers'

  }
  render() {
    const { el } = this;    
    return el("div", null, [this.getCustomerElements()]);
  }
}

export default CMPTDCustomerDetails;

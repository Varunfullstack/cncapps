import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle.js";
import APIItemBillingCategory from "./services/APIItemBillingCategory.js";
import "../style.css";
import "./ItemBillingCategoryComponent.css";

class ItemBillingCategoryComponent extends MainComponent {
  api = new APIItemBillingCategory();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      showModal: false,
      mode: "new",
      data: { ...this.getInitData() },
      items: [],
    };
  }
  getInitData = () => {
    return {
      id: null,
      name: "",
      arrearsBilling: 0,       
    };
  };
  componentDidMount() {
    this.getData();
  }

  getData = () => {
    this.api.getAll().then((res) => {      
      this.setState({ items: res.data });
    },error=>{
      this.alert("Error in loading data")
    });
  };

  getDataTable = () => {
    const columns = [
      {
        path: "name",
        label: "Name",
         hdClassName: "text-center",
        //icon: "fal fa-2x fa-text color-gray2 pointer",
        sortable: true,
        //className: "text-center",
      },
     
      {
        path: "arrearsBilling",
        label: "Arrears Billing	",         
        hdClassName: "text-center",
        //icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content:(item)=><Toggle checked={item.arrearsBilling} disabled={true}></Toggle>
      },      
      {
        path: "edit",
        label: "",
        hdToolTip: "Edit",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-edit color-gray2 pointer",
        sortable: false,
        className: "text-center",
        content: (template) =>
          this.getEditElement(template, () => this.showEditModal(template)),
      },
      {
        path: "trash",
        label: "",
        hdToolTip: "Delete",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-trash-alt color-gray2 pointer",
        sortable: false,
        className: "text-center",
        content: (template) =>
          this.getDeleteElement(template, () => this.handleDelete(template)),
      },
    ];

    return (
      <Table
        style={{ width: 900, marginTop: 20 }}
        onOrderChange={this.handleOrderChange}
        allowRowOrder={true}
        key="items"
        pk="id"
        columns={columns}
        data={this.state.items || []}
        search={true}
      ></Table>
    );
  };
  showEditModal = (data) => {
    this.setState({ showModal: true, data:{...data}, mode: "edit" });
  };
  handleDelete = async (type) => {
    const conf = await this.confirm(
      "Are you sure to delete this Item?"
    );
    if (conf)
      this.api.deleteItem(type.id).then((res) => {
        if (res.state) this.getData();
        else this.alert(res.error);
      });
  };
 
  handleNewTemplate = () => {
    this.setState({
      mode: "new",
      showModal: true,
      data: { ...this.getInitData() },
    });
  };
  hideModal = () => {
    this.setState({ showModal: false });
  };
  getModalElement = () => {
    const { mode, data, items } = this.state;
    return (
      <Modal
        width={500}
        show={this.state.showModal}
        title={mode == "new" ? "Add New Item" : "Edit Item"}
        onClose={this.hideModal}
        content={
          <div key="content" id="formData">
            <div className="form-group">
              <label>Name</label>
              <input
                required
                value={data.name}
                type="text"
                name=""
                id=""
                className="form-control"
                onChange={(event) =>
                  this.setValue("name", event.target.value)
                }
              />
            </div>
             
            <div className="form-group">
              <label>Arrears Billing</label>
              <Toggle checked={data.arrearsBilling} onChange={()=>this.setValue("arrearsBilling", data.arrearsBilling?0:1)}>
              </Toggle>              
            </div>
            
          </div>
        }
        footer={
          <div key="footer">
            <button onClick={this.handleSave}>Save</button>
            <button onClick={this.hideModal}>Cancel</button>
          </div>
        }
      ></Modal>
    );
  };
  handleSave = () => {
    const { data } = this.state;
    if (!this.isFormValid("formData")) {
      this.alert("Please enter all required inputs.");
      return;
    }   
    let callApi;
    if (data.id==null) callApi = this.api.add(data);
    else callApi = this.api.update(data);
    callApi
      .then((result) => {
        if (result.state) {
          this.setState({ showModal: false });
          this.getData();
        } else {
          this.alert(result.error);
        }
      })
      .catch((error) => {
        console.log(error);
        this.alert(error.error || "Error in save data.");
      });
  };
  render() {
    return (
      <div>
        <Spinner show={this.state.showSpinner}></Spinner>
        <ToolTip title="New Item" width={30}>
          <i
            className="fal fa-2x fa-plus color-gray1 pointer"
            onClick={this.handleNewTemplate}
          ></i>
        </ToolTip>
        {this.getConfirm()}
        {this.getAlert()}
        {this.getModalElement()}
        {this.getDataTable()}
      </div>
    );
  }
}

export default ItemBillingCategoryComponent;
document.addEventListener("DOMContentLoaded", () => {
  const domContainer = document.querySelector("#reactItemBillingCategory");
  if (domContainer)
    ReactDOM.render(React.createElement(ItemBillingCategoryComponent), domContainer);
});

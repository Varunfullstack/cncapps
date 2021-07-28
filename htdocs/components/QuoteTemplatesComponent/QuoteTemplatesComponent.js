import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import Spinner from "../shared/Spinner/Spinner";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle.js";
import APIQuoteTemplates from "./services/APIQuoteTemplates.js";
import "../style.css";
import "./QuoteTemplatesComponent.css";
import { replaceQuotes } from "../utils/utils.js";

class QuoteTemplatesComponent extends MainComponent {
  api = new APIQuoteTemplates();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      showSpinner: false,
      showModal: false,
      mode: "new",
      data: { ...this.getInitData() },
      templates: [],
    };
  }
  getInitData = () => {
    return {
      id: null,
      description: "",
      linkedSalesOrderId: "",
      sortOrder: 0,
    };
  };
  componentDidMount() {
    this.getData();
  }

  getData = () => {
    this.api.getAllTemplates().then((res) => {
      this.setState({ templates: res.data });
      console.log(res.data);
    });
  };

  getDataTable = () => {
    const columns = [
      {
        path: "description",
        label: "",
        hdToolTip: "Description",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-text color-gray2 pointer",
        sortable: true,
        //className: "text-center",
        content:(item)=><div dangerouslySetInnerHTML={{__html: item.description}}></div>
      },
      {
        path: "linkedSalesOrderId",
        label: "",
        hdToolTip: "Sales Order",
        hdClassName: "text-center",
        icon: "fal fa-2x fa-tag color-gray2 pointer",
        sortable: true,
        className: "text-center",
        content: (template) => (
          <a
            href={`SalesOrder.php?action=displaySalesOrder&ordheadID=${template.linkedSalesOrderId}`}
            target="_blank"
          >
            {template.linkedSalesOrderId}
          </a>
        ),
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
        key="templates"
        pk="id"
        columns={columns}
        data={this.state.templates || []}
        search={true}
      ></Table>
    );
  };
  showEditModal = (data) => {
    this.setState({ showModal: true, data:{...data}, mode: "edit" });
  };
  handleDelete = async (type) => {
    const conf = await this.confirm(
      "Are you sure to delete this quote template?"
    );
    if (conf)
      this.api.deleteTemplate(type.id).then((res) => {
        if (res.state) this.getData();
        else this.alert(res.error);
      });
  };
  handleOrderChange = async (current, next) => {
    const { templates } = this.state;
    current=templates.find(t=>t.id==current.id);
    next=templates.find(t=>t.id==next.id);
    current.description=replaceQuotes(current.description);
    next.description=replaceQuotes(next.description);
    if (next) {
      current.sortOrder = next.sortOrder;
      next.sortOrder = current.sortOrder + 0.001;
      await this.api.updateTemplate(next);
    }
    if (!next) {
      current.sortOrder = Math.max(...templates.map((i) => i.sortOrder)) + 0.001;
    }

    await this.api.updateTemplate(current);
    this.getData();
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
    const { mode, data, templates } = this.state;
    return (
      <Modal
        width={500}
        show={this.state.showModal}
        title={mode == "new" ? "Add New Template" : "Edit Template"}
        onClose={this.hideModal}
        content={
          <div key="content" id="formData">
            <div className="form-group">
              <label>Description</label>
              <input
                required
                value={replaceQuotes(data.description)}
                type="text"
                name=""
                id=""
                className="form-control"
                onChange={(event) =>
                  this.setValue("description", event.target.value)
                }
              />
            </div>
            <div className="form-group">
              <label>Linked Sales Order Id </label>
              <input
                required
                value={data.linkedSalesOrderId}
                type="number"
                name=""
                id=""
                className="form-control"
                onChange={(event) =>
                  this.setValue("linkedSalesOrderId", event.target.value)
                }
              />
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
    let updateData = {
      description: data.description,
      linkedSalesOrderId: data.linkedSalesOrderId,
      id: data.id,
      sortOrder: data.sortOrder,
    };

    this.api
      .updateTemplate(updateData)
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
        <ToolTip title="New Type" width={30}>
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

export default QuoteTemplatesComponent;
document.addEventListener("DOMContentLoaded", () => {
  const domContainer = document.querySelector("#reactQuoteTemplatesComponent");
  if (domContainer)
    ReactDOM.render(React.createElement(QuoteTemplatesComponent), domContainer);
});

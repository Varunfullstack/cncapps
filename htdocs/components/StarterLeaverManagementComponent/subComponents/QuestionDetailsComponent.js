import MainComponent from "../../shared/MainComponent.js";
import React from "react";

import CustomerSearch from "../../shared/CustomerSearch.js";
import Toggle from "../../shared/Toggle.js";
import Modal from "../../shared/Modal/modal.js";
import APIStarterLeaverManagement from "../services/APIStarterLeaverManagement.js";

export default class QuestionDetailsComponent extends MainComponent {
  api = new APIStarterLeaverManagement();
  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      data: {
        customerID: "",
        type: "y/n",
        formType: "starter",
        required: "",
        multi: "",
        options: [],
        name: "",
        label: "",
      },
      optionValue:""
    };
  }

  componentDidMount() {}

  handleCancel = () => {
    if (this.props.onClose) this.props.onClose();
  };
  handleDeleteOption = () => {};
  

  hanldeSave = () => {
    const { data } = this.state;
    if(!this.isFormValid("form"))
    {
        this.alert("Please add all required inputs");
        return;
    }
    console.log(data);
  };
  handleAddOptions = () =>{
      const {optionValue,data}=this.state;
    if(this.state.optionValue!="")
    {
        const indx=data.options.indexOf(optionValue);
        if(indx>=0)
        this.alert("Option exist")
        else
        data.options.push(optionValue);
        this.setState({data,optionValue:""});
    }
    else
    this.alert("Please enter the option text")

  }
  getContent = () => {
    const { data } = this.state;
    if (!this.props.show) return null;

    return (
      <Modal
        show={this.props.show}
        title="Add New Question"
        width={600}
        footer={
          <div key="footer" onClose={this.handleCancel}>
            <button onClick={this.handleCancel}>Cancel</button>
            <button onClick={this.hanldeSave}>Save</button>
          </div>
        }
      >
        <div style={{ display: "flex", flexDirection: "row" }} id="form">
          <table>
            <tbody>
              <tr>
                <td>Customer</td>
                <td>
                  <CustomerSearch
                    onChange={(customer) =>
                      this.setValue("customerID", customer.id)
                    }
                    width={250}
                  ></CustomerSearch>
                </td>
              </tr>
              <tr>
                <td>Starter/Leaver</td>
                <td>
                  <select
                    className="form-contol"
                    value={data.formType}
                    onChange={(event) =>
                      this.setValue("formType", event.target.value)
                    }
                  >
                    <option value="starter">Starter</option>
                    <option value="leaver">Leaver</option>
                  </select>
                </td>
              </tr>
              <tr>
                <td>Name(no space allowed)</td>
                <td>
                  <input
                    required
                    className="form-contol"
                    value={data.name}
                    onChange={(event) =>
                      this.setValue("name", event.target.value)
                    }
                  ></input>
                </td>
              </tr>
              <tr>
                <td>Question Label </td>
                <td>
                  <input
                    required
                    className="form-contol"
                    value={data.label}
                    onChange={(event) =>
                      this.setValue("label", event.target.value)
                    }
                  ></input>
                </td>
              </tr>
              <tr>
                <td>Required? </td>
                <td>
                  <Toggle
                    checked={data.required}
                    onChange={() => this.setValue("required", !data.required)}
                  ></Toggle>
                </td>
              </tr>
              <tr>
                <td>Question Type </td>
                <td>
                  <select
                    className="form-contol"
                    value={data.type}
                    onChange={(event) =>
                      this.setValue("type", event.target.value)
                    }
                  >
                    <option value="y/n">Yes/No</option>
                    <option value="multi">Multiple Choice</option>
                    <option value="free">Free Type</option>
                  </select>
                </td>
              </tr>
              <tr style={{display:data.type=="multi"?"":"none"}}>
                <td valign="top">Question Options </td>
                <td>
                  <table className="table table-striped">
                    <thead>
                      <tr>
                        <th>
                          <input className="form-control" value={this.state.optionValue} onChange={(event)=>this.setState({optionValue:event.target.value})}></input>
                        </th>
                        <th style={{width:40,textAlign:"center"}}>
                          <i className="fal fa-2x fa-plus pointer " style={{color:"white"}} onClick={this.handleAddOptions}></i>
                        </th>
                      </tr>
                    </thead>
                    <tbody>
                      {data.options.map((key) => (
                        <tr>
                          <td>{key}</td>
                          <td>
                            {this.getDeleteElement(
                              key,
                              this.handleDeleteOption
                            )}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </Modal>
    );
  };

  render() {
    return (
      <div>
        {this.getAlert()}
        {this.getContent()}
      </div>
    );
  }
}

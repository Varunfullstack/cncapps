import React from "react";
import {params} from "../utils/utils";
import APICustomers from "../services/APICustomers";
import MainComponent from "../shared/MainComponent";
import Table from "../shared/table/table";
import ToolTip from "../shared/ToolTip";
import Spinner from "../shared/Spinner/Spinner";
import Modal from "../shared/Modal/modal.js";
import Toggle from "../shared/Toggle";

export default class CustomerOrdersComponent extends MainComponent {
    api = new APICustomers();

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            customerId: null,
            orders: [],
            reset: false,
            showModal: false,
            isNew: true,
            data: {...this.getInitData()},
        };
    }

    componentDidMount() {
        this.getData();
    }

    getData = () => {
        const customerId = params.get("customerID");
        this.api.getCustomerOrders(customerId).then((res) => {
            this.setState({orders: res.data, customerId});
        });
    };
    getTable = () => {
        const columns = [
            {
                path: "id",
                label: "ID",
                hdToolTip: "ID",
                sortable: true,
                width: 200,
                content: (order) => (
                    <a
                        style={{color: "black"}}
                        href={`${order.url}`}
                        target="_blank"
                    >
                        {order.id}
                    </a>
                ),
            },
            {
                path: "type",
                label: "Type",
                hdToolTip: "Type",
                sortable: true,
                width: 200,
            },
            {
                path: "date",
                label: "Date",
                hdToolTip: "Date",
                sortable: true,
                width: 200,
            },
            {
                path: "custPORef",
                label: "POR Ref",
                hdToolTip: "POR Ref",
                sortable: true,
                width: 200,
            },
            {
                path: "edit",
                label: "",
                hdToolTip: "Edit order",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: false,
                content: (order) =>
                    this.getEditElement(order, () => this.handleEdit(order)),
            },
            {
                path: "delete",
                label: "",
                hdToolTip: "Delete order",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: false,
                content: (order) =>
                    this.getDeleteElement(
                        order,
                        () => this.handleDelete(order),
                        order.isDeletable
                    ),
            },
        ];
        return (
            <Table
                key="orders"
                pk="id"
                style={{maxWidth: 1300}}
                columns={columns}
                data={this.state.orders || []}
                search={true}
            ></Table>
        );
    };

    getInitData() {
        return {
            id: "",
            customerID: params.get("customerID"),
            type: "",
            date: "",
            custPORef: "",
        };
    }

    handleEdit = (order) => {
        this.setState({data: order, showModal: true, isNew: false});
    };

    handleDelete = async (order) => {
        if (await this.confirm("Are you sure you want to delete this order?")) {
            this.APICustomers.deleteCustomerOrder(order.id).then((res) => {
                this.getData();
            });
        }
    };

    handleNewItem = () => {
        this.setState({
            showModal: true,
            isNew: true,
            data: {...this.getInitData()},
        });
    };

    getCheckBox = (name, yesNo = true) => {
        const {data} = this.state;
        let trueValue = "Y";
        let falseValue = "N";
        if (!yesNo) {
            trueValue = 1;
            falseValue = 0;
        }
        return (
            <input
                checked={data[name] == trueValue}
                onChange={() =>
                    this.setValue(name, data[name] == trueValue ? falseValue : trueValue)
                }
                type="checkbox"
            />
        );
    };

    handleClose = () => {
        this.setState({showModal: false});
    };

    handleSave = () => {
        const {data, isNew} = this.state;
        if (!this.isFormValid('orderformdata')) {
            this.alert("Please enter required data");
            return;
        }
        if (!isNew) {
            this.api.updateCustomerOrder(data).then((res) => {
                if (res.status == 200) {
                    this.setState({showModal: false, reset: true}, () =>
                        this.getData()
                    );
                }
            });
        } else {
            data.id = null;
            this.api.addCustomerOrder(data).then((res) => {
                if (res.status == 200) {
                    this.setState({showModal: false, reset: true}, () =>
                        this.getData()
                    );
                }
            });
        }
    };

    getModal = () => {
        const {isNew, showModal} = this.state;
        if (!showModal) return null;
        return (
            <Modal
                width={500}
                title={isNew ? "Create Order" : "Update Order"}
                show={showModal}
                content={this.getModalContent()}
                footer={
                    <div key="footer">
                        <button onClick={this.handleClose} className="btn btn-secodary">
                            Cancel
                        </button>
                        <button onClick={this.handleSave}>Save</button>
                    </div>
                }
                onClose={this.handleClose}
            ></Modal>
        );
    };

    getModalContent = () => {
        const {data} = this.state;
        return (
            <div key="content" id='orderformdata'>
                <table className="table">
                    <tbody>
                    <tr>
                        <td className="text-right">Site Address</td>
                        <td>
                            <input
                                required
                                value={data.add1 || ""}
                                onChange={(event) =>
                                    this.setValue("add1", event.target.value)
                                }
                                className="form-control"
                            />
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <input
                                required
                                value={data.add2 || ""}
                                onChange={(event) =>
                                    this.setValue("add2", event.target.value)
                                }
                                className="form-control"
                            />
                        </td>
                    </tr>
                    <tr>
                        <td className="text-right">Active?</td>
                        <td>
                            <Toggle
                                checked={data.activeFlag === "Y"}
                                onChange={() =>
                                    this.setValue(
                                        "activeFlag",
                                        data.activeFlag === "Y" ? "N" : "Y"
                                    )
                                }
                            ></Toggle>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        );
    };

    render() {
        return (
            <div>
                <Spinner show={this.state.showSpinner}/>
                <ToolTip title="New Order" width={30}>
                    <i
                        className="fal fa-2x fa-plus color-gray1 pointer"
                        onClick={this.handleNewItem}
                    />
                </ToolTip>
                {this.getConfirm()}
                {this.getAlert()}
                {this.getTable()}
                <div className="modal-style">{this.getModal()}</div>
            </div>
        );
    }
}

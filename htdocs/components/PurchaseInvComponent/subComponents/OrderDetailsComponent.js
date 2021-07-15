import moment from "moment";
import React from "react";
import MainComponent from "../../shared/MainComponent.js";
import Spinner from "../../shared/Spinner/Spinner";
import Table from "../../shared/table/table.js";
import Toggle from "../../shared/Toggle.js";
import APIPurchaseInv from "../services/APIPurchaseInv.js";

export default class OrderDetailsComponent extends MainComponent {
    api = new APIPurchaseInv();
    tableTimeChange;

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            showSpinner: false,
            showModal: false,
            lines: [],
            invoiceNo: "",
            invoiceDate: moment().format("YYYY-MM-DD"),
            recieveAll: false
        };
    }

    componentDidMount() {
        this.getData();
        document.addEventListener("keydown", this.handleKeyDown)

    }

    handleKeyDown = (ev) => {
        if (ev.key == "F5") {
            this.handleInvoiceAll(!this.state.recieveAll);
            ev.preventDefault();
            return false;
        }

    }

    componentWillUnmount() {
        document.removeEventListener("keydown", this.handleKeyDown);
    }

    getData = () => {
        const {porheadID} = this.props;
        if (porheadID)
            this.api.getOrderLines(porheadID).then(
                (res) => {
                    const lines = res.data;
                    lines.map((line, index) => line.id = index + 1);
                    this.setState({lines: res.data});
                },
                (error) => this.alert("Error in loading data")
            );
    };

    getDataTable = () => {
        const {lines} = this.state;
        const columns = [
            {
                path: "description",
                label: "Description",
                hdClassName: "text-center",
                sortable: true,
            },
            {
                path: "qtyOrdered",
                label: "Ordered",
                hdClassName: "text-center",
                sortable: true,
                className: "text-center",
            },
            {
                path: "qtyOS",
                label: "OS",
                hdClassName: "text-center",
                sortable: true,
                className: "text-center",
            },
            {
                path: "curPOUnitCost",
                label: "Price",
                hdClassName: "text-center",
                sortable: true,
                className: "text-center",
            },
            {
                path: "qtyToInvoice",
                label: "Inv Qty	",
                hdClassName: "text-center",
                sortable: true,
                className: "text-center",
                content: (order) => (
                    <div style={{display: "flex", justifyContent: "center"}}>
                        <input
                            type="number"
                            className="form-control"
                            style={{width: 100}}
                            defaultValue={order.qtyToInvoice}
                            onChange={(event) =>
                                this.handleOrderChange(
                                    order,
                                    "qtyToInvoice",
                                    parseInt(event.target.value)
                                )
                            }
                        ></input>
                    </div>
                ),
            },
            {
                path: "curInvUnitCost",
                label: "Inv Price	",
                hdClassName: "text-center",
                sortable: true,
                className: "text-center",
                content: (order) => (
                    <div style={{display: "flex", justifyContent: "center"}}>
                        <input
                            type="number"
                            className="form-control"
                            style={{width: 100}}
                            defaultValue={order.curInvUnitCost}
                            onChange={(event) =>
                                this.handleOrderChange(
                                    order,
                                    "curInvUnitCost",
                                    parseFloat(event.target.value)
                                )
                            }
                        ></input>
                    </div>
                ),
            },
            {
                path: "curInvTotalCost",
                label: "Total",
                hdClassName: "text-center",
                sortable: true,
                className: "text-center",
                footerContent: () => this.getTotal("curInvTotalCost")
            },
            {
                path: "curVAT",
                label: "VAT",
                hdClassName: "text-center",
                sortable: true,
                className: "text-center",
                content: (order) => (
                    <div style={{display: "flex", justifyContent: "center"}}>
                        <input
                            type="number"
                            className="form-control"
                            style={{width: 100}}
                            defaultValue={order.curVAT}
                            onChange={(event) =>
                                this.handleOrderChange(
                                    order,
                                    "curVAT",
                                    parseInt(event.target.value)
                                )
                            }
                        ></input>
                    </div>
                ),
                footerContent: () => this.getTotal("curVAT")
            },
            {
                path: "serialNo",
                label: "Serial No",
                hdClassName: "text-center",
                sortable: true,
                content: (item) => (
                    <div style={{display: "flex", justifyContent: "center"}}>
                        <input
                            disabled={item.lineDisabled || item.disabled}
                            className="form-control"
                            style={{width: 150}}
                            defaultValue={item.serialNo}
                            id={`serialNo${item.id}`}
                            // onChange={(event) =>
                            //   this.handleOrderChange(order, "serialNo", event.target.value)
                            // }
                        ></input>
                    </div>
                ),
            },
            {
                path: "warrantyID",
                label: "Warranty",
                hdClassName: "text-center",
                sortable: true,
                content: (order) => (
                    <select
                        disabled={order.lineDisabled || order.disabled}
                        className="form-control"
                        value={order.warrantyID || ""}
                        onChange={(event) =>
                            this.handleOrderChange(order, "warrantyID", event.target.value)
                        }
                    >
                        <option>N/A</option>
                        {order.warranties.map((w, indx) => (
                            <option key={indx} value={w.warrantyID}>
                                {w.warrantyDescription}
                            </option>
                        ))}
                    </select>
                ),
            },
            {
                path: "renew",
                label: "Renew",
                hdClassName: "text-center",
                sortable: true,
                className: "text-center",
                content: (order) => (
                    <Toggle
                        disabled={order.lineDisabled || order.disabled}
                        checked={order.renew}
                        onChange={(event) =>
                            this.handleOrderChange(order, "renew", !order.renew)
                        }
                    ></Toggle>
                ),
            },
        ];

        return (
            <Table
                style={{marginTop: 20}}
                key="ordersTable"
                pk="itemID"
                columns={columns}
                data={lines || []}
                search={false}
                hover={false}
                hasFooter={true}
            ></Table>
        );
    };
    updateItems = (lines) => {
        lines.map(line => {
            const serialNo = document.getElementById(`serialNo${line.id}`).value;
            line.serialNo = serialNo;
        })
    }
    calcTotal = () => {
        const {lines} = this.state;
        const {vatRate} = this.props;
        lines.map((line) => {
            line.curInvTotalCost = (line.qtyToInvoice * line.curInvUnitCost).toFixed(
                2
            );
            line.curVAT = (line.curInvTotalCost * vatRate * 0.01).toFixed(2);
        });
        this.setState({lines});
    };
    getTotal = (field) => {
        const {lines} = this.state;
        const total = lines.reduce(
            (total, cur) => (parseFloat(total ?? 0) + parseFloat(cur[field] ?? 0)).toFixed(2),
            0
        );
        return <div style={{textAlign: "center"}}>{total}</div>;
    };
    handleOrderChange = (item, prop, value) => {
        const {lines} = this.state;
        if (this.tableTimeChange) clearTimeout(this.tableTimeChange);
        this.tableTimeChange = setTimeout(() => {
            const line = lines.find((o) => o.id == item.id);
            line[prop] = value;
            this.calcTotal();
        }, 1000);
    };

    handleSupplierChange = (supplier) => {
        this.setFilter("supplierID", supplier?.id || "");
    };
    handlePurchaseOrder = () => {
        const {porheadID} = this.props;
        window.open(
            `PurchaseOrder.php?action=display&porheadID=${porheadID}`,
            "_blank"
        );
    };
    handleUpdate = () => {
        const {lines, invoiceDate, invoiceNo} = this.state;
        const {porheadID} = this.props;
        this.updateItems(lines);
        const linesToReceive = lines.filter((l) => l.qtyToInvoice > 0);
        if (linesToReceive.length == 0) {
            this.alert("Please enter at least one value to invoice");
            return;
        }
        const items = lines.map(line => {
            return {
                description: line.description,
                curInvTotalCost: line.curInvTotalCost,
                curInvUnitCost: line.curInvUnitCost,
                curPOUnitCost: line.curPOUnitCost,
                curVAT: line.curVAT,
                itemID: line.itemID,
                orderSequenceNo: line.orderSequenceNo,
                partNo: line.partNo,
                qtyOS: line.qtyOS,
                qtyOrdered: line.qtyOrdered,
                qtyToInvoice: line.qtyToInvoice,
                renew: line.renew,
                requireSerialNo: line.requireSerialNo,
                sequenceNo: line.sequenceNo,
                serialNo: line.serialNo,
                warrantyID: line.warrantyID,
            }
        })
        const data = {
            porheadID,
            items: items,
            invoiceNo,
            invoiceDate
        }
        this.api
            .updateInvoice(data)
            .then((res) => {
                if (res.data.type == "A") {
                    this.props.onClose();
                } else {
                    this.getData();
                }
            })
            .catch((res) => {
                this.alert(res.error);
            });
    };
    handleInvoiceAll = (value) => {
        let {lines, recieveAll} = this.state;
        this.updateItems(lines);
        lines.map((line) => (line.qtyToInvoice = value ? line.qtyOS : 0));
        this.setState({lines, recieveAll: !recieveAll}, () => this.calcTotal());

    };
    getHeader = () => {
        const {invoiceNo, invoiceDate} = this.state;
        return (
            <table>
                <tbody>
                <tr>
                    <td style={{textAlign: "right"}}>Purchase Invoice No</td>
                    <td><input type="text" className="form-control" value={invoiceNo}
                               onChange={(event) => this.setState({invoiceNo: event.target.value})}></input></td>
                    <td style={{textAlign: "right"}}>Invoice Date</td>
                    <td><input type="date" className="form-control" value={invoiceDate}
                               onChange={(event) => this.setState({invoiceDate: event.target.value})}></input></td>
                    <td style={{textAlign: "right"}}>Invoice All</td>
                    <td><Toggle width={30} onChange={this.handleInvoiceAll} checked={this.state.recieveAll}></Toggle>
                    </td>
                </tr>

                </tbody>
            </table>
        );
    };

    render() {
        return (
            <div>
                <Spinner show={this.state.showSpinner}></Spinner>
                {this.getAlert()}
                {this.getHeader()}
                {this.getDataTable()}
                <div className="modal-footer">
                    <button
                        onClick={() => this.openTab(`PurchaseOrder.php?action=display&porheadID=${this.props.porheadID}`)}>Purchase
                        Order
                    </button>
                    <button
                        onClick={() => this.openPopup(`SalesOrder.php?action=displaySalesOrder&ordheadID=${this.props.ordheadID}&htmlFmt=popup`)}>Sales
                        Order
                    </button>

                    <button onClick={this.handleUpdate}>Update</button>
                </div>
            </div>
        );
    }
}

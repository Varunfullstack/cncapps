import MainComponent from "../shared/MainComponent.js";
import React from "react";
import ReactDOM from "react-dom";
import Spinner from "../shared/Spinner/Spinner";

import APIItems from "./services/APIItems.js";
import Table from "../shared/table/table.js";
import ToolTip from "../shared/ToolTip.js";
import Modal from "../shared/Modal/modal.js";
import APIItemTypes from "../ItemTypeComponent/services/APIItemTypeComponent.js";
import APIManufacturer from "../ManufacturerComponent/services/APIManufacturer.js";
import ItemSearchComponent from "../shared/ItemSearchComponent.js";
import '../style.css';
import './ItemsComponent.css';

class ItemsComponent extends MainComponent {
    api = new APIItems();
    apiItemType = new APIItemTypes();
    apiManufacturer = new APIManufacturer();
    scrollTimer;

    constructor(props) {
        super(props);
        this.state = {
            ...this.state,
            filter: {
                limit: 100,
                page: 1,
                orderBy: 'description',
                orderDir: 'asc',
                q: ''
            },
            reset: false,
            items: [],
            showSpinner: false,
            showModal: false,
            isNew: true,
            data: {...this.getInitData()},
            itemTypes: [],
            warranties: [],
            renewalTypes: [],
            itemBillingCategories: [],
            manufactureries: [],
            childItem: null,
            childItems: []
        };
    }

    componentDidMount() {
        this.getData();
        window.addEventListener('scroll', this.handleScroll, true);
        // this.apiItemType.getAllTypes().then(itemTypes=>this.setState({itemTypes:sort(itemTypes.data,'description')}));
        // this.api.getWarranty().then(res=>this.setState({warranties:res.data}));
        // this.api.getRenewalTypes().then(res=>this.setState({renewalTypes:res.data}));
        // this.api.getItemBillingCategory().then(res=>this.setState({itemBillingCategories:res.data}));
    }

    componentWillUnmount() {
        window.removeEventListener('scroll', this.handleScroll);
    }

    getLookups() {

        if (this.state.manufactureries.length == 0) {
            this.setState({showSpinner: true});
            Promise.all([
                this.apiItemType.getAllTypes().then(res => res.data),
                this.api.getWarranty().then(res => res.data),
                this.api.getRenewalTypes().then(res => res.data),
                this.api.getItemBillingCategory().then(res => res.data),
                this.apiManufacturer.getTypeList().then(res => res.data)
            ]).then(([itemTypes, warranties, renewalTypes, itemBillingCategories, manufactureries]) => {
                this.setState({
                    itemTypes,
                    warranties,
                    renewalTypes,
                    itemBillingCategories,
                    manufactureries,
                    showSpinner: false
                })
            })
            //this.apiManufacturer.getAllTypes().then(res=>this.setState({manufactureries:res.data,showSpinner:false}));
        }

    }

    getInitData() {
        return {
            itemID: '',
            description: '',
            itemTypeID: '',
            manufacturerName: '',
            manufacturerID: '',
            warrantyID: '',
            curUnitCost: '',
            curUnitSale: '',
            partNo: '',
            partNoOld: '',
            serialNoFlag: 'N',
            discontinuedFlag: 'N',
            servercareFlag: 'N',
            renewalTypeID: '',
            allowDirectDebit: 'N',
            itemBillingCategoryID: '',
            contractResponseTime: '',
            excludeFromPOCompletion: 'N',
            allowSRLog: 0,
            isStreamOne: 0,
            notes: '',
            stockcat: '',
            allowGlobalPriceUpdate: 0

        };
    }

    handleScroll = (event) => {
        const {filter} = this.state;
        let scrollTop = window.scrollY;
        let docHeight = document.body.offsetHeight;
        let winHeight = window.innerHeight;
        let scrollPercent = scrollTop / (docHeight - winHeight);
        let scrollPercentRounded = Math.round(scrollPercent * 100);
        if (scrollPercentRounded > 70) {
            if (this.scrollTimer) clearTimeout(this.scrollTimer);
            this.scrollTimer = setTimeout(() => {
                filter.page++;
                this.setState({filter, reset: false}, () => this.getData(true));
            }, 500);
        }
        console.log(scrollPercentRounded);
    }
    getData = (noSpinner = false) => {
        const {filter, reset, items} = this.state;
        if (!noSpinner)
            this.setState({showSpinner: true});
        this.api.getItems(filter.limit, filter.page, filter.orderBy, filter.orderDir, filter.q)
            .then(res => {
                if (!reset)
                    this.setState({items: [...items, ...res.data], showSpinner: false});
                else
                    this.setState({items: res.data, showSpinner: false});

                console.log(reset, res);
            })
    }
    getDataTable = () => {
        const {items} = this.state;
        const columns = [
            {
                path: "description",
                label: "",
                hdToolTip: "Description",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-file-alt color-gray2 pointer",
                sortable: true,
                //className: "text-center",
            },
            {
                path: "curUnitCost",
                label: "",
                hdToolTip: "Cost Price",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-coin color-gray2 pointer",
                sortable: true,
                className: "text-center",
            },
            {
                path: "curUnitSale",
                label: "",
                hdToolTip: "Sale Price",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-coins color-gray2 pointer",
                sortable: true,
                className: "text-center",
            },
            {
                path: "salesStockQty",
                label: "",
                hdToolTip: "Sale Stock",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-boxes color-gray2 pointer",
                sortable: true,
                className: "text-center",
                content: this.getSalesStock
            },
            {
                path: "partNo",
                label: "",
                hdToolTip: "Part Price",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-barcode color-gray2 pointer",
                sortable: true,
                //className: "text-center",               
            },
            {
                path: "itemCategory",
                label: "",
                hdToolTip: "Item Category",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-ballot-check color-gray2 pointer",
                sortable: true,
                //className: "text-center",               
            },
            {
                path: "renewalTypeID",
                label: "",
                hdToolTip: "Renewal Type",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-tasks color-gray2 pointer",
                sortable: true,
                content: (item) => this.getRenewalTypeName(item.renewalTypeID)
                //className: "text-center",               
            },
            {
                path: "manufacturerName",
                label: "",
                hdToolTip: "Manufacturer",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-warehouse-alt color-gray2 pointer",
                sortable: true,
                //className: "text-center",               
            },
            {
                path: "",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                //icon: "fal fa-2x fa-signal color-gray2 pointer",
                sortable: true,
                content: (item) => this.getEditElement(item, this.handleEdit)
                //className: "text-center",   
            }
        ]
        //onOrderChange
        return <Table
            style={{marginTop: 20}}
            key="leadStatus"
            pk="itemID"
            columns={columns}
            data={items || []}
            search={true}
            onSearch={this.handleSearch}
            onSort={this.handleSort}
        >
        </Table>
    }
    getSalesStock = (item) => {
        //console.log(item);
        return <input type="number"
                      style={{width: 40}}
                      key={item.itemID + "salesStockQty"}
                      id={item.itemID}
                      value={item.salesStockQty || ''}
                      onChange={this.handleItemSalesStock}
        ></input>

    }
    handleItemSalesStock = (event) => {
        const itemID = event.target.id;
        const value = event.target.value;
        console.log(itemID, value);
        const {items} = this.state;
        const indx = items.findIndex(i => i.itemID == itemID);
        items[indx].salesStockQty = value;
        this.setState({items});
        this.api.updateItemQty(itemID, value).then(res => {
            console.log(res);
        })
    }
    getChildItemsData = (itemId) => {
        console.log('get childs');
        this.api.getChildItems(itemId).then(res => {
            const childItems = res.data.map(i => {
                return {id: i.childItemId, name: i.description, quantity: i.quantity};
            })
            this.setState({childItems})
        });
    }
    handleEdit = (item) => {
        console.log('items', item.itemID, item);
        this.getChildItemsData(item.itemID);
        this.setState({data: item, showModal: true, isNew: false});
        this.getLookups();

    }
    handleSearch = (value) => {
        const {filter} = this.state;
        filter.q = value;
        filter.page = 1;
        this.setState({filter, reset: true}, () => this.getData());
        console.log('search by ', value);
    }
    handleSort = (column) => {
        const {filter} = this.state;
        filter.orderDir = column.order;
        filter.orderBy = column.path;
        filter.page = 1;
        this.setState({filter, reset: true}, () => this.getData());
        console.log('sortby ', column);
    }

    getRenewalTypeName(id) {
        switch (id) {
            case 1:
                return "Broadband";
            case 2:
                return "Renewals";
            case 3:
                return "Quotation";
            case 4:
                return "Domain";
            case 5:
                return "Hosting";
            default:
                return "";
        }
    }

    handleNewItem = () => {
        this.setState({showModal: true, isNew: true, childItem: null, data: {...this.getInitData()}});
        this.getLookups();

    }
    getModalContent = () => {
        const {data, itemTypes, warranties, renewalTypes, itemBillingCategories, manufactureries} = this.state;
        return <div key="content">
            <table className="table">
                <tbody>
                <tr>
                    <td className="text-right">Description</td>
                    <td><input required
                               value={data.description}
                               onChange={(event) => this.setValue("description", event.target.value)}
                               className="form-control"
                    ></input></td>
                    <td className="text-right">Type</td>
                    <td><select className="form-control"
                                value={data.itemTypeID}
                                onChange={(event) => this.setValue("itemTypeID", event.target.value)}
                    >
                        <option></option>
                        {itemTypes.map(item => <option key={item.id}
                                                       value={item.id}
                        >{item.description}</option>)}
                    </select>
                    </td>
                </tr>
                <tr>
                    <td className="text-right">Manufacturer</td>
                    <td>
                        <select value={data.manufacturerID || ''}
                                onChange={(event) => this.setValue("manufacturerID", event.target.value)}
                                className="form-control"
                        >
                            <option></option>
                            {manufactureries.map(w => <option key={w.manufacturerID}
                                                              value={w.id}
                            >{w.name}</option>)}
                        </select>
                    </td>
                    <td className="text-right">Warranty</td>
                    <td><select value={data.warrantyID || ''}
                                onChange={(event) => this.setValue("warrantyID", event.target.value)}
                                className="form-control"
                    >
                        <option></option>
                        {warranties.map(w => <option key={w.id}
                                                     value={w.id}
                        >{w.name}</option>)}
                    </select>
                    </td>
                </tr>
                <tr>
                    <td className="text-right">Unit Cost</td>
                    <td>
                        <div style={{display: "flex"}}>
                            <input value={data.curUnitCost}
                                   onChange={(event) => this.setValue("curUnitCost", event.target.value)}
                                   className="form-control"
                            ></input>
                            {
                                data.allowGlobalPriceUpdate ?
                                    <button onClick={() => this.updateGlobalPrice('cost', data.curUnitCost, data.itemID)}> Globally
                                        Update Contract Pricing</button> : null}
                        </div>

                    </td>
                    <td className="text-right">Renewal Type</td>
                    <td><select value={data.renewalTypeID || ''}
                                onChange={(event) => this.setValue("renewalTypeID", event.target.value)}
                                className="form-control"
                    >
                        <option>Not a renewal</option>
                        {renewalTypes.map(w => <option key={w.id}
                                                       value={w.id}
                        >{w.name}</option>)}
                    </select></td>
                </tr>
                <tr>

                    <td className="text-right">Unit Sale</td>
                    <td>
                        <div style={{display: "flex"}}>
                            <input value={data.curUnitSale}
                                   onChange={(event) => this.setValue("curUnitSale", event.target.value)}
                                   className="form-control"
                            ></input>
                            {
                                data.allowGlobalPriceUpdate ?
                                    <button onClick={() => this.updateGlobalPrice('sale', data.curUnitSale, data.itemID)}> Globally
                                        Update Contract Pricing</button> : null
                            }
                        </div>

                    </td>
                    <td className="text-right">Item Billing Category</td>
                    <td><select value={data.itemBillingCategoryID || ''}
                                onChange={(event) => this.setValue("itemBillingCategoryID", event.target.value)}
                                className="form-control"
                    >
                        <option></option>
                        {itemBillingCategories.map(w => <option key={w.id}
                                                                value={w.id}
                        >{w.name}</option>)}
                    </select></td>
                </tr>
                <tr>
                    <td className="text-right">Part Number</td>
                    <td><input value={data.partNo}
                               onChange={(event) => this.setValue("partNo", event.target.value)}
                               className="form-control"
                    ></input></td>
                    <td className="text-right">Contract Response Time</td>
                    <td><input value={data.contractResponseTime}
                               onChange={(event) => this.setValue("contractResponseTime", event.target.value)}
                               className="form-control"
                    ></input></td>
                </tr>
                <tr>
                    <td className="text-right">Old Part Number</td>
                    <td><input value={data.partNoOld}
                               onChange={(event) => this.setValue("partNoOld", event.target.value)}
                               className="form-control"
                    ></input></td>
                    <td style={{verticalAlign: "top"}}
                        className="childs text-right "
                        colSpan={2}
                        rowSpan={6}
                    >{this.getChildItemsElement()}</td>

                </tr>
                <tr>
                    <td className="text-right">Notes</td>
                    <td className="text-left"
                        colSpan="1"
                    ><textarea value={data.notes}
                               onChange={(event) => this.setValue("notes", event.target.value)}
                               className="form-control"
                    ></textarea></td>
                </tr>
                <tr>
                    <td className="text-right">Discontinued</td>
                    <td className="text-left">
                        <table className="full-width table-check">
                            <tbody>
                            <tr>
                                <td align="left">{this.getCheckBox("discontinuedFlag")}</td>
                                <td align="right">
                                    <label>Server Care</label>
                                    {this.getCheckBox("servercareFlag")}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>

                </tr>
                <tr>
                    <td className="text-right">Allow Direct Debit</td>
                    <td className="text-left">
                        <table className="full-width table-check">
                            <tbody>
                            <tr>
                                <td align="left">{this.getCheckBox("allowDirectDebit")}</td>
                                <td align="right">
                                    <label>Exclude item from PO Status Report</label>
                                    {this.getCheckBox("excludeFromPOCompletion")}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>

                </tr>
                <tr>
                    <td className="text-right">Allow SRs to be logged against this contract</td>
                    <td className="text-left">
                        <table className="full-width table-check">
                            <tbody>
                            <tr>
                                <td align="left">{this.getCheckBox("allowSRLog", false)}</td>
                                <td align="right">
                                    <label>Item is linked to StreamOne</label>
                                    {this.getCheckBox("isStreamOne", false)}
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td className="text-right">Serial No Required</td>
                    <td className="text-left">{this.getCheckBox("serialNoFlag")}</td>
                </tr>

                </tbody>
            </table>
        </div>
    }
    updateGlobalPrice = async (type, value, itemId) => {
        const res = await this.confirm('This will update all the ' + type + ' prices for all active contracts. Are you sure?');
        if (!res) {
            return;
        }
        this.api.updateContractsPrice(type, value, itemId).then(res => {
            this.setState({reset: true}, () => this.getData());
        })
    }
    handleChildSelect = (childItem) => {
        console.log('child', childItem);
        this.setState({childItem});
    }
    getChildItemsElement = () => {
        const {childItem} = this.state
        return <div style={{color: "black", textAlign: "left", marginLeft: 10}}>
            <div style={{display: "flex", borderTop: "1px solid white", height: 5}}></div>
            <h3 className="childs-title">Child Items</h3>

            <div className="flex-row">
                <ItemSearchComponent width={260}
                                     onSelect={this.handleChildSelect}
                                     value={childItem?.name}
                ></ItemSearchComponent>
                <i className="fal fa-2x fa-plus color-white pointer icon ml-3"
                   onClick={this.handleAddChild}
                ></i>
            </div>
            {this.getChildItems()}
        </div>
    }
    handleAddChild = () => {
        const {childItem, childItems} = this.state;
        if (childItem) {
            //add child item
            childItem.quantity = 1;
            childItems.push(childItem);
            this.setState({childItem: null});
        } else {
            this.alert("Please select item");
        }
    }
    getChildItems = () => {
        const {childItems} = this.state;
        return <table className={this.getTableStyle()}>

            <tbody>
            {childItems.map(c =>
                <tr key={c.id}>
                    <td>{c.name}</td>
                    <td><input style={{width: 30}}
                               type="number"
                               value={c.quantity}
                               onChange={(event) => this.handleChildQuantity(c.id, event.target.value)}
                    ></input></td>
                    <td>{this.getDeleteElement(c, () => this.handleDeleteChild(c))}</td>
                </tr>
            )}
            </tbody>
        </table>
    }

    handleChildQuantity(id, q) {
        const {childItems} = this.state;
        const indx = childItems.findIndex(c => c.id == id);
        childItems[indx].quantity = q;
        this.setState({childItems});
    }

    handleDeleteChild = (c) => {
        console.log('delete', c);
        let {childItems} = this.state;
        childItems = childItems.filter(child => child.id != c.id);
        this.setState({childItems});
    }
    getCheckBox = (name, yesNo = true) => {
        const {data} = this.state;
        let trueValue = 'Y';
        let falseValue = 'N';
        if (!yesNo) {
            trueValue = 1;
            falseValue = 0;
        }
        return <input checked={data[name] == trueValue}
                      onChange={() => this.setValue(name, data[name] == trueValue ? falseValue : trueValue)}
                      type="checkbox"
        ></input>
    }
    getModal = () => {
        const {isNew, showModal} = this.state;
        if (!showModal)
            return null;
        return <Modal
            width={850}
            title={isNew ? "Create Item" : "Update Item"}
            show={showModal}
            content={this.getModalContent()}
            footer={<div key="footer">
                <button onClick={this.handleClose}
                        className="btn btn-secodary"
                >Cancel
                </button>
                <button onClick={this.handleSave}>Save</button>
            </div>}
            onClose={this.handleClose}
        >

        </Modal>
    }
    handleSave = () => {
        const {data, isNew, childItems} = this.state;
        //console.log(childItems);
        if (!data.description) {
            this.alert("Please enter description");
            return;
        }
        if (!data.manufacturerID) {
            this.alert("Please select manufacturer");
            return;
        }
        if (!data.itemTypeID) {
            this.alert("Please select type");
            return;
        }
        delete data.itemCategory;
        delete data.manufacturerName;
        if (!isNew) {
            this.api.updateChildItems(data.itemID, childItems).then((res) => {
                console.log(res);
            });

            this.api.updateItem(data).then((res) => {
                console.log(res);
                if (res.state) {
                    this.setState({showModal: false, reset: true}, () =>
                        this.getData()
                    );
                }
            });
        } else {
            data.itemID = null;
            this.api.addItem(data).then((res) => {
                console.log(res);
                if (res.state) {
                    if (childItems.length > 0 && res.data.itemId)
                        this.api
                            .updateChildItems(res.data.itemId, childItems)
                            .then((res) => {
                                console.log(res);
                            });
                    this.setState({showModal: false, reset: true}, () =>
                        this.getData()
                    );
                }
            });
        }
        console.log(data);
    }
    handleClose = () => {
        this.setState({showModal: false});
    }

    render() {
        return <div key="main">
            {this.getAlert()}
            <div style={{position: "fixed", zIndex: 102}}>
                {this.getConfirm()}
            </div>

            <Spinner show={this.state.showSpinner}></Spinner>
            <ToolTip title="New Item"
                     width={30}
            >
                <i className="fal fa-2x fa-plus color-gray1 pointer"
                   onClick={this.handleNewItem}
                ></i>
            </ToolTip>
            <div className="modal-style">
                {this.getModal()}
            </div>

            {this.getDataTable()}
        </div>;
    }
}

export default ItemsComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactItemsComponent");
    if (domContainer)
        ReactDOM.render(React.createElement(ItemsComponent), domContainer);
});
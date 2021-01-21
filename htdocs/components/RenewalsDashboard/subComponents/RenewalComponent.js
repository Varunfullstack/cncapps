import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Table from "../../shared/table/table";
import {exportCSV, sort} from "../../utils/utils";

export class RenewalComponent extends MainComponent {
    constructor(props) {
        super(props);
        this.state = {};
    }

    getDataTableElement = () => {
        let {data} = this.props;
        data = data.map(d => {
            if (d.orderId == null)
                d.orderId = 0;
            //d.nextPeriodStartDate=moment(d.nextPeriodStartDate,'DD/MM/YYYY').format("YYYY-MM-DD");
            //d.latestQuoteSent=moment(d.nextPeriodStartDate,'DD/MM/YYYY').format("YYYY-MM-DD HH:mm");
            if (d.sentQuotationColor == "white")
                d.sentQuotationColor = "";
            return d;
        });
        data = sort(data, 'orderId', 'desc');
        console.log('sorted', data);
        const columns = [
            {
                path: "customerName",
                label: "",
                hdToolTip: "Customer",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-building color-gray2 pointer",
                sortable: true,
                //className: "text-center",
                backgroundColorColumn: "sentQuotationColor",
            },
            {
                path: "itemDescription",
                label: "",
                hdToolTip: "Item",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-shopping-cart color-gray2 pointer",
                sortable: true,
                // className: "text-center",
                backgroundColorColumn: "sentQuotationColor",
            },
            {
                path: "orderId",
                label: "",
                hdToolTip: "Item",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",
                sortable: true,
                className: "text-center",
                backgroundColorColumn: "sentQuotationColor",
                content: (order) => {
                    if (order.orderId > 0)
                        return <a href={`SalesOrder.php?action=displaySalesOrder&ordheadID=${order.orderId}`}
                                  target="_blank"
                        >{order.orderId}</a>
                }
            },
            {
                path: "startDate",
                label: "",
                hdToolTip: "Invoice From",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hourglass-start color-gray2 pointer",
                sortable: true,
                className: "text-center",
                backgroundColorColumn: "sentQuotationColor",
            },
            {
                path: "nextPeriodStartDate",
                label: "",
                hdToolTip: "Expire Date",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
                sortable: true,
                className: "text-center",
                backgroundColorColumn: "sentQuotationColor",
            },
            {
                path: "comments",
                label: "",
                hdToolTip: "Comments",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-file color-gray2 pointer",
                sortable: true,
                //className: "text-center",
                backgroundColorColumn: "sentQuotationColor",
                content: order => {
                    return order.comments ? order.comments.substr(0, 200) : ""
                }
            },
            {
                path: "latestQuoteSent",
                label: "",
                hdToolTip: "Latest Quote Sent",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hourglass-start color-gray2 pointer",
                sortable: true,
                className: "text-center",
                backgroundColorColumn: "sentQuotationColor",
            },
            {
                path: "customerItemID",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-edit color-gray2 pointer",
                sortable: false,
                className: "text-center",
                backgroundColorColumn: "sentQuotationColor",
                content: (order) => {
                    return <a href={`RenQuotation.php?action=edit&ID=${order.customerItemID}`}
                              target="_blank"
                    >
                        <i className="fal fa fa-edit color-gray2 pointer"></i>
                    </a>
                }
            },
        ]
        console.log('data length', data.length);
        return <Table id='renewals'
                      data={data || []}
                      columns={columns}
                      pk={'customerItemID'}
                      search={true}
        >
        </Table>
    }
    handleExport = () => {
        const {data} = this.props;
        const exportData = data.map(d => {
            return {
                'Customer': d.customerName,
                'Item': d.itemDescription,
                'Sales Order': d.orderId == 0 ? "" : d.orderId,
                'Invoice From': d.startDate,
                'Expire Date': d.nextPeriodStartDate,
                'Comments': d.comments,
                'Latest Quote Sent': d.latestQuoteSent
            }
        })
        exportCSV(exportData, 'Renewals.csv');
    }

    render() {
        return <div>
            <button onClick={this.handleExport}>CSV</button>
            {this.getDataTableElement()}
        </div>
    }
}
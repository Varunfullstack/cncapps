import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Table from "../../shared/table/table";
import {dateFormatExcludeNull, exportCSV, poundFormat} from "../../utils/utils";

export class RenHostingComponent extends MainComponent {
    constructor(props) {
        super(props);
        this.state = {};
    }

    getDataTableElement = () => {
        let {data} = this.props;
        const columns = [
            {
                path: "customerName",
                label: "",
                hdToolTip: "Customer",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-building color-gray2 pointer",
                sortable: true,
            },
            {
                path: "itemDescription",
                label: "",
                hdToolTip: "Type",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-shopping-cart color-gray2 pointer",
                sortable: true,
            },
            {
                path: "notes",
                label: "",
                hdToolTip: "Notes",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-file color-gray2 pointer",
                sortable: true,
                content: order => {
                    return order.notes ? order.notes.substr(0, 200) : ""
                }
            },
            {
                path: "invoiceFromDate",
                label: "",
                hdToolTip: "Invoice From",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hourglass-start color-gray2 pointer",
                sortable: true,
                className: "text-center",
                content: order => dateFormatExcludeNull(order.invoiceFromDate),
            },
            {
                path: "invoiceToDate",
                label: "",
                hdToolTip: "Invoice To",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
                sortable: true,
                className: "text-center",
                content: order => dateFormatExcludeNull(order.invoiceToDate),
            },
            {
                path: "costAnnum",
                label: "",
                hdToolTip: "Cost Price/Annum",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-coin color-gray2 pointer",
                sortable: true,
                className: "text-right",
                content: order => poundFormat(order.costAnnum)
            },
            {
                path: "saleAnnum",
                label: "",
                hdToolTip: "Sale Price/Annum",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-coins color-gray2 pointer",
                sortable: true,
                className: "text-right",
                content: order => poundFormat(order.saleAnnum)
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
                    return <a href={`RenHosting.php?action=edit&ID=${order.customerItemID}`}
                              target="_blank"
                    >
                        <i className="fal fa fa-edit color-gray2 pointer"></i>
                    </a>
                }
            },
        ]
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
                'Type': d.itemDescription,
                'Notes': d.notes,
                'Invoice From': d.invoiceFromDate,
                'To': d.invoiceToDate,
                "Cost Price/Annum": d.costAnnum,
                "Sale Price/Annum": d.saleAnnum,
            }
        })
        exportCSV(exportData, 'Hosting Renewals.csv');
    }

    render() {
        return <div>
            <button onClick={this.handleExport}>CSV</button>
            {this.getDataTableElement()}
        </div>
    }
}
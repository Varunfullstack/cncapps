import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Table from "../../shared/table/table";
import { exportCSV } from "../../utils/utils";

export class RenBroadbandComponent extends MainComponent
{
    constructor(props) {
        super(props);
        this.state = {
        };
    }
    getDataTableElement=()=>{        
        let {data}=this.props;      
        const columns=[
            {
               path: "customerName",
               label: "",
               hdToolTip: "Customer",
               hdClassName: "text-center",
               icon: "fal fa-2x fa-building color-gray2 pointer",
               sortable: true,               
            },
            {
                path: "ispID",
                label: "",
                hdToolTip: "ISP",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hashtag color-gray2 pointer",
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
                path: "adslPhone",
                label: "",
                hdToolTip: "ADSL Phone",
                hdClassName: "text-left",
                icon: "fal fa-2x fa-phone color-gray2 pointer ",
                sortable: true,
             },
             {
                path: "costPricePerMonth",
                label: "",
                hdToolTip: "Cost/Month",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-donate color-gray2 pointer",
                sortable: true,                
             },
             {
                path: "salePricePerMonth",
                label: "",
                hdToolTip: "Sale/Month",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-dollar-sign color-gray2 pointer",
                sortable: true,             
             },
             {
                path: "invoiceFromDate",
                label: "",
                hdToolTip: "Invoice From",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hourglass-start color-gray2 pointer",
                sortable: true,
                className: "text-center",                
             },
             {
                path: "invoiceToDate",
                label: "",
                hdToolTip: "Invoice To",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-hourglass-end color-gray2 pointer",
                sortable: true,
                className: "text-center",              
             },
             {
                path: "customerItemID",
                label: "",
                hdToolTip: "Edit",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-edit color-gray2 pointer",
                sortable: true,
                className: "text-center",
                backgroundColorColumn:"sentQuotationColor",
                content:(order)=>{                     
                    return <a href={`RenBroadband.php?action=edit&ID=${order.customerItemID}`} target="_blank">
                        <i className="fal fa fa-edit color-gray2 pointer"></i>
                    </a>                    
                }
             },
        ]
        console.log('data length',data.length);
        return <Table id='renewals' data={data||[]} columns={columns} pk={'customerItemID'} search={true}>
        </Table>
    }
    handleExport=()=>{
        const {data}=this.props;
        const exportData=data.map(d=>{
            return {
                'Customer':d.customerName,
                'ISP':d.ispID,      
                'Type':d.itemDescription,
                'Phone':d.adslPhone,
                'Cost/Month':d.costPricePerMonth,
                'Sale/Month':d.salePricePerMonth,
                'Invoice From':d.invoiceFromDate,
                'To':d.invoiceToDate,
            }
        })
        exportCSV(exportData,'Internet Services.csv');
    }
    render()
    {
        return <div>
            <button onClick={this.handleExport}>CSV</button>
            {this.getDataTableElement()}
        </div>
    }
}
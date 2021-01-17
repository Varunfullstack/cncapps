import MainComponent from "../../shared/MainComponent";
import React from 'react';
import Table from "../../shared/table/table";
import { exportCSV, sort } from "../../utils/utils";

export class RenContractComponent extends MainComponent
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
                className: "text-center",                
                 
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
                path: "quantity",
                label: "",
                hdToolTip: "Quantity",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-list color-gray2 pointer",
                sortable: true,               
             },
             {
                path: "costAnnum",
                label: "",
                hdToolTip: "Cost Price/Annum",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-donate color-gray2 pointer",
                sortable: true,
                className: "text-center",             
             },
             {
                path: "saleAnnum",
                label: "",
                hdToolTip: "Sale Price/Annum",
                hdClassName: "text-center",
                icon: "fal fa-2x fa-dollar-sign color-gray2 pointer",
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
                    return <a href={`RenContract.php?action=edit&ID=${order.customerItemID}`} target="_blank">
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
                'Type':d.itemDescription,                
                'Invoice From':d.invoiceFromDate,
                'To':d.invoiceToDate,
                'Quantity':d.quantity,
                'Cost Price/Annum':d.costAnnum,
                'Sale Price/Annum':d.saleAnnum,
            }
        })
        exportCSV(exportData,'Contract Renewals.csv');
    }
    render()
    {
        return <div>
            <button onClick={this.handleExport}>CSV</button>
            {this.getDataTableElement()}
        </div>
    }
}
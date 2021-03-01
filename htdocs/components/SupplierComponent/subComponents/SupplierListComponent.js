import React from "react";
import Table from "../../shared/table/table";
import {SupplierService} from "../../services/SupplierService";


export class SupplierListComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
        this.editSupplier = this.props.onSupplierEdit;
        this.state = {
            data: []
        }
        this.getTableElement = this.getTableElement.bind(this);
    }

    componentDidMount() {
        SupplierService.getSuppliersSummaryData().then(data => {
            this.setState({data});
        })
    }

    getTableElement() {
        let columns = [
            {
                hide: false,
                order: 1,
                path: "name",
                key: "name",
                label: "Supplier Name",
                hdToolTip: "Supplier Name",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
            },
            {
                hide: false,
                order: 2,
                path: "address1",
                key: "address1",
                label: "Supplier Address",
                hdToolTip: "Supplier Address",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
                content: (supplierRow) => {
                    return `${supplierRow.address1}${supplierRow.address2 ? `, ${supplierRow.address2}` : ''}, ${supplierRow.town}, ${supplierRow.county}, ${supplierRow.postcode}`
                }

            },
            {
                hide: false,
                order: 2,
                path: "mainContactName",
                key: "mainContactName",
                label: "Supplier Contact Name",
                hdToolTip: "Supplier Contact Name",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
                content: (supplierRow) => {
                    return `${supplierRow.mainContactTitle ? `${supplierRow.mainContactTitle}. ` : ''}${supplierRow.mainContactName}${supplierRow.mainContactPosition ? ` (${supplierRow.mainContactPosition})` : ''}`
                }

            },
            {
                hide: false,
                order: 20,
                path: "id",
                key: "address2",
                label: "Supplier Address",
                hdToolTip: "Supplier Address",
                sortable: true,
                width: "55",
                hdClassName: "text-center",
                className: "text-center",
                content: (supplierRow) => (
                    <button onClick={this.editSupplier(supplierRow)}>Test</button>
                )


            },

        ];
        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));
        const {data} = this.state;
        console.log(data);

        return <Table
            data={data || []}
            columns={columns}
            pk="id"
            search={true}
        />
    }

    render() {
        return (
            <React.Fragment>
                {this.getTableElement()}
            </React.Fragment>
        )
    }
}
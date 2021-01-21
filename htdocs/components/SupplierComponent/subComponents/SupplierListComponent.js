import React from "react";
import Table from "../../shared/table/table";

export class SupplierListComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
        this.state = {
            data: [
                {id: 1, name: "test"}
            ]
        }
        this.getTableElement = this.getTableElement.bind(this);
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
                hdClassName: "text-center",
                className: "text-center",
            },
            {
                hide: false,
                order: 2,
                path: "name",
                key: "test",
                label: "Supplier Name",
                hdToolTip: "Supplier Name",
                sortable: true,
                width: "55",
                hdClassName: "text-center",
                className: "text-center",
                content: (supplierRow) => (
                    <button>Test</button>
                )


            },

        ];
        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));
        const {data} = this.state;

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
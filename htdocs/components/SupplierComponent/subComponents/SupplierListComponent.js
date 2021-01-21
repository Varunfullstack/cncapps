import React from "react";
import Table from "../../shared/table/table";

export class SupplierListComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
        this.getTableElement = this.getTableElement.bind(this);
    }

    addToolTip = (element, title) => {
        return this.el(
            "div",
            {className: "tooltip"},
            element,
            this.el("div", {className: "tooltiptext tooltip-bottom"}, title)
        );
    };

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
            }

        ];
        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));
        const {data} = this.props;

        return <Table
            data={data || []}
            columns={columns}
            pk="problemID"
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
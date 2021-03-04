import React from "react";
import Table from "../../shared/table/table";
import {SupplierService} from "../../services/SupplierService";
import {SHOW_ACTIVE} from "../../customerEditMain/visibilityFilterTypes";
import {VisibilityFilterOptions} from "../../customerEditMain/actions";


export class SupplierListComponent extends React.PureComponent {

    constructor(props, context) {
        super(props, context);
        this.editSupplier = this.props.onSupplierEdit;
        this.state = {
            data: [],
            visibilityFilter: VisibilityFilterOptions.SHOW_ACTIVE
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
                    return `${supplierRow.mainContactTitle ? `${supplierRow.mainContactTitle}. ` : ''}${supplierRow.mainContactName ?? ""}${supplierRow.mainContactPosition ? ` (${supplierRow.mainContactPosition})` : ''}`
                }

            },
            {
                hide: false,
                order: 3,
                path: "mainContactPhone",
                key: "mainContactPhone",
                label: "Contact Phone",
                hdToolTip: "Contact Phone",
                sortable: true,
                width: "55",
                hdClassName: "text-left",
                className: "text-left",
                content: (supplierRow) => {
                    return supplierRow.mainContactPhone
                }

            },
            {
                hide: false,
                order: 20,
                path: "id",
                key: "address2",
                sortable: false,
                width: "55",
                hdClassName: "text-center",
                className: "text-center",
                content: (supplierRow) => (
                    <button onClick={this.editSupplierRowFunction(supplierRow)}><i className="fal fa-pencil"/></button>
                )
            },

        ];
        columns = columns
            .filter((c) => c.hide == false)
            .sort((a, b) => (a.order > b.order ? 1 : -1));
        const {data, visibilityFilter} = this.state;

        return <Table
            data={data.filter(x => !(visibilityFilter === VisibilityFilterOptions.SHOW_ACTIVE && !x.active))}
            columns={columns}
            pk="id"
            search={true}
        />
    }

    editSupplierRowFunction = (supplierRow) => {
        return () => {
            // navigate to the edit page
            console.log(supplierRow);
        }
    }

    onToggleVisibility = () => {
        let visibilityFilterOption = VisibilityFilterOptions.SHOW_ALL;
        if (this.state.visibilityFilter === VisibilityFilterOptions.SHOW_ALL) {
            visibilityFilterOption = VisibilityFilterOptions.SHOW_ACTIVE;
        }
        this.setState({visibilityFilter: visibilityFilterOption});
    }

    render() {
        const {visibilityFilter} = this.state;

        return (
            <React.Fragment>
                <div>
                    <div className="toggle-inline">
                        <label>Show Inactive</label>
                        <label className="switch"
                        >

                            <input type="checkbox"
                                   name="showOnlyActiveSites"
                                   onChange={this.onToggleVisibility}
                                   title="Show only active sites"
                                   value="1"
                                   checked={visibilityFilter === SHOW_ACTIVE}
                                   className="form-control"
                            />
                            <span className="slider round"/>
                        </label>
                    </div>
                </div>
                {this.getTableElement()}
            </React.Fragment>
        )
    }
}
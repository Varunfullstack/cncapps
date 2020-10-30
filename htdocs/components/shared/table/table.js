import React from 'react';
import TableHeader from "./tableHeader";
import TableBody from "./tableBody";

class Table extends React.Component {
    constructor(props) {
        super(props);
        this.state = {
            sortColumn: {
                path: this.props.defaultSortPath,
                order: this.props.defaultSortOrder ? this.props.defaultSortOrder : 'asc',
                searchFilter: ""
            }
        }

    }

    handleSort = (sortColumn) => {

        for (let i = 0; i < this.props.columns.length; i++) {
            if (this.props.columns[i].path === sortColumn.path) {
                //check if column is sortable
                const sortable = this.props.columns[i].sortable ? this.props.columns[i].sortable : false;
                if (sortable)
                    this.setState({sortColumn});
            }
        }

    }
    get = (o, p) => p.split('.').reduce((a, v) => a[v], o);
    sort = (array, path, order = 'asc') => {
        return array.sort((a, b) => {
            if (this.get(a, path) > this.get(b, path) || this.get(a, path) == null || this.get(a, path) == undefined)
                return order == 'asc' ? 1 : -1;
            if (this.get(a, path) < this.get(b, path) || this.get(b, path) == null || this.get(a, path) == undefined)
                return order == 'asc' ? -1 : 1;
            else return 0;
        })
    }
    handleSearch = (event) => {
        console.log(event.target.value);
        this.setState({searchFilter: event.target.value});
    }

    filterData(data, columns) {
        const {searchFilter} = this.state;
        let filterdData = [];
        if (searchFilter && searchFilter.length > 0) {
            for (let i = 0; i < data.length; i++) {
                for (let j = 0; j < columns.length; j++) {
                    if (columns[j].path != null && columns[j].path != "") {
                        if (data[i][columns[j].path] && data[i][columns[j].path].toLowerCase().indexOf(searchFilter.toLowerCase()) >= 0) {
                            filterdData.push(data[i]);
                            break;
                        }
                    }
                }
            }
            return filterdData;
        } else return [...data];
    }

    render() {
        const props = this.props;
        const {data, columns, pk, selected, selectedKey, search, searchLabelStyle} = props;
        const {sortColumn} = this.state;
        const {handleSearch} = this;
        const el = React.createElement;
        const filterData = search ? this.filterData(data, columns) : data;
        if (this.state.sortColumn.path != null && data.length > 0) {
            this.sort(filterData, this.state.sortColumn.path, this.state.sortColumn.order);
        }
        return [
            search ? el('div', {key: "tableSearch"}, [
                el('label', {key: "lbLabel", style: searchLabelStyle || null}, "Search"),
                el('input', {key: "inpSearch", onChange: handleSearch})
            ]) : null,
            el("table", {key: "table", className: "table table-striped"}, [
                el(TableHeader, {
                    key: "tableHeader",
                    columns: columns,
                    sortColumn: sortColumn,
                    onSort: this.handleSort,
                }),
                filterData.length > 0 ? el(TableBody, {
                    key: "tableBody",
                    data: filterData,
                    columns,
                    pk,
                    selected,
                    selectedKey
                }) : null,
            ])];
    }
}

export default Table;
1;

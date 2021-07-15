import React from 'react';
import './table.css';

import TableHeader from "./tableHeader.js";
import TableBody from "./tableBody.js";
import TableFooter from "./tableFooter.js";
import PropTypes from "prop-types";
import {isNumeric} from "../../utils/utils"

/**
 * -- main properties
 * key: "documents",
 * data: data?.documents || [],
 * columns: columns,
 * pk: "id",
 * search: false,
 * hasFooter:false
 * -- columns properties
 * classNameColumn
 * className
 * backgroundColorColumn
 * path:''
 * label:''
 * sortable:false
 * footerContent :(c)=>
 * footerColSpan :1
 * toolTip
 * textColorColumn -> td text color
 * allowRowOrder Boolean allo rows drag and drops using jqueryUI
 * onOrderChange Event fire on row order changed and return current and next element
 * searchControls add other search control after search element
 * cellType
 */
export const CellType = {Text: "Text", Number: "Number", Money: "Money", Default: "Default"};

class Table extends React.Component {
    delayTimer;

    constructor(props) {
        super(props);
        const sortColumn = {...this.props.columns.find(x => x.path === this.props.defaultSortPath)};
        sortColumn.order = this.props.defaultSortOrder ? this.props.defaultSortOrder : 'asc';

        this.state = {
            searchFilter: "",
            sortColumn,
        }
    }

    componentDidMount() {
    }

    componentDidUpdate(prevProps, prevState) {
        if (this.props.allowRowOrder) {
            setTimeout(() => {
                $("#table" + this.props.id + " tbody").sortable({
                    cursor: "move",
                    helper: this.fixHelperModified,
                    stop: this.updateIndex
                }).disableSelection()
            }, 1000);
        }
    }

    fixHelperModified = (e, tr) => {
        var $originals = tr.children();
        var $helper = tr.clone();
        $helper.children().each(function (index) {
            $(this).width($originals.eq(index).width());
            $(this).css('background-color', '#00b9f1');
        });
        return $helper;
    }
    /**
     *
     * @param {place element} e
     * @param {drag element} ui
     */
    updateIndex = (e, ui) => {
        const currentItemId = $(ui.item[0]).attr('id');
        const nextItemId = $(ui.item[0]).next().attr('id');
        const currentItem = this.props.data.filter(i => i[this.props.pk] == currentItemId)[0];
        const nextItem = this.props.data.filter(i => i[this.props.pk] == nextItemId)[0];
        if (this.props.onOrderChange)
            this.props.onOrderChange(currentItem, nextItem);
    };

    disableSortable() {
        if (this.props.allowRowOrder) {
            return $("#table" + this.props.id + " tbody").sortable('option', "disabled", true);
        }
    }

    enableSortable() {
        if (this.props.allowRowOrder) {
            return $("#table" + this.props.id + " tbody").sortable('option', "disabled", false);
        }
    }

    handleSort = (path) => {

        let {sortColumn} = this.state;
        const {columns} = this.props;

        if (sortColumn.path !== path) {
            sortColumn = {...columns.find(x => x.path == path)};
            sortColumn.order = 'asc';
        } else {
            sortColumn.order = sortColumn.order == "asc" ? "desc" : "asc";
        }

        if (sortColumn.path !== this.props.defaultSortPath || sortColumn.order !== this.props.defaultSortOrder) {
            this.disableSortable();
        } else {
            this.enableSortable();
        }
        if (this.props.onSort) {
            this.props.onSort(sortColumn);
        }
        this.setState({sortColumn});
    };
    get = (o, p) => p.split(".").reduce((a, v) => a[v], o) || '';
    sort = (array, path, order = "asc") => {
        return array.sort((a, b) => {
            if (
                this.get(a, path) > this.get(b, path) ||
                this.get(a, path) == null ||
                this.get(a, path) == undefined
            )
                return order == "asc" ? 1 : -1;
            if (
                this.get(a, path) < this.get(b, path) ||
                this.get(b, path) == null ||
                this.get(a, path) == undefined
            )
                return order == "asc" ? -1 : 1;
            else return 0;
        });
    };
    handleSearch = (event) => {

        if (event.target.value) {
            this.disableSortable();
        } else {
            this.enableSortable();
        }
        clearTimeout(this.delayTimer);
        event.persist();
        this.delayTimer = setTimeout(() => {
            if (this.props.onSearch)            // custome search
                this.props.onSearch(event.target.value);
            else
                this.setState({searchFilter: event.target.value});
        }, 1000); // Will do the ajax stuff after 1000 ms, or 1 s
    };

    filterData(data, columns) {
        const {searchFilter} = this.state;
        let filterdData = [];
        if (searchFilter && searchFilter.length > 0) {
            for (let i = 0; i < data.length; i++) {
                for (let j = 0; j < columns.length; j++) {
                    if (columns[j].path != null && columns[j].path !== "") {
                        if (
                            data[i][columns[j].path] &&
                            data[i][columns[j].path]
                                .toString()
                                .toLowerCase()
                                .indexOf(searchFilter.toLowerCase()) >= 0
                        ) {
                            filterdData.push(data[i]);
                            break;
                        }
                    }
                }
            }
            return filterdData;
        } else return [...data];
    }

    correctCellType(columns, data) {
        if (columns && data) {
            for (let i = 0; i < columns.length; i++) {
                if (!columns[i].cellType && !columns[i].content) {
                    let isText = false;
                    for (let j = 0; j < data.length; j++) {
                        if (data[j][columns[i].path] && !isNumeric(data[j][columns[i].path])) {
                            isText = true;
                            break;
                        }
                    }
                    if (isText)
                        columns[i].cellType = CellType.Text;
                    else
                        columns[i].cellType = CellType.Number;
                } else if (!columns[i].cellType && columns[i].content)
                    columns[i].cellType = CellType.Text;
            }

        }
        return columns;
    }

    render() {
        const props = this.props;
        const {
            data,
            pk,
            selected,
            selectedKey,
            search,
            searchLabelStyle,
            hasFooter,
            style
        } = props;
        let {columns} = props;
        const {sortColumn} = this.state;
        const {handleSearch} = this;
        const el = React.createElement;
        const filterData = search ? this.filterData(data, columns) : data;
        columns = this.correctCellType(columns, filterData);
        let striped = "table-striped";
        if (this.props.striped === false)
            striped = "";
        if (this.state.sortColumn.path != null && data.length > 0 && !this.props.onSort) {
            if (this.state.sortColumn.sortFn) {
                filterData.sort(this.state.sortColumn.sortFn(this.state.sortColumn.order));
            } else {
                this.sort(filterData, this.state.sortColumn.path, this.state.sortColumn.order);
            }
        }
        return el("div", {style: style},
            el("div", {className: "flex-row", key: "tableSearch"},
                search
                    ? el("div", {key: "tableSearch", style: {marginBottom: 5}, className: "flex-row"}, [
                        el(
                            "label",
                            {key: "lbLabel", style: searchLabelStyle || null},
                            "Search"
                        ),
                        el("input", {
                            key: "inpSearch",
                            onChange: handleSearch,
                            className: "form-control",
                            style: {width: 250},
                            type: 'search'
                        })
                    ])
                    : null,
                this.props.searchControls || null
            )
            ,
            el("table", {
                key: "table" + this.props.id,
                id: "table" + this.props.id,
                className: "table  " + striped + " " + (this.props.hover === false ? "" : "table-hover"),

            }, [
                el(TableHeader, {
                    key: "tableHeader",
                    id: "tableHeader",
                    columns: columns,
                    sortColumn: sortColumn,
                    onSort: this.handleSort,
                }),
                filterData.length > 0 ? el(TableBody, {
                        key: "TableBody",
                        id: "tableBody",
                        data: filterData,
                        columns,
                        pk,
                        selected,
                        selectedKey,
                    })
                    : null,
                hasFooter ? el(TableFooter, {key: "tableFooter", id: "tableFooter", columns}) : null
            ]),
        );
    }
}

Table.propTypes = {
    defaultSortPath: PropTypes.string,
    defaultSortOrder: PropTypes.string,
}
export default Table;


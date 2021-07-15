import React from "react";
import "../ToolTip.css";
import {CellType} from "./table";
import ToolTip from "../ToolTip";

class TableHeader extends React.Component {
    el = React.createElement;
    raiseSort = (path) => {
        this.props.onSort(path);
    };
    renderSortIcon = (column) => {
        let key = "fa-sort";
        let className = "fa fa-sort";
        let style = {color: "gray"};

        if (this.props.sortColumn != null) {
            if (
                column.path !== this.props.sortColumn.path &&
                (column.sortable == undefined || column.sortable == false)
            )
                return null;
            if (column.path == this.props.sortColumn.path) {
                style = null;
                if (this.props.sortColumn.order == "asc") {
                    key = "fa-sort-up";
                    className = "fa fa-sort-up";
                } else {
                    key = "fa-sort-desc";
                    className = "fa fa-sort-down";
                }
            }
        }
        if (column.sortable == true) return this.el("i", {key, className, style});
        return null;
    };

    getCellAlign(c) {
        if (c && c.cellType) {
            switch (c.cellType) {
                case CellType.Text:
                    return "flex-start";
                case CellType.Number:
                    return "flex-end";
                case CellType.Money:
                    return "flex-end";
                case CellType.Default:
                    return "center";
            }
        }
        return "center";
    }

    render() {
        const {columns} = this.props;
        const {el, raiseSort, renderSortIcon} = this;
        return el(
            "thead",
            null,
            el(
                "tr",
                null,
                columns.map((c) => (
                    <th
                        className={(c?.hdClassName || "") + " clickable "}
                        key={c.key || c.path || c.label.replace(" ", "")}
                        onClick={() => raiseSort(c.path)}
                        width={c.width ? c.width : ""}
                    >
                        <div
                            key={this.getCellAlign(c)}
                            style={{
                                display: "flex",
                                justifyContent: this.getCellAlign(c),
                            }}
                        >
                            <div
                                style={{
                                    ...c.hdStyle,
                                    whiteSpace: "nowrap",
                                    display: "flex",
                                    alignItems: "center",
                                    justifyContent: "center",
                                }}
                            >
                                <ToolTip title={c.hdToolTip ? c.hdToolTip : ""}>
                                    <span>{c?.label || " "}</span>
                                    {c.icon ? (
                                        <i className={c.icon} onClick={this.handleExportCsv}></i>
                                    ) : null}
                                </ToolTip>
                                <div style={{marginLeft: 3}}>{renderSortIcon(c)}</div>
                            </div>
                        </div>
                    </th>
                ))
            )
        );
    }
}

export default TableHeader;

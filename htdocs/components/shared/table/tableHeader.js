import React from 'react';
import '../ToolTip.css'

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

    render() {
        const {columns} = this.props;
        const {el, raiseSort, renderSortIcon} = this;
        return el(
            "thead",
            null,
            el(
                "tr",
                null,
                columns.map((c) =>
                    el(
                        "th",
                        {
                            className: (c?.hdClassName || " ") + " clickable ",
                            key: c.key || c.path || c.label.replace(" ", ""),
                            onClick: () => raiseSort(c.path),
                            width: c.width ? c.width : "",
                            //title:c.toolTip?c.toolTip:""
                        },
                        el('div', {className: "tooltip",style:c.hdStyle},
                            c?.label || " ",
                            c.icon ? el('i', {className: c.icon}) : null,
                            renderSortIcon(c),
                            c.hdToolTip ? el('div', {className: "tooltiptext tooltip-bottom"},
                                c.hdToolTip ? c.hdToolTip : ""
                            ) : null
                        )
                    )
                )
            )
        );
    }
}

export default TableHeader;

class TableHeader extends React.Component {
    el = React.createElement;
    raiseSort = (path) => {
        const sortColumn = {...this.props.sortColumn};
        if (this.props.sortColumn != null && sortColumn.path === path)
            sortColumn.order = sortColumn.order === "asc" ? "desc" : "asc";
        else {
            sortColumn.path = path;
            sortColumn.order = "asc";
        }
        this.props.onSort(sortColumn);
    };
    renderSortIcon = (column) => {
        let key = "fa-sort";
        let className = "fa fa-sort";
        let style = {color: 'gray'};

        if (this.props.sortColumn != null) {
            if (
                column.path !== this.props.sortColumn.path &&
                (column.sortable === undefined || column.sortable === false)
            )
                return null;
            if (column.path === this.props.sortColumn.path) {
                style = null;
                if (this.props.sortColumn.order === "asc") {
                    key = "fa-sort-asc";
                    className = "fa fa-sort-asc";
                } else {
                    key = "fa-sort-desc";
                    className = "fa fa-sort-desc";
                }
            }
        }
        if (column.sortable === true) return this.el("i", {key, className, style});
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
                            className: "clickable",
                            key: c.path || c.key,
                            onClick: () => raiseSort(c.path),
                        },
                        c.label,
                        " ",
                        renderSortIcon(c)
                    )
                )
            )
        );
    }
}

export default TableHeader;

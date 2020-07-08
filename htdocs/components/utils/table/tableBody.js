// items
class TableBody extends React.Component {
    el = React.createElement;
    get = (o, p) => p.split('.').reduce((a, v) => a[v], o);

    render() {
        const {data, columns, pk} = this.props;
        const {el} = this;
        return el("tbody", {key: 'tbody'},
            data.map(item => el("tr", {
                key: pk ? item[pk] : item[0]
            }, columns.map(c => el("td", {
                key: c.path || c.key
            }, c.content ? c.content(item) : this.get(item, c.path)))))
        );
    }
}

// items
export default TableBody;

import React from 'react';

class TableFooter extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {}
    }

    render() {
        const {el} = this;
        const {columns} = this.props;
        return el('tfoot', {key: 'tfoot', className: 'footer'},
            el('tr', {key: 'tfootTr'},
                columns.map(c => el('td', {
                    key: c.key || c.path || c.label.replace(' ', ''),
                    colSpan: c?.footerColSpan || 1,
                    className:c.footerClass
                }, c.footerContent ? c.footerContent(c) : null))));

    }
}

export default TableFooter;
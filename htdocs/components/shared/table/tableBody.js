import React from 'react';
import { CellType } from './table';

// items
class TableBody extends React.Component {
    el = React.createElement;
    index = 0;
    get = (o, p) => p != null ? p.split('.').reduce((a, v) => a !== undefined && a[v], o) : '';

    makeid(length = 5) {
        var result = "";
        var characters =
            "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        var charactersLength = characters.length;
        for (var i = 0; i < length; i++) {
            result += characters.charAt(Math.floor(Math.random() * charactersLength));
        }
        return result;
    }

    addToolTip = (element, title,cell) => {
        return <div style={{display:"flex",justifyContent:this.getCellAlign(cell)}}>          
         {title ? this.el('div', {className: 'tooltip'}, element, this.el('div', {className: "tooltiptext tooltip-bottom"}, title)) : element}
         </div>
    }
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
        const {data, columns, pk, selected, selectedKey} = this.props;
        const {el, makeid} = this;
        this.index = 0;
        return el("tbody", {key: 'tbody'},
            data.map((item, index) => el("tr", {
                key: (pk ? item[pk] : item[0]).toString() + makeid().toString(),
                id: (pk ? item[pk] : null).toString(),
                className: selected && pk && selected[pk] == item[pk] && selected[selectedKey] == item[selectedKey] ? 'selected' : null
            }, columns.map(c => el("td", {
                    key: c.path || c.key || c.label.replace(' ', '') + makeid().toString(),
                    className: (c.className ? c.className : ' ') + " " + (c.classNameColumn ? this.get(item, c.classNameColumn) : '')
                    , style: {
                        backgroundColor: c.backgroundColorColumn ? this.get(item, c.backgroundColorColumn) : '',
                        color: c.textColorColumn ? this.get(item, c.textColorColumn) : ''
                    }

                }, this.addToolTip(
                c.content ? c.content(item) :
                    <div dangerouslySetInnerHTML={{__html: this.get(item, c.path)}}/>, c?.toolTip || null,c)
            ))))
        );
    }
}

// items
export default TableBody;

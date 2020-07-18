 
 // items
class TableBody extends React.Component {
  el=React.createElement;
  get=(o, p) => p!=null?p.split('.').reduce((a, v) =>a!=undefined &&  a[v], o):'';
  render() {
    const { data, columns,pk,selected,selectedKey } = this.props;
    const {el}=this;
    //if(selected)
    //console.warn(selected[pk],selected[selectedKey]);
    return el("tbody", {key:'tbody'}, 
    data.map((item,index) => el("tr", {
      key: (pk?item[pk]:item[0])+index,
      className:selected&&pk&&selected[pk]==item[pk]&&selected[selectedKey]===item[selectedKey]?'selected':null
    }, columns.map(c => el("td", {
      key: c.path || c.key||c.label.replace(' ','')
    }, c.content ? c.content(item) : this.get(item, c.path)))))
    );     
  }
}

// items
export default TableBody;

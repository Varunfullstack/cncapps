 
 // items
class TableBody extends React.Component {
  el=React.createElement;
  index=0;
  get=(o, p) => p!=null?p.split('.').reduce((a, v) =>a!=undefined &&  a[v], o):'';
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
  render() {
    const { data, columns,pk,selected,selectedKey } = this.props;
    const {el,makeid}=this;
    this.index=0;
    //if(selected)
    //console.warn(selected[pk],selected[selectedKey]);
    return el("tbody", {key:'tbody'},
    data.map((item,index) => el("tr", {
      key: (pk?item[pk]:item[0]).toString()+makeid().toString(),
      className:selected&&pk&&selected[pk]==item[pk]&&selected[selectedKey]===item[selectedKey]?'selected':null
    }, columns.map(c => el("td", {
      key: c.path || c.key||c.label.replace(' ','')+makeid().toString()
    }, c.content ? c.content(item) : this.get(item, c.path)))))
    );     
  }
}

// items
export default TableBody;

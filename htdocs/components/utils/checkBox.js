class CheckBox extends React.Component {
  el = React.createElement;  
  render() {
    const { label, name, checked, onChange } = this.props;
    // return this.el("div", {className:'check-box',  onClick: onChange}, [
    //   this.el("i", {
    //     key: name,         
    //     className: "far fa-2x " + (checked ? "fa-check-square" : "fa-square"),
       
    //   }),
    //   this.el("label", { key: name + "_label" }, label),
    // ]);

    return this.el("div", {className:'check-box',onClick:onChange }, [
      this.el("input", {
        type:'checkbox',
        key: name,         
        defaultChecked :checked,    
        
      }),
      this.el("label", { key: name + "_label" }, label),
    ]);
  }
}
export default CheckBox;

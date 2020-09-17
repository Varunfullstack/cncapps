class Toggle extends React.Component {
    el=React.createElement;
    constructor(props) {
        super(props);
        this.state = {  }
    }
    render() { 
        const {el}=this;
        const {checked,onChange,name}=this.props;
        return el('label',{className:"switch",key: name,},
        el("input",{type:"checkbox",defaultChecked: checked,onChange:onChange}),
        el("span",{className:"slider round"} )
        );
    }
}
 
export default Toggle;
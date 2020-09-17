
class Stepper extends React.Component {
    el = React.createElement;
    constructor(props) {
        super(props);
        this.state = {  }
    }
    handleOnClick=(step)=>{
       
        if(this.props.onChange&&(!step?.disabled&&true))
            this.props.onChange(step);
    }
    render() { 
        const {steps}=this.props;
        const {el,handleOnClick}=this
        return steps&&steps.length>0?el(
            "div",
            { className: "wrapper-progressBar" },
            el(
              "ul",
              { className: "progressBar" },
              steps.filter(s=>s.display).map((s) =>
                el("li", {key:s.id, className: s.active ? "active" : "",onClick:()=>handleOnClick(s) }, 
                el('div',{className:"title-container"},el("span",{className:"title"},s.title),el("span",{className:"number"},s.id))
                )
              )
            )
          ):null;
    }
}
 
export default Stepper;
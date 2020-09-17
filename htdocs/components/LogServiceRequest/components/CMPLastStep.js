import CheckBox from "../../utils/checkBox.js";
import SVCLogService from "../SVCLogService.js?v=1";
import Toggle from "../../utils/toggle.js";

class CMPLastStep extends React.Component {
  el = React.createElement;
  api=new SVCLogService();

  constructor(props) {
    super(props);
    const {data}=this.props;
    this.state = {
        checkList:[],
        data: {
            existingProblem: data.existingProblem|| false,
            criticalSR:data.criticalSR|| false,
            hideSR:data.hideSR|| false,
            monitorSR:data.monitorSR|| false,
            priorityId: data.priorityId|| -1,
            internalNotesId:data.internalNotesId|| -1
        },
    };
  }
  componentDidMount() {
    this.api.getCheckList().then(res=>{
        //console.log(res);
        this.setState({checkList:res});
    })
  }
  getChkProblemBefore = () => {
    const { el, handleCheckBoxChange } = this;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Have they had this problem before?")),
      el(
        "td",
        null,
        el(Toggle, {
          name: "exitistingProblem",                    
          checked: this.state.data.existingProblem,
          onChange:()=>handleCheckBoxChange('existingProblem'),
        })
      )
    );
  };
  getCriticalSRBefore = () => {
    const { el, handleCheckBoxChange } = this;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Critical SR")),
      el(
        "td",
        null,
        el(Toggle, {
          name: "criticalSR",                  
          checked: this.state.data.criticalSR,
          onChange:()=>handleCheckBoxChange('criticalSR'),
        })
      )
    );
  };
  getHideSR = () => {
    const { el, handleCheckBoxChange } = this;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Hide Entire SR From Customer")),
      el(
        "td",
        null,
        el(Toggle, {
          name: "hideSR",          
          checked: this.state.data.hideSR,
          onChange:()=>handleCheckBoxChange('hideSR'),
        })
      )
    );
  };
  getMonitorSR	 = () => {
    const { el, handleCheckBoxChange } = this;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Monitor SR")),
      el(
        "td",
        null,
        el(Toggle, {
          name: "monitorSR",         
          checked: this.state.data.monitorSR,
          onChange:()=>handleCheckBoxChange('monitorSR'),
        })
      )
    );
  };
  handleCheckBoxChange = (prop) => {
    const { data } = this.state;    
    data[prop] = !data[prop];    
    this.setState({ data });
  };
  handleValueChange = (prop,value) => {    
    const { data } = this.state;
    data[prop] = value; 
    this.setState({ data });
  };
  handleNext = () => {
    const { data,checkList } = this.state;  
    console.log(checkList);
    if(data.internalNotesId  >-1)
    data.checkList=checkList.filter(c=>c.id==data.internalNotesId)[0];
    this.props.updateSRData(data);
    //this.props.setActiveStep(4)
  };
  getNextButton = () => {
    const { el, handleNext } = this;
    return el(
      "div",
      null,
      el("button", { onClick: handleNext, className: "float-right" }, "Save")
    );
  };
  getProblemPriority = () => {
    const { el, handleValueChange } = this;
    const { data } = this.state;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "How serious is this issue?")),
      el(
        "td",
        null,
        el(
          "select",
          {
            value: data.priorityId,
            onChange: (event) => handleValueChange("priorityId", event.target.value),
            style:{width:200}
          },
          el("option", { value: -1 }, "Select Priority"),
          el("option", { value: 1 }, "It's affecting everybody (P1)"),
          el(
            "option",
            { value: 2 },
            "It's affecting more than just one person but they can work (P2)"
          ),
          el("option", { value: 3 }, "It's only affecting me (P3)"),
          el("option", { value: 4 }, "This is a change and not a fault (P4)"),
          el("option", { value: 5 }, "This is a project work (P5)")
        )
      )
    );
  };
  getCheckList = () => {
    const { el, handleValueChange } = this;
    const { data ,checkList} = this.state;
    return el(
      "tr",
      null,
      el("td", null, el("label", null, "Is a checklist needed?")),
      el(
        "td",
        null,
        el(
          "select",
          {            
            value: data.internalNotesId,
            onChange: (event) => handleValueChange("internalNotesId", event.target.value),
            style:{width:200}
          },
          el("option", {key:'i_1', value: -1 }, "Select Standard Text "),
          checkList.map(s=>
          el("option", {key:'i'+ s.id, value: s.id }, s.title)),
        
        )
      )
    );
  };
  getElements = () => {
    const { el, getChkProblemBefore, getProblemPriority,getCheckList
    ,getCriticalSRBefore,
    getHideSR,
    getMonitorSR } = this;
    return el("table", null, el("tbody", null,    
     getChkProblemBefore(),     
     getCriticalSRBefore(),
     getHideSR(),     
     getMonitorSR(),
     getProblemPriority(),
     getCheckList()
     ));
  };
  render() {
    const { el, getNextButton, getElements } = this;
    return el("div", null, getElements(), getNextButton());
  }
}

export default CMPLastStep;

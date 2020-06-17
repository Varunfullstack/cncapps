 "use strict";
import CheckBox from './utils/checkBox.js';
 class CMPMySettings extends React.Component {  
  el = React.createElement;
  constructor(props) {
    super(props);
    this.state = { };
  }

  componentDidMount()
  {
    fetch('?action=getMySettings')
    .then(res => res.json())
    .then(data=>{     
      data.lengthOfServices=(moment().diff(moment(data.startDate),'months')/12).toFixed(1);
      this.setState({...data})
      console.log(data);
    })
  }
  getElement(key,label,value)
  {
    return [
      this.el('dt',{key:key+"_label",className:'col-3' },label),
      this.el('dd',{key:key+'_value',className:'col-9'},value),
    ]; 
  }

  getUserLog()
  {
    if(this.state.userLog)
    return this.el("ul", { className: "list-group user-log",key:"user_log" }, [
      this.state.userLog.map((log) => {
        return this.el('li',{className:'list-group-item',key:log.userTimeLogID},log.loggedDate+' '+log.startedTime)
      }),
    ]);
    else return null;
  }
  handleOnChange=()=>{
    console.log('change');
  }
  render() {
    
    return this.el(
      "div",
      {className:'my-account'},
      [
        
      this.el('h1',{key:'section_title_1'},'About Me'), 
      this.el('dl',{className:'row',key:'about_me'},[
          this.getElement('name','Name',this.state.name),

          this.getElement('jobTitle','Job Title',this.state.jobTitle),

          this.getElement('startDate','Start Date',this.state.startDate),

          this.getElement('lengthOfServices','Length Of Services',this.state.lengthOfServices),
          
          this.getElement('manager','Manager',this.state.manager),

          this.getElement('team','Team',this.state.team),
          
          this.getElement('userLog','User Log',''),
      ]),
      this.getUserLog(),      
      this.el(CheckBox,{key:'sendMeEmail', name:'sendMeEmail',label:"Send me an email when I'm assigned a Service Request.",checked:true,onChange:this.handleOnChange()},null) ,
    ]
    );
  }
}
export default CMPMySettings;

const domContainer = document.querySelector('#react_main');
ReactDOM.render(React.createElement(CMPMySettings), domContainer);

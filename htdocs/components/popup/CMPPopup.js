import MainComponent from "../CMPMainComponent.js?v=1";
import CMPDailyStats from "../SDManagerDashboard/components/CMPDailyStats.js?v=1"
import {params} from "../utils/utils.js?v=1"
class CMPPupop extends MainComponent {
  el=React.createElement
  constructor(props) {
    super(props);
    this.state = {  }
   }
   render() { 
       const {el}=this;
       const action =params.get("action");
       switch(action)
       {
           case "dailyStats":
            return el(CMPDailyStats);
            default:
                return el('label',null, "Not Found")
       }     
   }
}
export default CMPPupop;
const domContainer = document.querySelector("#reactMainPopup");
ReactDOM.render(React.createElement(CMPPupop), domContainer);
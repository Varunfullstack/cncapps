import MainComponent from "../CMPMainComponent.js";
import { params } from "../utils/utils.js";
import CMPActivityList from "./components/CMPActivityList.js";

class CMPActivityType extends MainComponent {
    el=React.createElement;
    constructor(props) {
        super(props);
        this.state = {  }
    }
    render() { 
        const action=params.get("action");
        switch(action)
        {
            default:
                return this.el(CMPActivityList);
        }        
    }
}
 
export default CMPActivityType;
 const domContainer = document.querySelector("#reactCMPActivityType");
 if(domContainer)
ReactDOM.render(React.createElement(CMPActivityType), domContainer);



"use strict";
import { params } from "../utils/utils.js?v=10";
import CMPActivityDisplay from "./components/CMPActivityDisplay.js?v=10";
import CMPActivityEdit from "./components/CMPActivityEdit.js?v=10";
import CMPGatherFixedInformation from "./components/CMPGatherFixedInformation.js?v=10";
class CMPActivity extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
        this.state = {  }
    }

    render() { 
        const action=params.get('action');
        console.log(action);
        const {el}=this;
        return ( 
            el('div',null,
            action==='displayActivity'?el(CMPActivityDisplay,null):null ,
            action==='editActivity'?el(CMPActivityEdit,null):null,
            action==='gatherFixedInformation'?el(CMPGatherFixedInformation):null,
            )
            );
    }
}
 
const domContainer = document.querySelector("#reactMainActivity");
ReactDOM.render(React.createElement(CMPActivity), domContainer);
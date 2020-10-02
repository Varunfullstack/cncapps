

"use strict";
import { params } from "../utils/utils.js";
import CMPActivityDisplay from "./components/CMPActivityDisplay.js"
import CMPActivityEdit from "./components/CMPActivityEdit.js";
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
            action==='editActivity'?el(CMPActivityEdit,null):null 

            )
            );
    }
}
 
const domContainer = document.querySelector("#reactMainActivity");
ReactDOM.render(React.createElement(CMPActivity), domContainer);
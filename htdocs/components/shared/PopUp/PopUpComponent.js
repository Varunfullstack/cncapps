import MainComponent from "../MainComponent.js?v=1";
import DailyStatsComponent from "../../SDManagerDashboard/subComponents/DailyStatsComponent.js?v=1"
import {params} from "../../utils/utils"

class PopUpComponent extends MainComponent {
    el = React.createElement

    constructor(props) {
        super(props);
        this.state = {}
    }

    render() {
        const {el} = this;
        const action = params.get("action");
        switch (action) {
            case "dailyStats":
                return el(DailyStatsComponent);
            default:
                return el('label', null, "Not Found")
        }
    }
}

export default PopUpComponent;
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector("#reactMainPopup");
    ReactDOM.render(React.createElement(PopUpComponent), domContainer);
})

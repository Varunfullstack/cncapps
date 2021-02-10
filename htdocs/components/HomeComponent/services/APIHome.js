import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIHome extends APIMain {
    getUpcomingVisits() {
        return this.get(`${ApiUrls.Home}getUpcomingVisitsData`);
    }

    getSalesFigures() {
        return this.get(`${ApiUrls.Home}salesFigures`);
    }

    getFixedAndReopenData() {
        return this.get(`${ApiUrls.Home}getFixedAndReopenData`);
    }

    getFirstTimeFixData() {
        return this.get(`${ApiUrls.Home}getFirstTimeFixData`);
    }

    getTeamPerformance() {
        return this.get(`${ApiUrls.Home}teamPerformance`);
    }

    getAllUserPerformance() {
        return this.get(`${ApiUrls.Home}allUserPerformance`);
    }

    getUserPerformance() {
        return this.get(`${ApiUrls.Home}userPerformance`);
    }

    getDefaultLayout() {
        return this.get(`${ApiUrls.Home}defaultLayout`);
    }

    setDefaultLayout(settings) {
        const body = {
            settings: JSON.stringify(settings),
        }
        return this.post(`${ApiUrls.Home}defaultLayout`, body);
    }

    getTeamsFeedback(){
        return this.get(`${ApiUrls.Home}teamsFeedback`);
    }
}
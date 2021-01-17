import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIRenewals extends APIMain {
    getTimeRequest(filter) {
        return fetch(
            `${ApiUrls.RenewalsDashboard}getTimeRequest&` + this.getTeams(filter)
        ).then((res) => res.json());
    }
}
import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIRequestDashboard extends APIMain {
    getTimeRequest(filter) {
        return fetch(
            `${ApiUrls.RequestDashboard}getTimeRequest&` + this.getTeams(filter)
        ).then((res) => res.json());
    }

    getChangeRequest(filter) {
        return fetch(
            `${ApiUrls.RequestDashboard}getChangeRequest&` + this.getTeams(filter)
        ).then((res) => res.json());
    }

    getSalesRequest(filter) {
        const salesFilter = {...filter};
        salesFilter.hd = true;
        salesFilter.es = true;
        salesFilter.p = true;
        salesFilter.sp = true;
        return fetch(
            `${ApiUrls.RequestDashboard}getSalesRequest&` + this.getTeams(salesFilter)
        ).then((res) => res.json());
    }

    getTeams(filter) {
        let teams = "";
        if (filter.hd)
            teams += "HD&";
        if (filter.es)
            teams += "ES&";
        if (filter.sp)
            teams += "SP&";
        if (filter.p)
            teams += "P&";
        teams += "limit=" + filter.limit;
        return teams;
    }

    setTimeRequest(body) {
        return this.post(`${ApiUrls.RequestDashboard}setTimeRequest`, body).then((res) => res.json());
    }

    processChangeRequest(body) {
        return this.post(`${ApiUrls.RequestDashboard}processChangeRequest`, body).then((res) => res.json());
    }

    processSalesRequest(body) {
        return this.post(`${ApiUrls.RequestDashboard}processSalesRequest`, body).then((res) => res.json());
    }

    setAllocateUser(userID, problemID) {
        return this.post(`${ApiUrls.RequestDashboard}setAllocateUser&userID=${userID}&problemID=${problemID}`, null).then((res) => res.json());
    }
}

import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIDailyReport extends APIMain {
    getOutStandingIncidents(filter) {
        return fetch(
            `${ApiUrls.AgedService}getoutstandingIncidents&` + new URLSearchParams(filter).toString()
        ).then((res) => res.json());
    }

    getDailyStatsSummary() {
        return this.get(`${ApiUrls.AgedService}dailyStatsSummary`);
    }

    getYears() {
        return this.get(`${ApiUrls.AgedService}years`);
    }

    getOutStandingPerYear(year) {
        return this.get(`${ApiUrls.DailyReport}outstandingReportPerformanceDataForYear&year=${year}`);
    }
}

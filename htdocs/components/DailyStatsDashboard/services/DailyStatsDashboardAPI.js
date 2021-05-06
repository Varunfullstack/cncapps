import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class DailyStatsDashboardAPI extends APIMain {
    getQueue(id, filter) {
        return fetch(
            `${ApiUrls.DailyStatsDashboard}getQueue&queue=${id}&` + new URLSearchParams(filter).toString()
        ).then((res) => res.json());
    }

    getDailyStatsSummary() {
        return this.get(`${ApiUrls.DailyStatsDashboard}dailyStatsSummary`);
    }

    getMissedCallBacks(hd, es, sp, p, limit) {
        return this.get(`${ApiUrls.DailyStatsDashboard}missedCallBack&hd=${hd}&es=${es}&sp=${sp}&p=${p}&limit=${limit}`);

    }

    getPendingChargeableRequests(hd, es, sp, p, limit) {
        return this.get(`${ApiUrls.DailyStatsDashboard}getPendingChargeableRequests&hd=${hd}&es=${es}&sp=${sp}&p=${p}&limit=${limit}`).then(res => res.data);
    }

    async cancelChargeableRequest(id) {
        return this.post(`${ApiUrls.DailyStatsDashboard}cancelPendingChargeableRequest`, {id})
    }

    async resendChargeableRequestEmail(id) {
        return this.post(`${ApiUrls.DailyStatsDashboard}resendPendingChargeableRequestEmail`, {id})
    }

    getUserProblemsSummary(option, customerID, queue) {
        let url = `${ApiUrls.DailyStatsDashboard}userProblemsSummary`;
        if (option) {
            url += `&option=${option}`;
        }
        if (customerID) {
            url += `&customerID=${customerID}`;
        }
        if (queue) {
            url += `&queueId=${queue}`;
        }
        return this.get(url);

    }
}

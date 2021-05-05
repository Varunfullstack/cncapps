import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APISDManagerDashboard extends APIMain {
    getQueue(id, filter) {
        return fetch(
            `${ApiUrls.sdDashboard}getQueue&queue=${id}&` + new URLSearchParams(filter).toString()
        ).then((res) => res.json());
    }

    getDailyStatsSummary() {
        return this.get(`${ApiUrls.sdDashboard}dailyStatsSummary`);
    }

    getMissedCallBacks(hd, es, sp, p, limit) {
        return this.get(`${ApiUrls.sdDashboard}missedCallBack&hd=${hd}&es=${es}&sp=${sp}&p=${p}&limit=${limit}`);

    }

    getPendingChargeableRequests(hd, es, sp, p, limit) {
        return this.get(`${ApiUrls.sdDashboard}getPendingChargeableRequests&hd=${hd}&es=${es}&sp=${sp}&p=${p}&limit=${limit}`).then(res => res.data);
    }

    async cancelChargeableRequest(id) {
        return this.post(`${ApiUrls.sdDashboard}cancelPendingChargeableRequest`, {id})
    }

    async resendChargeableRequestEmail(id) {
        return this.post(`${ApiUrls.sdDashboard}resendPendingChargeableRequestEmail`, {id})
    }

    getUserProblemsSummary(option, customerID, queue) {
        let url = `${ApiUrls.sdDashboard}userProblemsSummary`;
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

    moveSR(from, to, option, customerID, queue, exchange = false) {
        return this.post(`${ApiUrls.sdDashboard}moveSR`, {
            from,
            to,
            option,
            customerID,
            queue,
            exchange
        });
    }

    getUnassignedSummary(hd, es, p, sp) {
        let url = `${ApiUrls.sdDashboard}unassignedSummary`;
        if (hd)
            url = "&hd";
        if (es)
            url = "&es";
        if (p)
            url = "&p";
        if (sp)
            url = "&sp";
        return this.get(url);
    }

}

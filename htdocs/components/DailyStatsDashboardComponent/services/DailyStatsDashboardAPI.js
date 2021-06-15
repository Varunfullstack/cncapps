import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class DailyStatsDashboardAPI extends APIMain {
    async getNearSLA() {
        const response = await fetch(
            `${ApiUrls.DailyStatsDashboard}nearSLA`
        )
        return this._getDataOrErrorFromResponse(response);
    }

    async getNearFixSLABreach() {
        const response = await fetch(
            `${ApiUrls.DailyStatsDashboard}nearFixSLABreach`
        )
        return this._getDataOrErrorFromResponse(response);
    }

    async getRaisedOn(date) {
        const url = new URLSearchParams();
        url.append('date', date);
        const response = await fetch(`${ApiUrls.DailyStatsDashboard}raisedOn&${url}`)
        return this._getDataOrErrorFromResponse(response);
    }

    async getStartedOn(date) {
        const url = new URLSearchParams();
        url.append('date', date);
        const response = await fetch(`${ApiUrls.DailyStatsDashboard}startedOn&${url}`)
        return this._getDataOrErrorFromResponse(response);
    }

    async getFixedOn(date) {
        const url = new URLSearchParams();
        url.append('date', date);
        const response = await fetch(`${ApiUrls.DailyStatsDashboard}fixedOn&${url}`)
        return this._getDataOrErrorFromResponse(response);
    }

    async getReopenedOn(date) {
        const url = new URLSearchParams();
        url.append('date', date);
        const response = await fetch(`${ApiUrls.DailyStatsDashboard}reopenedOn&${url}`)
        return this._getDataOrErrorFromResponse(response);
    }

    async breachedSLAOn(date) {
        const url = new URLSearchParams();
        url.append('date', date);
        const response = await fetch(`${ApiUrls.DailyStatsDashboard}breachedSLAOn&${url}`)
        return this._getDataOrErrorFromResponse(response);
    }

    async _getDataOrErrorFromResponse(response) {
        const decodedBody = await response.json();
        if (decodedBody.status !== 'ok') {
            let message = "Server Operation Failed!";
            if ("message" in decodedBody) {
                message = `${message}: ${decodedBody.message} `;
            }
            throw new Error(message);
        }
        return decodedBody.data;
    }
}

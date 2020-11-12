import APIMain from "../../services/APIMain.js?v=1";
import ApiUrls from "../../services/ApiUrls.js?v=1";

export default class APISDManagerDashboard extends APIMain {
  getQueue(id,filter) {
    return fetch(
      `${ApiUrls.sdDashboard}getQueue&queue=${id}&`+new URLSearchParams(filter).toString()
    ).then((res) => res.json());
  }
  getDailyStatsSummary() {
    return this.get(`${ApiUrls.sdDashboard}dailyStatsSummary` );
  }
}

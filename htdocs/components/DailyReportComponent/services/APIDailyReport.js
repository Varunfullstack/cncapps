import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIDailyReport extends APIMain {
  getQueue(id,filter) {
    return fetch(
      `${ApiUrls.DailyReport}getQueue&queue=${id}&`+new URLSearchParams(filter).toString()
    ).then((res) => res.json());
  }
  getDailyStatsSummary() {
    return this.get(`${ApiUrls.DailyReport}dailyStatsSummary` );
  }
}

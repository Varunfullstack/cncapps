import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APISDManagerDashboard extends APIMain {
  getQueue(id,filter) {
    return fetch(
      `${ApiUrls.sdDashboard}getQueue&queue=${id}&`+new URLSearchParams(filter).toString()
    ).then((res) => res.json());
  }
  getDailyStatsSummary() {
    return this.get(`${ApiUrls.sdDashboard}dailyStatsSummary` );
  }
  getMissedCallBacks(hd,es,sp,p,limit){    
    return this.get(`${ApiUrls.sdDashboard}missedCallBack&hd=${hd}&es=${es}&sp=${sp}&p=${p}&limit=${limit}` );

  }
}

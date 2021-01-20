import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIMySettings extends APIMain {
   
  saveMySettings(data) {
    return this.post(`${ApiUrls.MySettings}mySettings`,data );
  }
}

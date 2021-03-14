import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APISalesRequest extends APIMain {
  CreateSalesRequest(files,body) {
    return this.uploadFiles(`${ApiUrls.CreateSalesRequest}salesRequest`,files,'file[]',body).then(res=>res.json());
  } 
}

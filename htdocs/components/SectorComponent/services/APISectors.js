import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APISectors extends APIMain {
  getAllTypes() {
    return this.get(`${ApiUrls.Sector}sectors`);
  }

  addType(body) {
    return this.postJson(`${ApiUrls.Sector}sectors`, body);
  }
  
  updateType(body) {
    return this.put(`${ApiUrls.Sector}sectors`, body);
  }

  deleteType(id) {
    return this.delete(`${ApiUrls.Sector}sectors&&id=${id}`);
  }
  
}

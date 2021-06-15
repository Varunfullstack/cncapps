import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIManufacturer extends APIMain {
  getAllTypes() {
    return this.get(`${ApiUrls.Manufacturer}items`);
  }
  getTypeList()
  {
    return this.get(`${ApiUrls.Manufacturer}manufacturerList`);
  }

  addType(body) {
    return this.postJson(`${ApiUrls.Manufacturer}items`, body);
  }
  
  updateType(body) {
    return this.put(`${ApiUrls.Manufacturer}items`, body);
  }

  deleteType(id) {
    return this.delete(`${ApiUrls.Manufacturer}items&&id=${id}`);
  }
  
}

import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APICustomerType extends APIMain {
  getAllTypes() {
    return this.get(`${ApiUrls.CustomerType}types`);
  }
  addType(body) {
    return this.postJson(`${ApiUrls.CustomerType}types`, body);
  }
  updateType(body) {
    return this.put(`${ApiUrls.CustomerType}types`, body);
  }
  deleteType(id) {
    return this.delete(`${ApiUrls.CustomerType}types&&id=${id}`);
  }
}

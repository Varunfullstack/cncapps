import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIIgnoredADDomains extends APIMain {
  getAllDomains() {
    return this.get(`${ApiUrls.IgnoredADDomains}domains`);
  }
  addDomain(body) {
    return this.postJson(`${ApiUrls.IgnoredADDomains}domains`, body);
  }
  updateDomain(body) {
    return this.put(`${ApiUrls.IgnoredADDomains}domains`, body);
  }
  deleteDomain(id) {
    return this.delete(`${ApiUrls.IgnoredADDomains}domains&&id=${id}`);
  }
}

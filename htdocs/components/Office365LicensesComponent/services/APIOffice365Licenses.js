import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIOffice365Licenses extends APIMain {
  getAllLicenses() {
    return this.get(`${ApiUrls.Office365Licenses}licenses`);
  }
  addLicense(body) {
    return this.postJson(`${ApiUrls.Office365Licenses}licenses`, body);
  }
  updateLicense(body) {
    return this.put(`${ApiUrls.Office365Licenses}licenses`, body);
  }
  deleteLicense(id) {
    return this.delete(`${ApiUrls.Office365Licenses}licenses&&id=${id}`);
  }
}

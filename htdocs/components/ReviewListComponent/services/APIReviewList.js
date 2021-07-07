import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIReviewList extends APIMain {
  getReviews() {
    return this.get(`${ApiUrls.ReviewList}getData&draw=1&columns%5B5%5D%5Bdata%5D=reviewDate&columns%5B5%5D%5Bname%5D=reviewDate&columns%5B5%5D%5Bsearchable%5D=true&columns%5B5%5D%5Borderable%5D=true&columns%5B5%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B5%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B6%5D%5Bdata%5D=reviewTime&columns%5B6%5D%5Bname%5D=reviewTime&columns%5B6%5D%5Bsearchable%5D=true&columns%5B6%5D%5Borderable%5D=true&columns%5B6%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B6%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B7%5D%5Bdata%5D=latestUpdate&columns%5B7%5D%5Bname%5D=latestUpdate&columns%5B7%5D%5Bsearchable%5D=true&columns%5B7%5D%5Borderable%5D=true&columns%5B7%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B7%5D%5Bsearch%5D%5Bregex%5D=false&columns%5B8%5D%5Bdata%5D=reviewUserName&columns%5B8%5D%5Bname%5D=reviewUserName&columns%5B8%5D%5Bsearchable%5D=true&columns%5B8%5D%5Borderable%5D=true&columns%5B8%5D%5Bsearch%5D%5Bvalue%5D=&columns%5B8%5D%5Bsearch%5D%5Bregex%5D=false&order%5B0%5D%5Bcolumn%5D=5&order%5B0%5D%5Bdir%5D=desc&order%5B1%5D%5Bcolumn%5D=6&order%5B1%5D%5Bdir%5D=asc&start=0&length=25&search%5Bvalue%5D=&search%5Bregex%5D=false&_=1625578834625`);
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

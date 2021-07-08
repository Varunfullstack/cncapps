import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIReviewList extends APIMain {
  getReviews(limit = 50, page = 1, orderBy = 'customerName', orderDir = 'asc', q = '', discontinued = null) {
    let url = `${ApiUrls.ReviewList}getData&limit=${limit}&page=${page}&orderBy=${orderBy}&orderDir=${orderDir}&q=${q}`;
    if (discontinued) {
        url = `${url}&discontinued=${discontinued}`;
    }
    return this.get(url);
}
}

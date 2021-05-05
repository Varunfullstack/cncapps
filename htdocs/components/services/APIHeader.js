import {sort} from "../utils/utils";
import APIMain from "./APIMain";
import ApiUrls from "./ApiUrls";


class APIHeader extends APIMain {
    getNumberOfAllowedMistaks(){
        return this.get(`${ApiUrls.Header}numberOfAllwoedMistaks`).then(res => res.value);
    }
    getKeywordMatchingPercent(){
        return this.get(`${ApiUrls.Header}keywordMatchingPercent`);
    }
    getHeaderData(){
        return this.get(`${ApiUrls.Header}header`);

    }
}

export default APIHeader;
import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIKeywordMatchingIgnores extends APIMain {      
    getWords() {
        return this.get(`${ApiUrls.KeywordMatchingIgnores}keywordsIgnore`);
    }
    AddWord(data) {
        return this.postJson(`${ApiUrls.KeywordMatchingIgnores}keywordsIgnore`,data);
    }
    UpdateWord(data) {
        return this.put(`${ApiUrls.KeywordMatchingIgnores}keywordsIgnore`,data);
    }
    deleteWord(id) {
        return this.delete(`${ApiUrls.KeywordMatchingIgnores}keywordsIgnore&&id=${id}`);
    }
}

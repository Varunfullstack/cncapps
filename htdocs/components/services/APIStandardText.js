import APIMain from "./APIMain.js";
import ApiUrls from "./ApiUrls.js";


class APIStandardText extends APIMain {
    getSalesRequestOptions = () => {
        return fetch(`${ApiUrls.StandardText}getSalesRequestOptions`).then(res => res.json());
    }

    getAllTypes() {
        return fetch(`${ApiUrls.StandardText}getList`)
            .then(res => res.json());
    }

    getOptionsByType = (type) => {
        return fetch(`${ApiUrls.StandardText}getByType&type=${type}`).then(res => res.json());
    }
}

export default APIStandardText;
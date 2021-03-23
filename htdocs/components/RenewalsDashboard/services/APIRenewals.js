import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APIRenewals extends APIMain {
    getRenewals() {
        return fetch(
            `${ApiUrls.Renewals}renewals`
        ).then((res) => res.json());
    }
    getRenContract() {
        return fetch(
            `${ApiUrls.Renewals}renContract`
        ).then((res) => res.json());
    }
    getRenBroadband() {
        return fetch(
            `${ApiUrls.Renewals}renBroadband`
        ).then((res) => res.json());
    }
    getRenDomain() {
        return fetch(
            `${ApiUrls.Renewals}renDomain`
        ).then((res) => res.json());
    }
    getRenHosting() {
        return fetch(
            `${ApiUrls.Renewals}renHosting`
        ).then((res) => res.json());
    }
}
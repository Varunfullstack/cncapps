import APIMain from "../../services/APIMain";
import ApiUrls from "../../services/ApiUrls";

export default class APICustomerFeedback extends APIMain {
    getCustomerFeedback(
        from = null,
        to = null,
        customerID = null,
        engineerID = null,
        hd = false,
        es = false,
        sp = false,
        p = false
    ) {
        const uri = new URL(`${document.location.origin}${ApiUrls.CustomerFeedback}search`);

        if (from) {
            uri.searchParams.append('from', from);
        }
        if (to) {
            uri.searchParams.append('to', to);
        }
        if (customerID) {
            uri.searchParams.append('customerID', customerID);
        }
        if (engineerID) {
            uri.searchParams.append('engineerID', engineerID);
        }
        if (hd) {
            uri.searchParams.append('hd', hd.toString());
        }
        if (es) {
            uri.searchParams.append('es', es.toString());
        }
        if (sp) {
            uri.searchParams.append('sp', sp.toString());
        }
        if (p) {
            uri.searchParams.append('p', p.toString());
        }


        return this.get(uri.toString());
    }
}
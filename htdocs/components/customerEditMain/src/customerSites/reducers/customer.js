import {CHANGE_DELIVER_SITE_NO, CHANGE_INVOICE_SITE_NO, INITIALIZE_CUSTOMER} from "../actionTypes";

const initialState = {
    customerId: null,
    invoiceSiteNo: null,
    deliverSiteNo: null
}

export default function (state = initialState, action) {
    switch (action.type) {
        case INITIALIZE_CUSTOMER: {
            const {customerId, invoiceSiteNo, deliverSiteNo} = action
            return {
                customerId,
                deliverSiteNo,
                invoiceSiteNo
            }
        }
        case CHANGE_DELIVER_SITE_NO: {
            const {siteNo} = action
            return {
                ...state,
                deliverSiteNo: siteNo
            }
        }
        case CHANGE_INVOICE_SITE_NO: {
            const {siteNo} = action
            return {
                ...state,
                invoiceSiteNo: siteNo
            }
        }
        default:
            return state
    }
}


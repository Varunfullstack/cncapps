import {
    CHANGE_DELIVER_SITE_NO,
    CHANGE_INVOICE_SITE_NO,
    INITIALIZE_CUSTOMER,
    SAVE_CUSTOMER_DATA_SUCCESS
} from "../actionTypes";

const initialState = {
    customerId: null,
    invoiceSiteNo: null,
    deliverSiteNo: null,
    hasPendingChanges: false
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
                deliverSiteNo: siteNo,
                hasPendingChanges: true
            }
        }
        case CHANGE_INVOICE_SITE_NO: {
            const {siteNo} = action
            return {
                ...state,
                invoiceSiteNo: siteNo,
                hasPendingChanges: true
            }
        }
        case SAVE_CUSTOMER_DATA_SUCCESS: {
            return {
                ...state,
                hasPendingChanges: false
            }
        }
        default:
            return state
    }
}


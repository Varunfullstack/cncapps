import {
    CHANGE_DELIVER_SITE_NO,
    CHANGE_INVOICE_SITE_NO,
    FETCH_ACCOUNT_MANAGERS_SUCCESS,
    FETCH_CUSTOMER_SUCCESS,
    FETCH_CUSTOMER_TYPES_SUCCESS,
    FETCH_LEAD_STATUSES_SUCCESS,
    FETCH_REVIEW_ENGINEERS_SUCCESS,
    FETCH_SECTORS_SUCCESS,
    SAVE_CUSTOMER_DATA_SUCCESS,
    UPDATE_CUSTOMER_VALUE
} from "../actionTypes";

const initialState = {
    customer: {
        accountManagerUserID: '',
        accountName: '',
        accountNumber: '',
        activeDirectoryName: '',
        becameCustomerDate: '',
        customerID: '',
        customerTypeID: '',
        droppedCustomerDate: '',
        gscTopUpAmount: '',
        lastReviewMeetingDate: '',
        leadStatusId: '',
        mailshotFlag: '',
        reviewDate: '',
        reviewTime: '',
        modifyDate: '',
        name: '',
        noOfPCs: '',
        noOfServers: '',
        noOfSites: '',
        primaryMainContactID: '',
        referredFlag: '',
        regNo: '',
        reviewMeetingBooked: '',
        reviewMeetingFrequencyMonths: '',
        sectorID: '',
        slaP1: '',
        slaP2: '',
        slaP3: '',
        slaP4: '',
        slaP5: '',
        sortCode: '',
        specialAttentionEndDate: '',
        specialAttentionFlag: '',
        support24HourFlag: '',
        techNotes: '',
        websiteURL: '',
        slaFixHoursP1: '',
        slaFixHoursP2: '',
        slaFixHoursP3: '',
        slaFixHoursP4: '',
        slaP1PenaltiesAgreed: '',
        slaP2PenaltiesAgreed: '',
        slaP3PenaltiesAgreed: '',
        reviewUserID: '',
        reviewAction: '',
        lastContractSent: '',
    },
    hasPendingChanges: false,
    customerTypes: [],
    leadStatuses: [],
    sectors: [],
    accountManagers: [],
    reviewEngineers: [],
}

export default function (state = initialState, action) {
    switch (action.type) {
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
        case FETCH_CUSTOMER_SUCCESS: {
            return {
                ...state,
                customer: action.customer
            }
        }
        case FETCH_CUSTOMER_TYPES_SUCCESS: {
            return {
                ...state,
                customerTypes: action.customerTypes
            }
        }
        case FETCH_LEAD_STATUSES_SUCCESS: {
            return {
                ...state,
                leadStatuses: action.leadStatuses
            }
        }
        case FETCH_SECTORS_SUCCESS: {
            return {
                ...state,
                sectors: action.sectors
            }
        }
        case FETCH_ACCOUNT_MANAGERS_SUCCESS: {
            return {
                ...state,
                accountManagers: action.accountManagers
            }
        }
        case FETCH_REVIEW_ENGINEERS_SUCCESS: {
            return {
                ...state,
                reviewEngineers: action.reviewEngineers
            }
        }
        case UPDATE_CUSTOMER_VALUE: {
            return {
                ...state,
                customer: {...state.customer, [action.field]: action.value}
            }
        }
        default:
            return state
    }
}


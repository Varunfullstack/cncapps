import {
    FETCH_ACCOUNT_MANAGERS_SUCCESS,
    FETCH_CUSTOMER_SUCCESS,
    FETCH_CUSTOMER_TYPES_SUCCESS,
    FETCH_LEAD_STATUSES_SUCCESS,
    FETCH_REVIEW_ENGINEERS_SUCCESS,
    FETCH_SECTORS_SUCCESS,
    REQUEST_UPDATE_CUSTOMER_FAILED,
    REQUEST_UPDATE_CUSTOMER_FAILED_OUT_OF_DATE,
    REQUEST_UPDATE_CUSTOMER_SUCCESS,
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
        opportunityDeal: '',
        dateMeetingConfirmed: '',
    },
    originalCustomer: {},
    hasPendingChanges: false,
    customerTypes: [],
    leadStatuses: [],
    sectors: [],
    accountManagers: [],
    reviewEngineers: [],
    customerNotes: [],
}

export default function (state = initialState, action) {
    switch (action.type) {
        case FETCH_CUSTOMER_SUCCESS: {
            return {
                ...state,
                customer: action.customer,
                originalCustomer: action.customer
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
        case REQUEST_UPDATE_CUSTOMER_FAILED_OUT_OF_DATE: {
            return {
                ...state,
                originalCustomer: {
                    ...state.originalCustomer,
                    lastUpdatedDateTime: action.lastUpdatedDateTime
                },
                customer: {
                    ...state.originalCustomer,
                    lastUpdatedDateTime: action.lastUpdatedDateTime
                }
            }
        }
        case REQUEST_UPDATE_CUSTOMER_SUCCESS: {
            return {
                ...state,
                originalCustomer: {
                    ...state.customer,
                    lastUpdatedDateTime: action.lastUpdatedDateTime
                },
                customer: {
                    ...state.customer,
                    lastUpdatedDateTime: action.lastUpdatedDateTime
                }
            }
        }
        case REQUEST_UPDATE_CUSTOMER_FAILED: {
            return {
                ...state,
                originalCustomer: {
                    ...state.originalCustomer,
                },
                customer: {
                    ...state.originalCustomer,
                }
            }
        }
        default:
            return state
    }
}


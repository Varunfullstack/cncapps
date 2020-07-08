import {ADD_SITE, SET_VISIBILITY_FILTER, VisibilityFilterOptions} from './actions';

function visibilityFilter(action, state = VisibilityFilterOptions.SHOW_ACTIVE) {
    if (action.type === SET_VISIBILITY_FILTER) {
        return action.filter
    } else {
        return state
    }
}

function sites(action, state = []) {
    switch (action.type) {
        case ADD_SITE:
            return [
                ...state,
                {
                    siteNo: -1,
                    address1: '',
                    address2: '',
                    address3: '',
                    town: '',
                    county: '',
                    postcode: '',
                    what3Words: '',
                    phone: '',
                    maxTravelHours: 1,
                    defaultInvoice: !state.length,
                    defaultDelivery: !state.length,
                    invoiceContact: '',
                    deliveryContact: '',
                    nonUK: false,
                    active: true,
                }
            ]
    }
}
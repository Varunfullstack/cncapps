import {INITIALIZE_CUSTOMER} from "../actionTypes";

const initialState = {
    customerId: null,
    defaultInvoice: null,
    defaultDelivery: null
}

export default function (state = initialState, action) {
    switch (action.type) {
        case INITIALIZE_CUSTOMER:
            const {customerId, defaultInvoice, defaultDelivery} = action
            return {
                customerId,
                defaultDelivery,
                defaultInvoice
            }
    }
}


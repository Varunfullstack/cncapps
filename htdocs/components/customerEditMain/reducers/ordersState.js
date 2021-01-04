import {FETCH_ORDERS_REQUEST, FETCH_ORDERS_SUCCESS} from "../actionTypes";

const initialState = {
    allIds: [],
    byIds: {},
    isFetching: false,
}
export default function (state = initialState, action) {
    switch (action.type) {
        case FETCH_ORDERS_REQUEST:
            return {
                ...state,
                isFetching: true,
            }
        case FETCH_ORDERS_SUCCESS:
            // we have received the list of sites
            return {
                ...state,
                ...action.orders.reduce(
                    (acc, order) => {
                        acc.allIds.push(order.id);
                        acc.byIds[order.id] = order
                        return acc;
                    }, {allIds: [], byIds: {}}
                ),
                isFetching: false,
            }
        default:
            return state
    }
}
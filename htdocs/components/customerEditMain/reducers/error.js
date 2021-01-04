import {ADD_ERROR, DISMISS_ERROR} from "../actionTypes";

const initialState = {
    items: []
}

export default function (state = initialState, action) {

    switch (action.type) {
        case DISMISS_ERROR:
            return {
                items: [
                    ...state.items.filter((value, index) => index !== +action.errorIndex)
                ]
            }
        case ADD_ERROR:
            return {
                items: [
                    ...state.items,
                    {
                        message: action.message,
                        variant: action.variant
                    }
                ]
            }
        default:
            return state
    }
}


import {VisibilityFilterOptions} from "../actions";
import {SET_VISIBILITY_FILTER} from "../actionTypes";

export default function (state = VisibilityFilterOptions.SHOW_ACTIVE, action) {
    if (action.type === SET_VISIBILITY_FILTER) {
        return action.filter
    } else {
        return state
    }
}
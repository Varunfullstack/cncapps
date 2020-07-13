import {VisibilityFilterOptions} from "../actions";
import {SET_VISIBILITY_FILTER} from "../actionTypes";

export default function (action, state = VisibilityFilterOptions.SHOW_ACTIVE) {
    if (action.type === SET_VISIBILITY_FILTER) {
        return action.filter
    } else {
        return state
    }
}
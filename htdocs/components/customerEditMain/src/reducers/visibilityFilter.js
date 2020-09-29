
import {TOGGLE_VISIBILITY} from "../actionTypes";
import {VisibilityFilterOptions} from "../actions";


export default function (state = VisibilityFilterOptions.SHOW_ACTIVE, action) {
    if (action.type === TOGGLE_VISIBILITY) {
        return action.filter = state === VisibilityFilterOptions.SHOW_ACTIVE ? VisibilityFilterOptions.SHOW_ALL : VisibilityFilterOptions.SHOW_ACTIVE
    } else {
        return state
    }
}
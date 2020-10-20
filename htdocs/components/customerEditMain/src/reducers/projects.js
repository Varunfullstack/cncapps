import {DELETE_PROJECT_SUCCESS, FETCH_PROJECTS_REQUEST, FETCH_PROJECTS_SUCCESS} from "../actionTypes";

const initialState = {
    allIds: [],
    byIds: {},
    isFetching: false,
}
export default function (state = initialState, action) {
    switch (action.type) {
        case FETCH_PROJECTS_REQUEST:
            return {
                ...state,
                isFetching: true,
            }
        case FETCH_PROJECTS_SUCCESS:
            // we have received the list of projects
            return {
                ...action.projects.reduce(
                    (acc, item) => {
                        acc.allIds.push(item.id);
                        acc.byIds[item.id] = item
                        return acc;
                    }, {allIds: [], byIds: {}}
                ),
                isFetching: false,
            }
        case DELETE_PROJECT_SUCCESS:
            return {
                ...state,
                byIds: {
                    ...Object
                        .keys(state.byIds)
                        .reduce((acc, key) => {
                                if (key !== action.id) {
                                    acc[key] = state.byIds[key];
                                }
                                return acc;
                            },
                            {}
                        )
                },
                allIds: [...state.allIds.filter(x => x !== action.id)]
            }
        default:
            return state
    }
}
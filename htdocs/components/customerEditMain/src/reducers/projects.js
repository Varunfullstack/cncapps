import {
    DELETE_PROJECT_SUCCESS,
    FETCH_PROJECTS_REQUEST,
    FETCH_PROJECTS_SUCCESS,
    HIDE_NEW_PROJECT_MODAL,
    NEW_PROJECT_FIELD_UPDATE,
    REQUEST_ADD_PROJECT_FAILURE,
    REQUEST_ADD_PROJECT_SUCCESS,
    SHOW_NEW_PROJECT_MODAL
} from "../actionTypes";

const newProjectInitialState = {
    description: '',
    summary: '',
    openedDate: ''
};

const initialState = {
    allIds: [],
    byIds: {},
    isFetching: false,
    newProject: newProjectInitialState,
    newProjectModalShown: false
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
                ...state,
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
        case SHOW_NEW_PROJECT_MODAL: {
            return {
                ...state,
                newProjectModalShown: true
            }
        }
        case HIDE_NEW_PROJECT_MODAL: {
            return {
                ...state,
                newProjectModalShown: false,
                newProject: newProjectInitialState
            }
        }
        case NEW_PROJECT_FIELD_UPDATE: {
            return {
                ...state,
                newProject: {
                    ...state.newProject,
                    [action.field]: action.value
                }
            }
        }
        case REQUEST_ADD_PROJECT_SUCCESS: {
            return {
                ...state,
                newProject: newProjectInitialState,
                newProjectModalShown: false,
                byIds: {
                    ...state.byIds,
                    [action.project.id]: {...action.project}
                },
                allIds: [...state.allIds, action.project.id]
            }
        }
        case REQUEST_ADD_PROJECT_FAILURE: {
            return {
                ...state,
                newProject: newProjectInitialState,
                newProjectModalShown: false,
            }
        }
        default:
            return state
    }
}
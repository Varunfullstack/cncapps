import {
    CLEAR_EDIT_SITE,
    DELETE_SITE_SUCCESS,
    FETCH_SITES_REQUEST,
    FETCH_SITES_SUCCESS,
    HIDE_NEW_SITE_MODAL,
    NEW_SITE_FIELD_UPDATE,
    REQUEST_ADD_SITE_FAILURE,
    REQUEST_ADD_SITE_SUCCESS,
    REQUEST_UPDATE_SITE_FAILED,
    REQUEST_UPDATE_SITE_FAILED_OUT_OF_DATE,
    REQUEST_UPDATE_SITE_SUCCESS,
    SAVE_SITE_SUCCESS,
    SET_EDIT_SITE,
    SHOW_NEW_SITE_MODAL,
    UPDATE_EDITING_SITE_VALUE,
    UPDATE_SITE
} from "../actionTypes";

const newSiteInitialState = {
    addressLine: '',
    town: '',
    postcode: '',
    phone: '',
    maxTravelHours: '',
}

const initialState = {
    allIds: [],
    byIds: {},
    isFetching: false,
    newSiteModalShow: false,
    newSite: newSiteInitialState,
    editingSite: null,
}


function getUpdatedEditingSite(state, updatedSites) {
    if (!state.editingSite) {
        return null;
    }

    const foundUpdatedEditingSite = updatedSites.find(x => x.siteNo === state.editingSite.siteNo && x.lastUpdatedDateTime > state.editingSite.lastUpdatedDateTime);
    if (!foundUpdatedEditingSite) {
        return state.editingSite;
    }
    return foundUpdatedEditingSite;
}

export default function (state = initialState, action) {
    switch (action.type) {
        case FETCH_SITES_REQUEST:
            return {
                ...state,
                isFetching: true,
            }
        case FETCH_SITES_SUCCESS:
            // we have received the list of sites
            return {
                ...state,
                ...action.sites.reduce(
                    (acc, site) => {
                        acc.allIds.push(site.siteNo);
                        acc.byIds[site.siteNo] = site
                        return acc;
                    }, {allIds: [], byIds: {}}
                ),
                isFetching: false,
                editingSite: getUpdatedEditingSite(state, action.sites)
            }
        case DELETE_SITE_SUCCESS:
            return {
                ...state,
                byIds: {
                    ...Object
                        .keys(state.byIds)
                        .reduce((acc, key) => {
                                if (key !== action.siteNo) {
                                    acc[key] = state.byIds[key];
                                }
                                return acc;
                            },
                            {}
                        )
                },
                allIds: [...state.allIds.filter(x => x !== action.siteNo)]
            }
        case UPDATE_SITE:
            //we are receiving changes from a component and we need to update the site with the changes
            return {
                ...state,
                byIds: {
                    ...state.byIds,
                    [action.siteNo]: {
                        ...state.byIds[action.siteNo],
                        ...action.data
                    }
                }
            }
        case SAVE_SITE_SUCCESS:
            return {
                ...state
            }
        case SHOW_NEW_SITE_MODAL: {
            return {
                ...state,
                newSiteModalShow: true
            }
        }
        case REQUEST_ADD_SITE_FAILURE:
        case HIDE_NEW_SITE_MODAL: {
            return {
                ...state,
                newSiteModalShow: false,
                newSite: newSiteInitialState
            }
        }
        case NEW_SITE_FIELD_UPDATE: {
            return {
                ...state,
                newSite: {
                    ...state.newSite,
                    [action.field]: action.value
                }
            }
        }
        case REQUEST_ADD_SITE_SUCCESS: {
            return {
                ...state,
                byIds: {
                    ...state.byIds,
                    [action.newSite.siteNo]: action.newSite,
                },
                newSiteModalShow: false,
                allIds: [...state.allIds, action.newSite.siteNo],
                newSite: newSiteInitialState
            }
        }
        case SET_EDIT_SITE: {
            return {
                ...state,
                editingSite: {...state.byIds[action.siteNo]}
            }
        }
        case CLEAR_EDIT_SITE: {
            return {
                ...state,
                editingSite: null
            }
        }
        case REQUEST_UPDATE_SITE_FAILED:
        case REQUEST_UPDATE_SITE_FAILED_OUT_OF_DATE: {
            return {
                ...state,
                editingSite: {...state.byIds[state.editingSite.siteNo]},
            }
        }
        case REQUEST_UPDATE_SITE_SUCCESS: {
            const updatedSite = {...state.editingSite, lastUpdatedDateTime: action.newLastUpdatedDateTime};
            return {
                ...state,
                editingSite: updatedSite,
                byIds: {...state.byIds, [state.editingSite.siteNo]: updatedSite}
            }
        }
        case UPDATE_EDITING_SITE_VALUE: {
            return {
                ...state,
                editingSite: {...state.editingSite, [action.field]: action.value}
            }
        }
        default:
            return state
    }

}


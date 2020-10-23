import {
    DELETE_SITE_SUCCESS,
    FETCH_SITES_REQUEST,
    FETCH_SITES_SUCCESS,
    HIDE_NEW_SITE_MODAL,
    NEW_SITE_FIELD_UPDATE,
    REQUEST_ADD_SITE_FAILURE,
    REQUEST_ADD_SITE_SUCCESS,
    SAVE_SITE_SUCCESS,
    SHOW_NEW_SITE_MODAL,
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
    lastUpdated: null,
    sitesPendingChanges: {},
    newSiteModalShow: false,
    newSite: newSiteInitialState
}

export default function (state = initialState, action) {
    switch (action.type) {
        case FETCH_SITES_REQUEST:
            return {
                ...state,
                isFetching: true,
                lastUpdated: null
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
                lastUpdated: new Date()
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
                },
                sitesPendingChanges: {
                    ...state.sitesPendingChanges,
                    [action.siteNo]: null
                }
            }
        case SAVE_SITE_SUCCESS:
            return {
                ...state,
                sitesPendingChanges: Object
                    .keys(state.sitesPendingChanges)
                    .reduce(
                        (acc, key) => {
                            if (key !== action.siteNo) {
                                acc[key] = null;
                            }
                            return acc;
                        },
                        {}
                    )
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
        default:
            return state
    }

}


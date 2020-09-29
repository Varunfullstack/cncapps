import {
    DELETE_SITE_SUCCESS,
    FETCH_SITES_REQUEST,
    FETCH_SITES_SUCCESS,
    SAVE_SITE_SUCCESS,
    UPDATE_SITE
} from "../actionTypes";

const initialState = {
    allIds: [],
    byIds: {},
    isFetching: false,
    lastUpdated: null,
    sitesPendingChanges: {}
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
        default:
            return state
    }

}


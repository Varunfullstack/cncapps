import {FETCH_SITES_REQUEST, FETCH_SITES_SUCCESS, SITE_DATA_SAVED, UPDATE_SITE} from "../actionTypes";

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
            console.log('fetch sites request')
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
        case SITE_DATA_SAVED:
            return {
                ...state,
                sitesPendingChanges: Object
                    .keys(state.sitesPendingChanges)
                    .reduce(
                        (acc, key) => {
                            if (key === action.siteNo) {
                                return acc;
                            }
                            acc[key] = null;
                        },
                        {}
                    )
            }
        default:
            return state
    }

}


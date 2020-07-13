import {FETCH_SITES_REQUEST, FETCH_SITES_SUCCESS} from "../actionTypes";

const initialState = {
    allIds: [],
    byIds: {},
    isFetching: false,
    lastUpdated: null
}

export default function (state = initialState, action) {
    switch (action.type) {
        case FETCH_SITES_REQUEST:
            
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

    }

}


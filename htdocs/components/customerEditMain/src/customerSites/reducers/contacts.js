import {FETCH_CONTACTS_REQUEST, FETCH_CONTACTS_SUCCESS} from "../actionTypes";

const initialState = {
    allIds: [],
    byIds: {},
    isFetching: false,
    lastUpdated: null
}

export default function (state = initialState, action) {
    switch (action.type) {
        case FETCH_CONTACTS_REQUEST:
            return {
                ...state,
                isFetching: true
            }
        case FETCH_CONTACTS_SUCCESS:
            // we have received the list of contacts
            return {
                ...action.contacts.reduce(
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


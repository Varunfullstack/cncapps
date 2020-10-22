import {
    DELETE_PORTAL_CUSTOMER_DOCUMENT_SUCCESS,
    FETCH_PORTAL_CUSTOMER_DOCUMENTS_REQUEST,
    FETCH_PORTAL_CUSTOMER_DOCUMENTS_SUCCESS,
    HIDE_NEW_PORTAL_CUSTOMER_DOCUMENT_MODAL,
    NEW_PORTAL_CUSTOMER_DOCUMENT_FIELD_UPDATE,
    REQUEST_ADD_PORTAL_CUSTOMER_DOCUMENT_FAILURE,
    REQUEST_ADD_PORTAL_CUSTOMER_DOCUMENT_SUCCESS,
    SHOW_NEW_PORTAL_CUSTOMER_DOCUMENT_MODAL,
} from "../actionTypes";

const newPortalDocumentInitialState = {
    description: '',
    customerContract: false,
    mainContractOnly: false,
    file: null
};

const initialState = {
    allIds: [],
    byIds: {},
    isFetching: false,
    newPortalDocument: newPortalDocumentInitialState,
    newPortalDocumentModalShown: false
}
export default function (state = initialState, action) {
    switch (action.type) {
        case FETCH_PORTAL_CUSTOMER_DOCUMENTS_REQUEST:
            return {
                ...state,
                isFetching: true,
            }
        case FETCH_PORTAL_CUSTOMER_DOCUMENTS_SUCCESS:
            // we have received the list of projects
            return {
                ...state,
                ...action.portalCustomerDocuments.reduce(
                    (acc, item) => {
                        acc.allIds.push(item.id);
                        acc.byIds[item.id] = item
                        return acc;
                    }, {allIds: [], byIds: {}}
                ),
                isFetching: false,
            }
        case DELETE_PORTAL_CUSTOMER_DOCUMENT_SUCCESS:
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
        case SHOW_NEW_PORTAL_CUSTOMER_DOCUMENT_MODAL: {
            return {
                ...state,
                newPortalDocumentModalShown: true
            }
        }
        case HIDE_NEW_PORTAL_CUSTOMER_DOCUMENT_MODAL: {
            return {
                ...state,
                newPortalDocumentModalShown: false,
                newPortalDocument: newPortalDocumentInitialState
            }
        }
        case NEW_PORTAL_CUSTOMER_DOCUMENT_FIELD_UPDATE: {
            return {
                ...state,
                newPortalDocument: {
                    ...state.newPortalDocument,
                    [action.field]: action.value
                }
            }
        }
        case REQUEST_ADD_PORTAL_CUSTOMER_DOCUMENT_SUCCESS: {
            return {
                ...state,
                newPortalDocument: newPortalDocumentInitialState,
                newPortalDocumentModalShown: false,
                byIds: {
                    ...state.byIds,
                    [action.portalCustomerDocument.id]: {...action.portalCustomerDocument}
                },
                allIds: [...state.allIds, action.portalCustomerDocument.id]
            }
        }
        case REQUEST_ADD_PORTAL_CUSTOMER_DOCUMENT_FAILURE: {
            return {
                ...state,
                newPortalDocument: newPortalDocumentInitialState,
                newPortalDocumentModalShown: false,
            }
        }
        default:
            return state
    }
}
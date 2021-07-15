import {
    CLEAR_EDIT_NOTE,
    DELETE_NOTE_SUCCESS,
    FETCH_CUSTOMER_NOTES_SUCCESS,
    GO_TO_FIRST_NOTE,
    GO_TO_LAST_NOTE,
    GO_TO_NEXT_NOTE,
    GO_TO_PREVIOUS_NOTE,
    HIDE_NEW_NOTE_MODAL,
    REQUEST_ADD_NOTE_FAILURE,
    REQUEST_ADD_NOTE_SUCCESS,
    REQUEST_UPDATE_NOTE_FAILED,
    REQUEST_UPDATE_NOTE_FAILED_OUT_OF_DATE,
    REQUEST_UPDATE_NOTE_SUCCESS,
    SET_EDIT_NOTE,
    SHOW_NEW_NOTE_MODAL,
    UPDATE_EDITING_NOTE_VALUE,
    UPDATE_NEW_NOTE_VALUE
} from "../actionTypes";

const initialState = {
    isFetching: false,
    currentNote: null,
    currentNoteIdx: 0,
    editingNote: null,
    byIds: {},
    allIds: [],
    newNoteModalShow: false,
    newNote: '',
}

const mapCustomerNotesToByIdsAndAllIds = (customerNotes) => {
    return customerNotes.reduce((acc, item) => {
        acc.byIds[item.id] = item;
        acc.allIds.push(item.id);
        return acc;
    }, {byIds: {}, allIds: []})
}

export default function (state = initialState, action) {
    switch (action.type) {
        case FETCH_CUSTOMER_NOTES_SUCCESS: {
            const mappedNotes = mapCustomerNotesToByIdsAndAllIds(action.customerNotes);
            return {
                ...state,
                ...mappedNotes,
                editingNote: action.customerNotes.length ? action.customerNotes[0] : null,
                isFetching: false
            }
        }
        case DELETE_NOTE_SUCCESS: {
            const newAllIds = [...state.allIds.filter(id => id !== action.id)];
            const newByIds = {
                ...Object
                    .keys(state.byIds)
                    .reduce((acc, key) => {
                            if (+key !== action.id) {
                                acc[key] = state.byIds[key];
                            }
                            return acc;
                        },
                        {}
                    )
            };
            let nextIndex = state.currentNoteIdx;
            let newEditingNote = null;
            if (nextIndex > newAllIds.length - 1) {
                nextIndex--;
            }
            if (nextIndex < 0) {
                nextIndex = 0;
            } else {
                newEditingNote = newByIds[newAllIds[nextIndex]];
            }
            console.log(action.id, state.byIds, newByIds);
            return {
                ...state,
                allIds: newAllIds,
                byIds: newByIds,
                editingNote: {...newEditingNote},
                currentNoteIdx: nextIndex
            }
        }
        case REQUEST_ADD_NOTE_SUCCESS: {
            return {
                ...state,
                byIds: {
                    ...state.byIds,
                    [action.newNote.id]: action.newNote,
                },
                newNoteModalShow: false,
                allIds: [action.newNote.id, ...state.allIds],
                newNote: '',
                editingNote: {...action.newNote},
                currentNoteIdx: 0
            }
        }
        case UPDATE_EDITING_NOTE_VALUE: {
            return {
                ...state,
                editingNote: {...state.editingNote, note: action.value}
            }
        }
        case REQUEST_UPDATE_NOTE_FAILED:
        case REQUEST_UPDATE_NOTE_FAILED_OUT_OF_DATE: {
            return {
                ...state,
                editingNote: {...state.byIds[state.editingNote.id]},
            }
        }
        case REQUEST_UPDATE_NOTE_SUCCESS: {
            const updateNote = {...action.modifiedNote};
            return {
                ...state,
                editingNote: updateNote,
                byIds: {...state.byIds, [state.editingNote.id]: updateNote}
            }
        }
        case SET_EDIT_NOTE: {
            return {
                ...state,
                editingNote: {...state.byIds[action.id]}
            }
        }
        case CLEAR_EDIT_NOTE: {
            return {
                ...state,
                editingNote: null
            }
        }
        case SHOW_NEW_NOTE_MODAL: {
            return {
                ...state,
                newNoteModalShow: true
            }
        }
        case REQUEST_ADD_NOTE_FAILURE:
        case HIDE_NEW_NOTE_MODAL: {
            return {
                ...state,
                newNoteModalShow: false,
                newNote: ''
            }
        }
        case UPDATE_NEW_NOTE_VALUE: {
            return {
                ...state,
                newNote: action.value
            }
        }
        case GO_TO_FIRST_NOTE: {
            const nextIndex = 0;
            return {
                ...state,
                editingNote: {...state.byIds[state.allIds[nextIndex]]},
                currentNoteIdx: nextIndex
            }
        }
        case GO_TO_PREVIOUS_NOTE: {
            const nextIndex = state.currentNoteIdx - 1
            if (nextIndex < 0) {
                return state;
            }
            return {
                ...state,
                editingNote: {...state.byIds[state.allIds[nextIndex]]},
                currentNoteIdx: nextIndex
            }
        }
        case GO_TO_NEXT_NOTE: {
            const nextIndex = state.currentNoteIdx + 1
            if (nextIndex >= state.allIds.length) {
                return state;
            }
            return {
                ...state,
                editingNote: {...state.byIds[state.allIds[nextIndex]]},
                currentNoteIdx: nextIndex
            }
        }
        case GO_TO_LAST_NOTE: {
            const nextIndex = state.allIds.length - 1
            if (nextIndex < 0) {
                return state;
            }
            return {
                ...state,
                editingNote: {...state.byIds[state.allIds[nextIndex]]},
                currentNoteIdx: nextIndex
            }
        }
        default:
            return state
    }
}


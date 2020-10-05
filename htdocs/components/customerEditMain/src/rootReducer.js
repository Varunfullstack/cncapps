import {combineReducers} from "redux";
import contacts from './reducers/contacts';
import sites from './reducers/sites';
import visibilityFilter from './reducers/visibilityFilter'
import customerEdit from './reducers/customerEdit'
import error from './reducers/error'

const rootReducer = combineReducers({
    contacts,
    sites,
    visibilityFilter,
    customerEdit,
    error
})

export default rootReducer
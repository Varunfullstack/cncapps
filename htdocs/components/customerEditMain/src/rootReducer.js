import {combineReducers} from "redux";
import contacts from './reducers/contacts';
import sites from './reducers/sites';
import visibilityFilter from './reducers/visibilityFilter'
import customerEdit from './reducers/customerEdit'

const rootReducer = combineReducers({
    contacts,
    sites,
    visibilityFilter,
    customerEdit,
})

export default rootReducer
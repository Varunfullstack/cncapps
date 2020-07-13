import {combineReducers} from "redux";
import contacts from 'reducers/contacts';
import sites from 'reducers/sites';
import visibilityFilter from 'reducers/visibilityFilter'
import customer from 'reducers/customer'

const rootReducer = combineReducers({
    contacts,
    sites,
    visibilityFilter,
    customer
})

export default rootReducer
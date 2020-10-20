import {combineReducers} from "redux";
import contacts from './reducers/contacts';
import sites from './reducers/sites';
import visibilityFilter from './reducers/visibilityFilter'
import customerEdit from './reducers/customerEdit'
import error from './reducers/error'
import projects from "./reducers/projects";

const rootReducer = combineReducers({
    contacts,
    sites,
    visibilityFilter,
    customerEdit,
    error,
    projects
})

export default rootReducer
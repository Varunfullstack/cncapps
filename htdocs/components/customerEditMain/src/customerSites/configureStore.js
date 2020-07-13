import thunkMiddleware from 'redux-thunk';
import {applyMiddleware, createStore} from "redux";
import rootReducer from "./reducers";


export default function configureStore(preloadedState) {
    return createStore(
        rootReducer,
        preloadedState,
        applyMiddleware(thunkMiddleware)
    )
}




import thunkMiddleware from 'redux-thunk';
import {applyMiddleware, createStore} from "redux";
import rootReducer from "./rootReducer";
import {composeWithDevTools} from 'redux-devtools-extension'
import createDebounce from 'redux-debounced';

const composeEnhancers = composeWithDevTools({
    trace: true,
    traceLimit: 25
})

export default function configureStore(preloadedState) {
    return createStore(
        rootReducer,
        preloadedState,
        composeEnhancers(applyMiddleware(createDebounce(), thunkMiddleware))
    )
}



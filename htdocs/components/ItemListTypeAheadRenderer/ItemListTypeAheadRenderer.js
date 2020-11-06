import React from 'react';
import ReactDOM from 'react-dom';
import TypeAheadSearch from "../shared/TypeAheadSearch";

function render(props, targetNode, callback) {
    const reactElement = React.createElement(TypeAheadSearch, props, null);
    ReactDOM.render(reactElement, targetNode, callback);
    return reactElement;
}

document.renderTypeAheadComponent = render;
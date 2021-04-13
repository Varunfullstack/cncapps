"use strict";
import React from 'react';

class AutoComplete extends React.Component {
    constructor(props) {
        super(props);

        this.state = {
            // The active selection's index
            activeSuggestion: 0,
            // The suggestions that match the user's input
            filteredSuggestions: this.props.items||[],
            // Whether or not the suggestion list is shown
            showSuggestions: false,
            // What the user has entered
            userInput: "",
            filtered: false,
            value:'',
            items:[]
        };

    }
     
    // Event fired when the input value is changed
    onChange = (e) => {
        let {items, displayLength, displayColumn} = this.props;
        if (!displayLength) displayLength = 10;
        const userInput = e.currentTarget.value;

        if(this.props.onFilter)
        {
            this.props.onFilter(userInput);
            this.setState({
                activeSuggestion: e.currentTarget.value.length > 0 ? 0 : -1,                
                showSuggestions: true,
                userInput:userInput,
                filtered: true
            });
            return;
        }
       

        // Filter our suggestions that don't contain the user's input
        let filteredSuggestions = items
            .filter(
                (suggestion) =>
                    (displayColumn ? suggestion[displayColumn] : suggestion).toLowerCase().indexOf(userInput.toLowerCase()) > -1
            );

        if (filteredSuggestions.length > displayLength) {
            filteredSuggestions = [
                {
                    name: "Keep typing to filter, there are more results not shown here",
                    id: null,
                },
                ...filteredSuggestions,
            ];
        }
        filteredSuggestions = filteredSuggestions.slice(0, displayLength);
        // Update the user input and filtered suggestions, reset the active
        // suggestion and make sure the suggestions are shown
        this.setState({
            activeSuggestion: e.currentTarget.value.length > 0 ? 0 : -1,
            filteredSuggestions,
            showSuggestions: true,
            userInput: e.currentTarget.value,
            filtered: true
        });
    };
    // Event fired when the user clicks on a suggestion
    onClick = (item) => {
        if (item.id != null) {
            const {displayColumn, onSelect} = this.props;
            // Update the user input and reset the rest of the state

            this.setState({
                activeSuggestion: 0,
                showSuggestions: false,
                userInput: displayColumn ? item[displayColumn] : item,
            });
            if (onSelect)
                onSelect(item);
        }
    };
    // Event fired when the user presses a key down
    onKeyDown = (e) => {
        const {activeSuggestion, filteredSuggestions, userInput} = this.state;
        const {displayColumn, onSelect} = this.props;

        if (userInput && userInput.length == 0) {
            this.setState({
                activeSuggestion: -1,
                showSuggestions: false,
                userInput: '',
                filteredSuggestions: []
            });
            onSelect(null);
            return;
        }
        if (e.keyCode == 27) //esc
        {
            this.setState({
                activeSuggestion: 0,
                showSuggestions: false,
                userInput: '',
                filteredSuggestions: []
            });
            return;
        }
        // User pressed the enter key, update the input and close the
        // suggestions
        if (e.keyCode == 13) {
            this.setState({
                activeSuggestion: 0,
                showSuggestions: false,
                userInput: displayColumn ? filteredSuggestions[activeSuggestion][displayColumn] : filteredSuggestions[activeSuggestion],
            });
            if (onSelect)
                onSelect(filteredSuggestions[activeSuggestion]);
        }
        // User pressed the up arrow, decrement the index
        else if (e.keyCode == 38) {
            if (activeSuggestion == 0) {
                return;
            }

            this.setState({activeSuggestion: activeSuggestion - 1});
        }
        // User pressed the down arrow, increment the index
        else if (e.keyCode == 40) {
            if (activeSuggestion - 1 == filteredSuggestions.length) {
                return;
            }

            this.setState({activeSuggestion: activeSuggestion + 1});
        }
    };
    handleOnClick = e => {
        let {items, displayLength} = this.props;
        const {userInput} = this.state;
        displayLength = displayLength ?? 10;
        let {filteredSuggestions} = this.state;
        if (filteredSuggestions.length == 0 && !userInput) {

            filteredSuggestions = items.slice(0, displayLength);
            if (items.length > displayLength) {
                filteredSuggestions = [
                    {
                        name: "Keep typing to filter, there are more results not shown here",
                        id: null,
                    },
                    ...filteredSuggestions,
                ];
            }
            this.setState({
                filteredSuggestions,
                showSuggestions: true
            })
        } else {
            this.setState({
                showSuggestions: true
            })
        }
    }
    handleOnBlur = e => {
        setTimeout(() => {
            this.setState({showSuggestions: false})
        }, 200);
    }
 
    static getDerivedStateFromProps(props,state){  
        if(props.value!=state.value)
        {
            state.value=props.value;
            state.userInput=props.value;
            return state;
        }
        else if(props.items.length!=state.items.length)
        {
            state.items=[...props.items];
            state.filteredSuggestions=[...props.items];
            return state;
        }
        else return state;
    }

    render() {
        const {displayColumn, pk, errorMessage, required, width} = this.props;
        const {
            onChange,
            onClick,
            onKeyDown,
            state: {
                activeSuggestion,
                filteredSuggestions,
                showSuggestions,
                userInput,
                filtered
            },
            handleOnClick,
            handleOnBlur
        } = this;
        let suggestionsListComponent;

        if (showSuggestions) {
            if (filteredSuggestions.length) {
                suggestionsListComponent = React.createElement("ul", {
                    className: "suggestions"
                }, filteredSuggestions.map((suggestion, index) => {
                    let className; // Flag the active suggestion with a class

                    if (index == activeSuggestion) {
                        className = "suggestion-active";
                    }

                    return React.createElement("li", {
                        className: className,
                        key: pk ? suggestion[pk] : suggestion,
                        onClick: () => onClick(suggestion)
                    }, displayColumn ? suggestion[displayColumn] : suggestion);
                }));
            } else {
                if (userInput !== "")
                    suggestionsListComponent = React.createElement("div", {
                        className: "no-suggestions"
                    }, React.createElement("em", null, errorMessage ? errorMessage : "No items "));
            }
        }
        let defaultValue = this.props.value ? this.props.value : "";
        return (
            <div>
                <input className={`form-control ${required ? "required" : ''}`}
                       type="text"
                       onChange={onChange}
                       onKeyDown={onKeyDown}
                       value={userInput || (!filtered && defaultValue) || ''}
                       onClick={handleOnClick}
                       onBlur={handleOnBlur}
                       disabled={this.props.disabled}
                       style={{width: width || '100%'}}
                       autoComplete="off"
                       placeholder={this.props.placeholder}
                />
                {suggestionsListComponent}
            </div>
        )
    }
}

export default AutoComplete;

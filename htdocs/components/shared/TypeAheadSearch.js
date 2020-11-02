import React from 'react';

class TypeAheadSearch extends React.Component {
    el = React.createElement;
    static defaultProps = {
        itemsToShow: 40,
        delay: 200
    }

    constructor(props) {
        super(props);
    }

    componentDidMount() {
        const {autocompleteSelectedCallBack, itemsToShow, searchRequest, delay} = this.props;
        $(this.refs.autocomplete).autocomplete({
            minLength: 0,
            source: function (request, responseCB) {
                searchRequest(request.term, itemsToShow, (items) => {
                    if (items.length > itemsToShow) {
                        items = items.slice(0, itemsToShow);
                        items.unshift({
                            value: -1,
                            label: 'Keep trying to filter, there are more results not shown here',
                            disabled: true
                        })
                    }
                    responseCB(items);
                })
            },
            delay,
            select: (event, ui) => {
                event.preventDefault();
                if (ui.item.disabled) {
                    return false;
                }
                if (autocompleteSelectedCallBack) {
                    autocompleteSelectedCallBack(ui.item)
                }
            },
        }).focus(function () {
            $(this).autocomplete("search", $(this).val());
        })
    }

    render() {
        const {value, onInputChange, readOnly, showClear, onClear} = this.props;
        return this.el(
            'div',
            {
                className: "wrapper"
            },
            [
                this.el(
                    'input',
                    {
                        type: 'text',
                        ref: 'autocomplete',
                        key: 'typeahead-input',
                        className: 'typeahead-input show-clear',
                        onChange: ($event) => {
                            onInputChange($event.target.value)
                        },
                        value: value,
                        readOnly
                    },
                ),
                showClear ? this.el(
                    'span',
                    {
                        key: 'typeahead-clear'
                    },
                    this.el(
                        'i',
                        {
                            className: 'fas fa-times',
                            onClick: ($event) => onClear()
                        }
                    )
                ) : ''
            ]
        )
    }
}

export default TypeAheadSearch;
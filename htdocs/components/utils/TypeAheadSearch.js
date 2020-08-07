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
                            id: -1,
                            name: 'Keep trying to filter, there are more results not shown here'
                        })
                    }
                    responseCB(items);
                })
            },
            delay,
            select: (event, ui) => {
                event.preventDefault();
                if (autocompleteSelectedCallBack) {
                    autocompleteSelectedCallBack(ui.item)
                }
            }
        })
    }

    render() {
        const {value, onInputChange, readOnly} = this.props;
        return this.el(
            React.Fragment,
            {},
            this.el(
                'input',
                {
                    type: 'text',
                    ref: 'autocomplete',
                    key: 'typeahead-input',
                    className: 'typeahead-input',
                    onChange: ($event) => onInputChange($event.target.value),
                    value: value,
                    readOnly
                },
            )
        )
    }
}

export default TypeAheadSearch;
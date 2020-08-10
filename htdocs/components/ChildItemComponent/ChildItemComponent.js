import Spinner from "../utils/spinner.js";
import TypeAheadSearch from "../utils/TypeAheadSearch.js";
import ItemList from "./ItemList.js";

class ChildItemComponent extends React.Component {
    el = React.createElement;

    /**
     * init state
     * @param {*} props
     */
    constructor(props) {
        super(props);
        this.state = {
            isDataLoaded: false,
            childItems: [],
            selectedChildItemId: null,
            inputSearch: ''
        };
    }

    componentDidUpdate() {

    }

    componentDidMount() {
        const {itemId} = this.props;
        this.fetchChildItems(itemId)
    }


    fetchChildItems(itemId) {
        const itemsURL = new URL('Item.php', location.origin);
        itemsURL.searchParams.append("action", "GET_CHILD_ITEMS");
        itemsURL.searchParams.append("itemId", itemId);
        fetch(itemsURL.toString())
            .then(x => x.json())
            .then(response => {
                this.setState({
                    childItems: response.data,
                    isDataLoaded: true
                })
            })
    }

    getAutocompleteURL() {
        const itemsURL = new URL('Item.php', location.origin);
        itemsURL.searchParams.append("action", "SEARCH_ITEMS");
        return itemsURL.toString();
    }

    render() {
        const {isDataLoaded, childItems} = this.state;
        if (!isDataLoaded) {
            return this.el('div', null, ["Loading Data", this.el(Spinner, {key: 'spinner'})])
        }
        return this.el('div',
            {className: "child-item-container"},
            [
                this.el(
                    'div',
                    {
                        key: 'child-item-add',
                        className: 'child-item-add'
                    },
                    [
                        this.el(TypeAheadSearch, {
                            key: 'child-item-typeahead',
                            autocompleteSelectedCallBack: (selected) => this.selectedChildItemId(selected),
                            searchRequest: (term, itemsToShow, responseCB) => {
                                const formData = new FormData();
                                formData.append('term', term);
                                formData.append('limit', itemsToShow + 1);
                                fetch(this.getAutocompleteURL(), {method: 'POST', body: formData})
                                    .then(x => x.json())
                                    .then(response => {
                                        responseCB(response.data.map(item => ({
                                            label: `${item.description}${item.partNo ? ' (' + item.partNo + ')' : ''}`,
                                            value: item.itemID
                                        })))
                                    })
                            },
                            value: this.state.inputSearch || '',
                            onInputChange: (value) => this.changeInput(value),
                            readOnly: this.state.selectedChildItemId,
                        }),
                        this.state.selectedChildItemId ? this.el(
                            'i',
                            {
                                onClick: ($event) => this.clearSelectedItem(),
                                key: 'child-clear-button',
                                type: 'button',
                                className: ''
                            },
                            this.el(
                                'i',
                                {
                                    className: 'fas fa-times-circle'
                                }
                            )
                        ) : '',
                        this.el(
                            'button',
                            {
                                onClick: ($event) => this.addChildItem(),
                                key: 'child-item-add-button',
                                disabled: !this.state.selectedChildItemId,
                                type: 'button'
                            },
                            this.el(
                                'i',
                                {
                                    className: 'fas fa-plus-circle'
                                }
                            )
                        )
                    ]
                ),
                this.el(
                    ItemList,
                    {
                        key: 'child-item-list',
                        items: childItems,
                        isDeletable: true,
                        onDeleteItem: (itemID) => this.deleteChild(itemID)
                    }
                )
            ]
        )

    }

    selectedChildItemId(selected) {
        this.setState({
            selectedChildItemId: selected.value,
            inputSearch: selected.label
        })
    }

    clearSelectedItem() {
        this.setState({
            selectedChildItemId: null,
            inputSearch: null,
        })
    }

    addChildItem() {
        const itemsURL = new URL('Item.php', location.origin);
        itemsURL.searchParams.append("action", "ADD_CHILD_ITEM");
        const itemId = this.props.itemId;
        const childItemId = this.state.selectedChildItemId;
        fetch(itemsURL.toString(), {
            method: 'POST', body: JSON.stringify({itemId, childItemId})
        })
            .then(x => x.json())
            .then(response => {
                this.clearSelectedItem();
                this.fetchChildItems(this.props.itemId);
            })
    }

    deleteChild(childItemId) {
        const itemsURL = new URL('Item.php', location.origin);
        itemsURL.searchParams.append("action", "REMOVE_CHILD_ITEM");
        const itemId = this.props.itemId;
        fetch(itemsURL.toString(), {
            method: 'POST', body: JSON.stringify({itemId, childItemId})
        })
            .then(x => x.json())
            .then(response => {
                this.fetchChildItems(this.props.itemId);
            })
    }

    changeInput(value) {
        this.setState({
            inputSearch: value
        })
    }
}

export default ChildItemComponent;
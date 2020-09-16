import ChildItemComponent from "./ChildItemComponent.js";
import ItemList from "./ItemList.js";

class ChildAndParentItems extends React.Component {
    el = React.createElement;

    /**
     * init state
     * @param {*} props
     */
    constructor(props) {
        super(props);
        this.state = {
            parentItems: [],
        };
    }

    componentDidUpdate() {

    }

    componentDidMount() {
        const {itemId} = this.props;
        this.fetchParentItems(itemId);
    }

    fetchParentItems(itemId) {
        const itemsURL = new URL('Item.php', location.origin);
        itemsURL.searchParams.append("action", "GET_PARENT_ITEMS");
        itemsURL.searchParams.append("itemId", itemId);
        fetch(itemsURL.toString())
            .then(x => x.json())
            .then(response => {
                this.setState({
                    parentItems: response.data,
                })
            })
    }

    renderChildItems() {

    }

    renderParentItems() {
        if (!this.state.parentItems || !this.state.parentItems.length) {
            return ''
        }

        return this.el(
            'div',
            {className: 'child-and-parent__parent-list', key: 'child-and-parent__parent-list-container'},
            [
                this.el('h3', {key: 'child-and-parent__parent-list-title'}, 'Parent Items'),
                this.el(ItemList, {items: this.state.parentItems, key: 'child-and-parent__parent-list'})
            ]
        )
    }

    render() {
        const {itemId} = this.props;
        return this.el(
            React.Fragment,
            null,
            [
                this.el('h3', {key: 'children-title'}, 'Child Items'),
                this.el(ChildItemComponent, {key: 'child-item-comp', itemId}),
                this.el('br', {key: 'spacer'}),
                this.renderParentItems()
            ]
        )

    }


}

export default ChildAndParentItems;
const domContainer = document.querySelector('#reactChildAndParentItemsComponent');
ReactDOM.render(React.createElement(ChildAndParentItems, {itemId: domContainer.dataset.itemId}), domContainer);
import ChildItemComponent from "./subComponents/ChildItemComponent";
import ItemList from "./subComponents/ItemList";
import React from 'react';
import ReactDOM from 'react-dom';

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
                this.el(ItemList, {
                    items: this.state.parentItems,
                    key: 'child-and-parent__parent-list',
                    showCount: false
                })
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
document.addEventListener('DOMContentLoaded', () => {
    const domContainer = document.querySelector('#reactChildAndParentItemsComponent');
    if (!domContainer.dataset.itemId) {
        return;
    }
    ReactDOM.render(React.createElement(ChildAndParentItems, {itemId: domContainer.dataset.itemId}), domContainer);
})

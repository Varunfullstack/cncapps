import React from 'react';

import './ItemList.css';

class ItemList extends React.Component {
    el = React.createElement;

    constructor(props) {
        super(props);
    }

    static defaultProps = {
        items: [],
        isDeletable: false
    };

    render() {
        const {items, isDeletable, onDeleteItem} = this.props;
        return this.el(
            'div',
            {
                className: 'c-item-list__container',
                key: 'item-list'
            },
            items.map(childItem => {
                return this.el(
                    'div',
                    {className: 'c-item-list__item', key: childItem.childItemId},
                    [
                        this.el(
                            'div',
                            {className: 'c-item-list__link', key: `child-link-${childItem.childItemId}`},
                            this.el(
                                'a',
                                {
                                    href: `/Item.php?action=editItem&itemID=${childItem.childItemId}`,
                                    target: '_blank'
                                },
                                childItem.description
                            )
                        ),
                        <div key="numberInput"
                             className="c-item-list__number-input"
                        >
                            <input type="number"
                                   min="1"
                                   value={childItem.quantity}
                                   onChange={$event => {
                                       this.props.onQuantityChanged(childItem.childItemId, +$event.target.value);
                                   }}
                            />
                        </div>,
                        isDeletable ?
                            this.el(
                                'div',
                                {className: 'c-item-list__delete', key: `child-delete-${childItem.childItemId}`},
                                this.el(
                                    'button',
                                    {
                                        onClick: ($event) => onDeleteItem(childItem.childItemId),
                                        type: 'button'
                                    },
                                    this.el('i', {className: 'fas fa-trash'})
                                )
                            ) : ''
                    ]
                )
            })
        );
    }
}

export default ItemList;

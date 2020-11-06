import React from 'react';

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
                    {className: 'c-item-list__item', key: childItem.itemID},
                    [
                        this.el(
                            'div',
                            {className: 'c-item-list__link', key: `child-link-${childItem.itemID}`},
                            this.el(
                                'a',
                                {
                                    href: `/Item.php?action=editItem&itemID=${childItem.itemID}`,
                                    target: '_blank'
                                },
                                childItem.description
                            )
                        ),
                        isDeletable ?
                            this.el(
                                'div',
                                {className: 'c-item-list__delete', key: `child-delete-${childItem.itemID}`},
                                this.el(
                                    'button',
                                    {
                                        onClick: ($event) => onDeleteItem(childItem.itemID),
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

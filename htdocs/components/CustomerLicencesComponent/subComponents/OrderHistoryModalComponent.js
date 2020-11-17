import APICustomerLicenses from "./APICustomerLicenses.js";
import Table from '../../shared/table/table.js';
import Modal from '../../shared/Modal/modal';
import React from 'react';

class OrderHistoryModalComponent extends React.Component {
    el = React.createElement;
    api;

    constructor(props) {
        super(props);
        this.api = new APICustomerLicenses();
    }

    handleOnClose = () => {
        const {onHide} = this.props;
        if (onHide) onHide();
    };
    getModalElement = () => {
        let items = [];
        if (this.props.items)
            items = [...this.props.items] || [];
        //console.log(items);

        if (items) {
            // items = [...this.props.items];
            const {title, show} = this.props;
            const {handleOnClose, el} = this;
            items = items.map((v, i) => {
                v.indx = i;
                return v;
            });

            const columns = [
                {path: "sku", label: "TD#", sortable: true, content: (i) => el('label', null, i.sku)},
                {path: "totalSeats", label: "Qty", sortable: true},
                {
                    path: "message",
                    label: "Seats required/Status change",
                    sortable: true,
                },
                {path: "createdBy", label: "Created by", sortable: true},
                {
                    path: "createdOn",
                    label: "Created on",
                    sortable: true,
                    content: (sub) =>
                        el("label", null, moment(sub.createdOn).format("DD/MM/YYYY HH:MM")),
                },
            ];

            //prepare body
            const body = el(Table, {
                id: "items",
                data: items || [],
                columns: columns,
                defaultSortPath: "createdOn",
                defaultSortOrder: "desc",
                pk: "indx",
            });
            const footer = el(React.Fragment, {key: "footer"}, [
                el("button", {key: "btnCancel", onClick: handleOnClose}, "Close"),
            ]);
            return el(Modal, {
                key: "Modal",
                show: show,
                width: "800px",
                title: title || "Order History",
                onClose: handleOnClose,
                content: body,
                footer,
            });
        } else return null;
    };

    render() {
        return this.getModalElement();
    }
}

export default OrderHistoryModalComponent;

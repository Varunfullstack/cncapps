import {getAllContacts, getMappedContacts} from "../selectors";
import {updateCustomerField} from "../actions";
import {connect} from "react-redux";

import React from 'react';
import {ContactComponent} from "./ContactComponent";

class ContactsComponent extends React.PureComponent {


    constructor(props, context) {
        super(props, context);
        this.onContactChanged = this.onContactChanged.bind(this);
    }

    onContactChanged($event) {

    }

    renderContacts() {
        const {contacts} = this.props;
        return contacts.map(contact => (
            <ContactComponent key={contact.contactID}
                              contact={contact}
                              onChange={this.onContactChanged}
            />
        ));
    }

    render() {
        return (
            <div className="mt-3">
                <div className="row"
                     key="firstRow"
                >
                    <div className="col-md-12">
                        <h2>Contacts</h2>
                    </div>
                    <div className="col-md-12">
                        <button className="btn btn-sm btn-new mt-3 mb-3">Add Contact</button>
                    </div>
                </div>
                {this.renderContacts()}
            </div>
        )
    }
}

function mapStateToProps(state) {
    return {
        contacts: getAllContacts(state)
    }
}

function mapDispatchToProps(dispatch) {
    return {
        customerValueUpdate: (field, value) => {
            dispatch(updateCustomerField(field, value))
        }
    }
}

export default connect(mapStateToProps, mapDispatchToProps)(ContactsComponent)

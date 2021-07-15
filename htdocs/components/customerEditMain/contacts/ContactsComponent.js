import {getAllContacts, getAllSites} from "../selectors/selectors";
import {updateCustomerField} from "../actions";
import {connect} from "react-redux";

import React from 'react';
import {ContactComponent} from "./ContactComponent";

class ContactsComponent extends React.PureComponent {


    constructor(props, context) {
        super(props, context);
        this.onContactChanged = this.onContactChanged.bind(this);
    }

    onContactChanged = $event => {
    };

    renderContacts() {
        const {contacts, sites} = this.props;
        return contacts.map(contact => (
            <ContactComponent key={contact.id}
                              contact={contact}
                              sites={sites}
                              onChange={this.onContactChanged}
            />
        ));
    }

    render() {
        return (
            <div className="mt-3">
                <div className="row">
                    <div className="col-md-12">
                        <h2>Contacts</h2>
                    </div>
                    <div className="col-md-12">
                        <button className="btn btn-sm btn-new mt-3 mb-3">Add Contact</button>
                    </div>
                </div>
                <div className="row">
                    <div className="col-md-12">
                        <table className="table table-hover">
                            <thead key="head">
                            <tr>
                                <th>Full Name</th>
                                <th>Position</th>
                                <th>Phone</th>
                                <th>Mobile</th>
                                <th>Email</th>
                                <th>Support Level</th>
                                <th>Inv</th>
                                <th>HR</th>
                            </tr>
                            </thead>
                            <tbody>
                            {this.renderContacts()}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        )
    }
}

function mapStateToProps(state) {
    return {
        contacts: getAllContacts(state),
        sites: getAllSites(state),
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

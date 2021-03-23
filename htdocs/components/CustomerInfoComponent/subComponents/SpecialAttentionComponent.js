import MainComponent from "../../shared/MainComponent";
import React from "react";
import APICustomerInfo from "../services/APICustomerInfo";
import Spinner from "../../shared/Spinner/Spinner";
import "../../shared/table/table.css";
import {dateFormatExcludeNull, sort} from "../../utils/utils";

class SpecialAttentionComponent extends MainComponent {
    api = new APICustomerInfo();

    constructor(props) {
        super(props);
        this.state = {
            showSpinner: false,
            customers: [],
            contacts: [],
        };
    }

    componentWillUnmount() {
    }

    componentDidMount() {
        this.getData();
    }

    getData = async () => {
        this.setState({showSpinner: true});
        const data = await this.api.getSpecialAttention();
        data.contacts.map(c => {
            c.name = c.customerName + c.contactName
            return c;
        })
        this.setState({
            showSpinner: false,
            customers: data.customers,
            contacts: sort(data.contacts, 'name'),
        });
    };
    getContactsElement = () => {
        const {contacts} = this.state;
        return (
            <div style={{width: 600}}>
                <p>
                    These contacts have a 50% reduction on their SLA to ensure they receive a priority service,
                </p>
                <p className="mb-4">
                    this may be because they are a new contact or because have experienced poor service recently.
                </p>
                <table className="table table-striped">
                    <thead>
                    <tr>
                        <th>
                            Contact
                        </th>
                        <th>
                            Customer
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {contacts.map((c) => (
                        <tr key={c.contactName}>
                            <td>
                                <a
                                    target="_blank"
                                    href={c.linkURL}
                                >
                                    {c.contactName}
                                </a>
                            </td>
                            <td>
                                {c.customerName}
                            </td>
                        </tr>
                    ))}
                    </tbody>

                </table>
            </div>
        );
    }
    getCustomersElement = () => {
        const {customers} = this.state;
        return (
            <div style={{width: 600}}>
                <p>
                    These customers have a 50% reduction on their SLA to ensure they receive a priority service,
                </p>
                <p className="mb-4">
                    this may be because they are a new customer or because have experienced poor service recently.
                </p>
                <table className="table table-striped">
                    <thead>
                    <tr>
                        <th>
                            Customer
                        </th>
                        <th>
                            End
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {customers.map((c) => (
                        <tr key={c.customerName}>
                            <td>
                                <a
                                    target="_blank"
                                    href={c.linkURL}
                                >
                                    {c.customerName}
                                </a>
                            </td>
                            <td>
                                {dateFormatExcludeNull(c.specialAttentionEndDate)}
                            </td>
                        </tr>
                    ))}
                    </tbody>

                </table>
            </div>
        );
    };

    render() {
        return (
            <div>
                <Spinner key="spinner"
                         show={this.state.showSpinner}
                ></Spinner>
                <div style={{
                    display: 'flex',
                    flexDirection: 'column',
                    justifyContent: 'space-between',
                    maxWidth: 1300
                }}
                >
                    {this.getCustomersElement()}
                    {this.getContactsElement()}
                </div>
            </div>
        );
    }
}

export default SpecialAttentionComponent;

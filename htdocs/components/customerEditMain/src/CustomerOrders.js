"use strict";
import React from 'react';

class CustomerOrders extends React.Component {
    el = React.createElement;


    constructor(props) {
        super(props);
        this.state = {
            loaded: false,
            customerOrders: []
        };

    }


    componentDidMount() {
        const {customerId} = this.props;
        fetch('?action=getCustomerOrders&customerId=' + customerId)
            .then(response => response.json())
            .then(response => this.setState({customerOrders: response.data, loaded: true}))

    }

    render() {
        const {customerOrders} = this.state;
        return (
            <div className="tab-pane fade show"
                 id="nav-orders"
                 role="tabpanel"
                 aria-labelledby="nav-orders-tab"
            >
                <div className="container-fluid mt-3 mb-3">
                    <div className="row">
                        <div className="col-md-12">
                            <h2>Orders</h2>
                        </div>
                    </div>
                    <div className="row">
                        <div className="col-md-12">
                            <table className="table table-striped table-bordered">
                                <thead>
                                <tr>
                                    <td className="fitwidth">Order No.</td>
                                    <td className="fitwidth">Type</td>
                                    <td className="fitwidth">Date</td>
                                    <td className="fitwidth">Cast PO Ref</td>
                                </tr>
                                </thead>
                                <tbody>
                                {
                                    customerOrders.map(c => (
                                        <tr key={c.id}>
                                            <td>
                                                <a href={c.url}>
                                                    <h6>{c.id}</h6>
                                                </a>
                                            </td>
                                            <td><h6>{c.type}</h6></td>
                                            <td><h6>{c.date}</h6></td>
                                            <td><h6>{c.custPORef}</h6></td>
                                        </tr>
                                    ))
                                }
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        )
    }
}

export default CustomerOrders;
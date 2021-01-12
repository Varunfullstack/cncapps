"use strict";
import React from 'react';

import {connect} from "react-redux";
import {getOrders} from "./selectors";

class CustomerOrders extends React.PureComponent {
    constructor(props) {
        super(props);
    }


    render() {
        const {orders} = this.props;
        return (
            <div className="mt-3">
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
                                orders.map(c => (
                                    <tr key={c.id}>
                                        <td>
                                            <a href={c.url}>
                                                {c.id}
                                            </a>
                                        </td>
                                        <td>{c.type}</td>
                                        <td>{c.date}</td>
                                        <td>{c.custPORef}</td>
                                    </tr>
                                ))
                            }
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
        orders: getOrders(state),
    }
}


export default connect(mapStateToProps)(CustomerOrders)

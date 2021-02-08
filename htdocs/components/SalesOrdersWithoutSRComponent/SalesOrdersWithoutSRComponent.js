import ReactDOM from "react-dom";
import React from "react";

class SalesOrdersWithoutSRComponent extends React.PureComponent {
    constructor(props, context) {
        super(props, context);
        this.state = {
            items: []
        }
    }

    componentDidMount() {
        fetch('/POStatusReport.php?action=GET_ORDERS_WITHOUT_SR_DATA').then(res => res.json()).then(response => {
            this.setState({items: response.data});
        })
    }

    render() {
        const {items} = this.state;
        if (!items || !items.length) {
            return "";
        }
        return (
            <React.Fragment>
                <h2>Sales Orders with no Service Requests</h2>
                <table>
                    <thead>
                    <tr>
                        <th>
                            SO Number
                        </th>
                        <th>
                            Description
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                    {
                        items.map(x => (
                                <tr key={x.salesOrderId}>
                                    <td>
                                        <a href={`/SalesOrder.php?action=displaySalesOrder&ordheadID=${x.salesOrderId}`}
                                           target="_blank"
                                        >{x.salesOrderId}</a>
                                    </td>
                                    <td>
                                        {x.itemLineDescription}
                                    </td>
                                </tr>
                            )
                        )
                    }
                    </tbody>
                </table>
            </React.Fragment>
        )
    }
}

export default SalesOrdersWithoutSRComponent;

document.addEventListener('DOMContentLoaded', () => {
        const domContainer = document.querySelector("#reactSalesOrderWithoutSRComponent");
        if (domContainer) {
            ReactDOM.render(React.createElement(SalesOrdersWithoutSRComponent), domContainer);
        }
    }
)